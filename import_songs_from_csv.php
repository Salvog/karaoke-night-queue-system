<?php
declare(strict_types=1);

set_time_limit(0);

/**
 * Import CSV -> MySQL table `songs`
 * Se duration_seconds è NULL / vuoto / non numerico -> 240 secondi
 */

// ================== CONFIG ==================
$DB_DSN  = 'mysql:host=127.0.0.1;dbname=KN01;charset=utf8mb4';
$DB_USER = 'root';
$DB_PASS = '';

$DEFAULT_DURATION = 240;
$BATCH_SIZE = 2000;

// ================== INPUT ==================
if ($argc < 2) {
    fwrite(STDERR, "Uso: php import_songs_from_csv.php \"C:\\path\\file.csv\"\n");
    exit(1);
}
$csvPath = $argv[1];
if (!is_file($csvPath)) {
    fwrite(STDERR, "CSV non trovato: $csvPath\n");
    exit(1);
}

// ================== DB ==================
$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

if (defined('PDO::MYSQL_ATTR_LOCAL_INFILE')) {
    $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);
}

// ================== STAGING TABLE ==================
$pdo->exec("
CREATE TABLE IF NOT EXISTS songs_stage (
  artist VARCHAR(255) NULL,
  title VARCHAR(255) NULL,
  duration_seconds VARCHAR(32) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ================== FAST PATH ==================
function tryFastImport(PDO $pdo, string $csvPath, int $defaultDuration): bool {

    $pdo->exec("TRUNCATE TABLE songs_stage");

    $mysqlPath = str_replace('\\', '/', $csvPath);

    $sqlLoad = "
LOAD DATA LOCAL INFILE " . $pdo->quote($mysqlPath) . "
INTO TABLE songs_stage
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@artist, @title, @duration, @p, @e, @mt, @sz, @lw)
SET
  artist = @artist,
  title = @title,
  duration_seconds = @duration
";

    try {
        $pdo->exec($sqlLoad);
    } catch (Throwable $e) {
        return false;
    }

    // Inserimento con durata forzata se non valida
    $pdo->exec("
INSERT INTO songs (title, artist, duration_seconds, created_at, updated_at)
SELECT
  LEFT(TRIM(title), 255),
  NULLIF(LEFT(TRIM(artist), 255), ''),
  CASE
    WHEN TRIM(duration_seconds) REGEXP '^[0-9]+$'
         AND (TRIM(duration_seconds) + 0) > 0
      THEN (TRIM(duration_seconds) + 0)
    ELSE $defaultDuration
  END,
  NOW(), NOW()
FROM songs_stage
WHERE TRIM(title) <> ''
");

    return true;
}

// ================== FALLBACK ==================
function fallbackImport(PDO $pdo, string $csvPath, int $defaultDuration, int $batchSize): void {

    $fh = fopen($csvPath, 'rb');
    if (!$fh) throw new RuntimeException("Impossibile aprire CSV");

    $header = fgetcsv($fh);
    if (!$header) throw new RuntimeException("CSV non valido");

    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    $idx = array_flip($header);

    $stmt = $pdo->prepare("
INSERT INTO songs (title, artist, duration_seconds, created_at, updated_at)
VALUES (:title, :artist, :dur, NOW(), NOW())
");

    $pdo->beginTransaction();
    $count = 0;

    while (($row = fgetcsv($fh)) !== false) {

        $title = trim((string)($row[$idx['title']] ?? ''));
        if ($title === '') continue;

        $artist = trim((string)($row[$idx['artist']] ?? ''));
        $durRaw = trim((string)($row[$idx['duration_seconds']] ?? ''));

        if (!ctype_digit($durRaw) || (int)$durRaw <= 0) {
            $dur = $defaultDuration;
        } else {
            $dur = (int)$durRaw;
        }

        $stmt->execute([
            ':title'  => mb_substr($title, 0, 255),
            ':artist' => $artist !== '' ? mb_substr($artist, 0, 255) : null,
            ':dur'    => $dur,
        ]);

        $count++;

        if ($count % $batchSize === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            echo "Importati: $count\n";
        }
    }

    $pdo->commit();
    fclose($fh);

    echo "Importati totali: $count\n";
}

// ================== RUN ==================
echo "CSV: $csvPath\n";

if (!tryFastImport($pdo, $csvPath, $DEFAULT_DURATION)) {
    echo "LOCAL INFILE non disponibile, uso fallback.\n";
    fallbackImport($pdo, $csvPath, $DEFAULT_DURATION, $BATCH_SIZE);
} else {
    echo "Import completato con LOAD DATA (fast path).\n";
}

echo "FINE\n";