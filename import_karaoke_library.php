<?php
declare(strict_types=1);

/**
 * Karaoke library importer (Windows) -> MySQL
 * - Estrae artist/title dal filename (pattern "A - B" e "A-B-..."), rimuove ML finale
 * - Calcola durata con ffprobe quando possibile (skip per .prm)
 * - UPSERT su chiave univoca (nel tuo caso: full_path_sha STORED GENERATED)
 * - Incrementale: se size+mtime invariati, skippa SOLO se duration_seconds è già valorizzato
 */

set_time_limit(0);

// ================== CONFIG ==================
$ROOT_DIRS = [
  'C:\\Users\\salvo\\Downloads\\wetransfer_wi_2026-02-18_2131',
];

// Estensioni da considerare
$ALLOWED_EXT = ['mp3','mid','prm'];

// Se ffprobe non è nel PATH, metti qui il percorso completo a ffprobe.exe (es. C:\\ffmpeg\\bin\\ffprobe.exe)
$FFPROBE_BIN = null;

$DB_DSN  = 'mysql:host=127.0.0.1;dbname=KN01;charset=utf8mb4';
$DB_USER = 'root';
$DB_PASS = '';

// Se true, per i file invariati non aggiorna nulla SOLO se duration_seconds non è NULL
$INCREMENTAL_SKIP_UNCHANGED = true;

// Commit ogni N record aggiornati (performance)
$COMMIT_EVERY = 1000;

// ================== HELPERS ==================
function shellQuote(string $s): string {
  // quoting affidabile su Windows
  return '"' . str_replace('"', '""', $s) . '"';
}

function resolveFfprobe(?string $configured): ?string {
  if ($configured !== null) {
    if (is_file($configured)) return $configured;
    throw new RuntimeException("FFPROBE_BIN configurato ma non trovato: $configured");
  }

  // prova con "where ffprobe" (Windows)
  $out = shell_exec('where ffprobe 2>NUL');
  if (is_string($out) && trim($out) !== '') {
    $lines = preg_split("/\r?\n/", trim($out));
    if (!empty($lines[0]) && is_file($lines[0])) return $lines[0];
  }

  return null;
}

function checkFfprobe(string $bin): bool {
  $out = shell_exec(shellQuote($bin) . ' -version 2>NUL');
  return is_string($out) && stripos($out, 'ffprobe version') !== false;
}

function mediaTypeFromExt(string $ext): string {
  return match ($ext) {
    'mp3' => 'AUDIO',
    'mid' => 'MIDI',
    'prm' => 'PARAM',
    default => 'UNKNOWN',
  };
}

function normalizeSpaces(string $s): string {
  $s = str_replace(['_', "\t"], ' ', $s);
  $s = preg_replace('/\s+/', ' ', trim($s));
  return $s ?? '';
}

function removeTrailingML(string $title): string {
  $t = trim($title);

  // " ... ML"
  $t2 = preg_replace('/\s+ML$/i', '', $t);
  if ($t2 !== null && $t2 !== $t) return trim($t2);

  // "...-ML" / "..._ML"
  $t2 = preg_replace('/[-_]+ML$/i', '', $t);
  if ($t2 !== null) return trim($t2);

  return $t;
}

function parseArtistTitleFromFilename(string $fullPath): array {
  $base = pathinfo($fullPath, PATHINFO_BASENAME);
  $name = pathinfo($base, PATHINFO_FILENAME);
  $name = normalizeSpaces($name);

  $artist = 'Unknown';
  $title  = $name;

  if (str_contains($name, ' - ')) {
    // "Artista - Titolo ..."
    [$a, $rest] = explode(' - ', $name, 2);
    $artist = trim($a);
    $title  = trim($rest);
  } else {
    // "ARTISTA-TITOLO-EXTRA"
    $parts = array_values(array_filter(array_map('trim', explode('-', $name)), fn($p) => $p !== ''));
    if (count($parts) >= 2) {
      $artist = $parts[0];
      $title  = $parts[1];
      // parti successive ignorate (es. ML)
    }
  }

  $artist = normalizeSpaces($artist);
  $title  = normalizeSpaces($title);
  $title  = removeTrailingML($title);

  if ($artist === '') $artist = 'Unknown';
  if ($title === '')  $title  = $name;

  return [$artist, $title];
}

function mtimeUtcMicro(int $mtime): string {
  // precisione al secondo -> .000000
  $dt = (new DateTimeImmutable('@'.$mtime))->setTimezone(new DateTimeZone('UTC'));
  return $dt->format('Y-m-d H:i:s') . '.000000';
}

function cut255(string $s): string {
  return function_exists('mb_substr') ? (string)mb_substr($s, 0, 255) : substr($s, 0, 255);
}

function ffprobeDurationSeconds(string $ffprobeBin, string $fullPath): ?int {
  $cmd = shellQuote($ffprobeBin)
    . ' -v error -show_entries format=duration -of default=nw=1:nk=1 '
    . shellQuote($fullPath)
    . ' 2>NUL';

  $out = shell_exec($cmd);
  if (!is_string($out)) return null;

  $out = trim($out);
  if (!preg_match('/^\d+(\.\d+)?$/', $out)) return null;

  $sec = (int) round((float)$out);
  return $sec > 0 ? $sec : null;
}

// ================== DB ==================
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Risolvi ffprobe (necessario per avere duration su mp3/mid)
$ffprobeBin = resolveFfprobe($FFPROBE_BIN);
if ($ffprobeBin === null || !checkFfprobe($ffprobeBin)) {
  throw new RuntimeException(
    "ffprobe non disponibile.\n" .
    "1) Installa FFmpeg: winget install --id Gyan.FFmpeg -e\n" .
    "2) Riapri PowerShell\n" .
    "3) Verifica: ffprobe -version / where ffprobe\n" .
    "Oppure imposta \$FFPROBE_BIN al path completo di ffprobe.exe\n"
  );
}

// Cache dei file già noti (per incremental)
$known = [];
if ($INCREMENTAL_SKIP_UNCHANGED) {
  // includo anche duration_seconds: se è NULL, NON vogliamo skipparlo
  $stmt = $pdo->query("SELECT full_path, size_bytes, last_write_utc, duration_seconds FROM karaoke_song");
  while ($r = $stmt->fetch()) {
    $lw = (string)$r['last_write_utc'];
    // Normalizza a 'Y-m-d H:i:s.000000' se MySQL/PDO lo restituisce senza microsecondi
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $lw)) {
      $lw .= '.000000';
    }
    $known[$r['full_path']] = [
      'size_bytes' => (int)$r['size_bytes'],
      'last_write_utc' => $lw,
      'duration_seconds' => $r['duration_seconds'] !== null ? (int)$r['duration_seconds'] : null,
    ];
  }
}

$upsert = $pdo->prepare("
INSERT INTO karaoke_song
(artist, title, duration_seconds, full_path, ext, media_type, size_bytes, last_write_utc)
VALUES
(:artist, :title, :dur, :path, :ext, :type, :size, :mtime)
ON DUPLICATE KEY UPDATE
  artist=VALUES(artist),
  title=VALUES(title),
  duration_seconds=VALUES(duration_seconds),
  ext=VALUES(ext),
  media_type=VALUES(media_type),
  size_bytes=VALUES(size_bytes),
  last_write_utc=VALUES(last_write_utc)
");

// ================== SCAN & IMPORT ==================
$total = 0;
$insertedOrUpdated = 0;
$skipped = 0;
$durMissing = 0;

$pdo->beginTransaction();
$txCount = 0;

try {
  foreach ($ROOT_DIRS as $root) {
    if (!is_dir($root)) {
      fwrite(STDERR, "Directory non trovata: $root\n");
      continue;
    }

    $it = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
      /** @var SplFileInfo $file */
      if (!$file->isFile()) continue;

      $ext = strtolower($file->getExtension());
      if (!in_array($ext, $ALLOWED_EXT, true)) continue;

      $fullPath = $file->getPathname();
      $size = (int)$file->getSize();
      $mtime = (int)$file->getMTime();
      $mtimeUtc = mtimeUtcMicro($mtime);

      $total++;

      if ($INCREMENTAL_SKIP_UNCHANGED && isset($known[$fullPath])) {
        $k = $known[$fullPath];

        $unchanged = ($k['size_bytes'] === $size && $k['last_write_utc'] === $mtimeUtc);
        $hasDuration = ($k['duration_seconds'] !== null);

        // Skip SOLO se invariato e la durata è già valorizzata
        if ($unchanged && $hasDuration) {
          $skipped++;
          if ($total % 2000 === 0) {
            echo "Scansionati: $total | aggiornati: $insertedOrUpdated | skip: $skipped | dur mancanti: $durMissing\n";
          }
          continue;
        }
      }

      [$artist, $title] = parseArtistTitleFromFilename($fullPath);
      $type = mediaTypeFromExt($ext);

      // Durata: skip sui .prm
      $dur = null;
      if ($ext !== 'prm') {
        $dur = ffprobeDurationSeconds($ffprobeBin, $fullPath);
        if ($dur === null) $durMissing++;
      }

      $upsert->execute([
        ':artist' => cut255($artist),
        ':title'  => cut255($title),
        ':dur'    => $dur,
        ':path'   => $fullPath,
        ':ext'    => $ext,
        ':type'   => $type,
        ':size'   => $size,
        ':mtime'  => $mtimeUtc,
      ]);

      $insertedOrUpdated++;
      $txCount++;

      if ($txCount >= $COMMIT_EVERY) {
        $pdo->commit();
        $pdo->beginTransaction();
        $txCount = 0;
      }

      if ($total % 2000 === 0) {
        echo "Scansionati: $total | aggiornati: $insertedOrUpdated | skip: $skipped | dur mancanti: $durMissing\n";
      }
    }
  }

  if ($pdo->inTransaction()) $pdo->commit();
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  throw $e;
}

echo "FINE\n";
echo "Totali scansionati: $total\n";
echo "Inseriti/Aggiornati: $insertedOrUpdated\n";
echo "Skip invariati: $skipped\n";
echo "Durate mancanti (ffprobe null): $durMissing\n";