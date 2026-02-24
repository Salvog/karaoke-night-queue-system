<?php
declare(strict_types=1);

set_time_limit(0);

/**
 * Import Karaoke library CSV -> MySQL karaoke_song
 *
 * CSV atteso (con header):
 * artist,title,duration_seconds,full_path,ext,media_type,size_bytes,last_write_utc
 *
 * Strategia:
 * 1) Prova LOAD DATA LOCAL INFILE -> karaoke_song_stage
 * 2) UPSERT in karaoke_song con conversioni (duration, datetime(6), ecc.)
 * 3) Se LOCAL INFILE non disponibile, fallback con fgetcsv() + batch upsert
 */

// ================== CONFIG ==================
$DB_DSN  = 'mysql:host=127.0.0.1;dbname=KN01;charset=utf8mb4';
$DB_USER = 'root';
$DB_PASS = '';

// Batch size per fallback CSV->DB
$BATCH_SIZE = 2000;

// ================== INPUT ==================
if ($argc < 2) {
  fwrite(STDERR, "Uso: php import_karaoke_from_csv.php \"C:\\path\\file.csv\"\n");
  exit(1);
}
$csvPath = $argv[1];
if (!is_file($csvPath)) {
  fwrite(STDERR, "CSV non trovato: $csvPath\n");
  exit(1);
}

// ================== DB CONNECT ==================
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// abilita LOCAL INFILE lato client PDO (se supportato)
if (defined('PDO::MYSQL_ATTR_LOCAL_INFILE')) {
  $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);
}

// ================== SQL HELPERS ==================
function toMySqlLocalPath(string $path): string {
  // MySQL LOCAL INFILE su Windows accetta spesso anche backslash,
  // ma in generale è più sicuro usare forward slashes.
  return str_replace('\\', '/', $path);
}

function stripBom(string $s): string {
  // rimuove UTF-8 BOM se presente
  return preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
}

// UPSERT singolo (fallback)
$upsertOne = $pdo->prepare("
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

// ================== FAST PATH: LOAD DATA LOCAL INFILE ==================
function tryFastImport(PDO $pdo, string $csvPath): bool {
  // 1) pulisci staging
  $pdo->exec("TRUNCATE TABLE karaoke_song_stage");

  // 2) LOAD DATA LOCAL INFILE
  $mysqlPath = toMySqlLocalPath($csvPath);

  // Nota: LINES TERMINATED BY '\n' va bene anche se il file è \r\n; ripuliamo \r dopo.
  $sqlLoad = "
LOAD DATA LOCAL INFILE " . $pdo->quote($mysqlPath) . "
INTO TABLE karaoke_song_stage
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(artist, title, duration_seconds, full_path, ext, media_type, size_bytes, last_write_utc)
";

  try {
    $pdo->exec($sqlLoad);
  } catch (Throwable $e) {
    // LOCAL INFILE disabilitato o permessi: fallback
    return false;
  }

  // 3) ripulisci BOM (se presente) e \r
  $pdo->exec("
UPDATE karaoke_song_stage
SET
  artist = REPLACE(artist, CONVERT(0xEFBBBF USING utf8mb4), ''),
  last_write_utc = REPLACE(last_write_utc, '\r', ''),
  full_path = REPLACE(full_path, '\r', '')
WHERE
  artist LIKE CONCAT(CONVERT(0xEFBBBF USING utf8mb4), '%')
   OR last_write_utc LIKE '%\r'
   OR full_path LIKE '%\r'
  ");

  // 4) UPSERT nella tabella finale con conversioni
  // - duration_seconds: '' -> NULL, altrimenti int
  // - last_write_utc: parse DATETIME(6) (formato: YYYY-MM-DD HH:MM:SS.ffffff)
  $sqlUpsert = "
INSERT INTO karaoke_song
(artist, title, duration_seconds, full_path, ext, media_type, size_bytes, last_write_utc)
SELECT
  COALESCE(NULLIF(TRIM(artist), ''), 'Unknown') AS artist,
  COALESCE(NULLIF(TRIM(title), ''), '') AS title,
  NULLIF(TRIM(duration_seconds), '') + 0 AS duration_seconds,
  TRIM(full_path) AS full_path,
  LOWER(TRIM(ext)) AS ext,
  CASE
    WHEN UPPER(TRIM(media_type)) IN ('AUDIO','MIDI','PARAM','UNKNOWN') THEN UPPER(TRIM(media_type))
    ELSE 'UNKNOWN'
  END AS media_type,
  NULLIF(TRIM(size_bytes), '') + 0 AS size_bytes,
  STR_TO_DATE(TRIM(last_write_utc), '%Y-%m-%d %H:%i:%s.%f') AS last_write_utc
FROM karaoke_song_stage
ON DUPLICATE KEY UPDATE
  artist=VALUES(artist),
  title=VALUES(title),
  duration_seconds=VALUES(duration_seconds),
  ext=VALUES(ext),
  media_type=VALUES(media_type),
  size_bytes=VALUES(size_bytes),
  last_write_utc=VALUES(last_write_utc)
";
  $pdo->exec($sqlUpsert);

  return true;
}

// ================== FALLBACK: fgetcsv + batch upsert ==================
function fallbackImport(PDO $pdo, string $csvPath, PDOStatement $upsertOne, int $batchSize): void {
  $fh = fopen($csvPath, 'rb');
  if ($fh === false) throw new RuntimeException("Impossibile aprire CSV: $csvPath");

  // Leggi header e mappa colonne
  $header = fgetcsv($fh);
  if ($header === false) throw new RuntimeException("CSV vuoto o non valido.");

  $header[0] = stripBom((string)$header[0]);
  $idx = array_flip($header);

  $required = ['artist','title','duration_seconds','full_path','ext','media_type','size_bytes','last_write_utc'];
  foreach ($required as $col) {
    if (!isset($idx[$col])) {
      throw new RuntimeException("Header CSV mancante colonna: $col");
    }
  }

  $pdo->beginTransaction();
  $count = 0;

  try {
    while (($row = fgetcsv($fh)) !== false) {
      $artist = isset($row[$idx['artist']]) ? trim((string)$row[$idx['artist']]) : '';
      $title  = isset($row[$idx['title']]) ? trim((string)$row[$idx['title']]) : '';
      $durRaw = isset($row[$idx['duration_seconds']]) ? trim((string)$row[$idx['duration_seconds']]) : '';
      $path   = isset($row[$idx['full_path']]) ? trim((string)$row[$idx['full_path']]) : '';
      $ext    = isset($row[$idx['ext']]) ? strtolower(trim((string)$row[$idx['ext']])) : '';
      $type   = isset($row[$idx['media_type']]) ? strtoupper(trim((string)$row[$idx['media_type']])) : 'UNKNOWN';
      $sizeR  = isset($row[$idx['size_bytes']]) ? trim((string)$row[$idx['size_bytes']]) : '';
      $mtime  = isset($row[$idx['last_write_utc']]) ? trim((string)$row[$idx['last_write_utc']]) : '';

      if ($path === '') continue;

      // Normalizza type
      if (!in_array($type, ['AUDIO','MIDI','PARAM','UNKNOWN'], true)) $type = 'UNKNOWN';

      // duration: '' -> null, altrimenti int
      $dur = null;
      if ($durRaw !== '' && is_numeric($durRaw)) {
        $dur = (int)$durRaw;
      }

      // size
      $size = 0;
      if ($sizeR !== '' && is_numeric($sizeR)) $size = (int)$sizeR;

      // mtime: ci aspettiamo "YYYY-MM-DD HH:MM:SS.ffffff"
      // Se per caso manca la parte microsecondi, aggiungiamo .000000
      if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $mtime)) {
        $mtime .= '.000000';
      }

      $upsertOne->execute([
        ':artist' => $artist !== '' ? $artist : 'Unknown',
        ':title'  => $title,
        ':dur'    => $dur,
        ':path'   => $path,
        ':ext'    => $ext,
        ':type'   => $type,
        ':size'   => $size,
        ':mtime'  => $mtime,
      ]);

      $count++;
      if ($count % $batchSize === 0) {
        $pdo->commit();
        $pdo->beginTransaction();
        echo "Importati (fallback): $count\n";
      }
    }

    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fclose($fh);
    throw $e;
  }

  fclose($fh);
  echo "Importati (fallback) totali: $count\n";
}

// ================== RUN ==================
echo "CSV: $csvPath\n";

$fastOk = tryFastImport($pdo, $csvPath);
if ($fastOk) {
  echo "Import completato con LOAD DATA LOCAL INFILE (fast path).\n";
} else {
  echo "LOCAL INFILE non disponibile: uso fallback (più lento) con fgetcsv().\n";
  fallbackImport($pdo, $csvPath, $upsertOne, $BATCH_SIZE);
}

echo "FINE\n";