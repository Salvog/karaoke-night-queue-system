<?php
declare(strict_types=1);

/**
 * Karaoke library exporter (Windows) -> CSV
 * - Estrae artist/title dal filename (pattern "A - B" e "A-B-..."), rimuove ML finale
 * - Calcola durata con ffprobe quando possibile (skip per .prm -> durata vuota)
 * - Scrive un CSV con tutte le info utili (per import bulk o uso diretto)
 */

set_time_limit(0);

// ================== CONFIG ==================
$ROOT_DIRS = [
  'C:\\Users\\salvo\\Downloads\\wetransfer_wi_2026-02-18_2131',
  // aggiungi altre cartelle se vuoi
];

$ALLOWED_EXT = ['mp3','mid','prm'];

// Output CSV
$OUT_CSV = __DIR__ . DIRECTORY_SEPARATOR . 'karaoke_library_export.csv';

// Se ffprobe non è nel PATH, metti qui il percorso completo a ffprobe.exe (es. C:\\ffmpeg\\bin\\ffprobe.exe)
$FFPROBE_BIN = null;

// Se vuoi compatibilità Excel, abilita BOM UTF-8 (opzionale)
$WRITE_UTF8_BOM = true;

// Ogni quanti file stampare progress
$PROGRESS_EVERY = 2000;

// ================== HELPERS ==================
function shellQuoteWin(string $s): string {
  return '"' . str_replace('"', '""', $s) . '"';
}

function resolveFfprobe(?string $configured): ?string {
  if ($configured !== null) {
    if (is_file($configured)) return $configured;
    throw new RuntimeException("FFPROBE_BIN configurato ma non trovato: $configured");
  }

  $out = shell_exec('where ffprobe 2>NUL');
  if (is_string($out) && trim($out) !== '') {
    $lines = preg_split("/\r?\n/", trim($out));
    if (!empty($lines[0]) && is_file($lines[0])) return $lines[0];
  }
  return null;
}

function checkFfprobe(string $bin): bool {
  $out = shell_exec(shellQuoteWin($bin) . ' -version 2>NUL');
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
  $dt = (new DateTimeImmutable('@'.$mtime))->setTimezone(new DateTimeZone('UTC'));
  return $dt->format('Y-m-d H:i:s') . '.000000';
}

function ffprobeDurationSeconds(string $ffprobeBin, string $fullPath): ?int {
  $cmd = shellQuoteWin($ffprobeBin)
    . ' -v error -show_entries format=duration -of default=nw=1:nk=1 '
    . shellQuoteWin($fullPath)
    . ' 2>NUL';

  $out = shell_exec($cmd);
  if (!is_string($out)) return null;

  $out = trim($out);
  if (!preg_match('/^\d+(\.\d+)?$/', $out)) return null;

  $sec = (int) round((float)$out);
  return $sec > 0 ? $sec : null;
}

// ================== ffprobe check ==================
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

// ================== CSV open ==================
$fp = fopen($OUT_CSV, 'wb');
if ($fp === false) {
  throw new RuntimeException("Impossibile creare il CSV: $OUT_CSV");
}

if ($WRITE_UTF8_BOM) {
  fwrite($fp, "\xEF\xBB\xBF"); // BOM per Excel (opzionale)
}

// Header
$header = [
  'artist',
  'title',
  'duration_seconds',
  'full_path',
  'ext',
  'media_type',
  'size_bytes',
  'last_write_utc',
];
fputcsv($fp, $header);

// ================== SCAN & EXPORT ==================
$total = 0;
$durMissing = 0;

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
    $mtimeUtc = mtimeUtcMicro((int)$file->getMTime());

    [$artist, $title] = parseArtistTitleFromFilename($fullPath);
    $type = mediaTypeFromExt($ext);

    // Durata: per prm la lasciamo vuota
    $dur = null;
    if ($ext !== 'prm') {
      $dur = ffprobeDurationSeconds($ffprobeBin, $fullPath);
      if ($dur === null) $durMissing++;
    }

    // Nota: fputcsv gestisce automaticamente virgolette e virgole
    fputcsv($fp, [
      $artist,
      $title,
      $dur,        // null -> campo vuoto nel CSV
      $fullPath,
      $ext,
      $type,
      $size,
      $mtimeUtc,
    ]);

    $total++;
    if ($total % $PROGRESS_EVERY === 0) {
      echo "Esportati: $total | Durate mancanti: $durMissing\n";
    }
  }
}

fclose($fp);

echo "FINE\n";
echo "CSV creato: $OUT_CSV\n";
echo "Totali esportati: $total\n";
echo "Durate mancanti (ffprobe null): $durMissing\n";