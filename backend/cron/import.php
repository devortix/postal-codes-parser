<?php
$startMemory = memory_get_usage();
$starTime = time();

use Dotenv\Dotenv;
use Rap2hpoutre\FastExcel\FastExcel;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable('/config/', null, true);

$dotenv->load();

// Перевірка обов'язкових змінних
$dotenv->required(['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASS', 'ZIP_URL']);

// Параметри бази даних
$dsn = sprintf(
    "mysql:host=%s;dbname=%s;charset=utf8",
    $_ENV['MYSQL_HOST'],
    $_ENV['MYSQL_DATABASE']
);
$username = $_ENV['MYSQL_USER'];
$password = $_ENV['MYSQL_PASS'];


// Константи
define('ZIP_URL', $_ENV['ZIP_URL']);
const RUNTIME_PATH = 'runtime/';
const ZIP_PATH = RUNTIME_PATH . 'postindex.zip';
const UNZIP_DIR = RUNTIME_PATH . 'unzipped/';


// Кількість рядків для пакетного запису
$batchSize = 1000;


try {
    // Створення папки
    if (!is_dir(RUNTIME_PATH)) {
        mkdir(RUNTIME_PATH, 0777, true);  // 0777 - права доступу, true - створює всі батьківські директорії
    }

    // Завантаження ZIP-файлу
    echo "Downloading ZIP archive..." . PHP_EOL;
    downloadZip(ZIP_URL, ZIP_PATH);

    // Розпаковка ZIP-архіву
    echo "Unzipping archive..." . PHP_EOL;
    $extractedFilePath = unzipFile(ZIP_PATH, UNZIP_DIR);

    if (!$extractedFilePath || !file_exists($extractedFilePath)) {
        throw new Exception("Failed to find or extract the XLSX file.");
    }

    echo "File extracted to: $extractedFilePath" . PHP_EOL;

    // Підключення до бази даних
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to the database successfully!" . PHP_EOL;

    // Зчитування та обробка XLSX-файлу
    $rowCount = processXlsxFile($extractedFilePath, $pdo, $batchSize);

    // Видалення файлів
    deleteDirectory();

    // Статистика ресурсів
    $peakMemory = memory_get_peak_usage();

    echo PHP_EOL;
    echo "Processed $rowCount rows successfully!" . PHP_EOL;
    echo "Максимальне використання пам'яті: " . intval($peakMemory / 1024 / 1024) . 'МБ' . PHP_EOL;
    echo "Час роботи: " . (time() - $starTime) . 'сек' . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

/**
 * Завантаження ZIP-архіву з URL
 *
 * @param string $url
 * @param string $destination
 * @return void
 */
function downloadZip(string $url, string $destination): void
{
    $fileContent = file_get_contents($url);

    if ($fileContent === false) {
        throw new Exception("Failed to download ZIP file from $url");
    }

    file_put_contents($destination, $fileContent);
    echo "ZIP file downloaded to $destination" . PHP_EOL;
}

/**
 * Розпаковка ZIP-архіву
 *
 * @param string $zipPath
 * @param string $unzipDir
 * @return string|null Шлях до розпакованого XLSX-файлу
 */
function unzipFile(string $zipPath, string $unzipDir): ?string
{
    $zip = new ZipArchive();

    if ($zip->open($zipPath) === true) {
        $zip->extractTo($unzipDir);
        $zip->close();

        echo "ZIP file extracted to $unzipDir" . PHP_EOL;

        // Повертаємо шлях до XLSX-файлу
        $extractedFiles = scandir($unzipDir);
        foreach ($extractedFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'xlsx') {
                return $unzipDir . $file;
            }
        }
    } else {
        throw new Exception("Failed to open ZIP file: $zipPath");
    }

    return null;
}

/**
 * Обробка XLSX-файлу та запис у БД
 *
 * @param string $filePath
 * @param PDO $pdo
 * @param int $batchSize
 * @return int Кількість оброблених рядків
 */
function processXlsxFile(string $filePath, PDO $pdo, int $batchSize): int
{
    $fastExcel = new FastExcel();
    $batchData = [];
    $rowCount = 0;

    // массив кодів які наявні у файлі.
    $ids = [];

    $fastExcel->import($filePath, function ($row) use (&$batchData, &$rowCount, &$ids, $batchSize, $pdo) {
        $data = array_values(array_slice($row, 1));

        $batchData[] = "(" . implode(
                ",",
                array_map(
                    function ($value) {
                        // Перевіряємо, чи значення порожнє
                        return $value === '' ? "NULL" : "'" . addslashes($value) . "'";
                    },
                    $data
                )
            ) . ")";
        $rowCount++;

        if (isset($data[4])) {
            $ids[] = $data[4];
        }

        // Коли пакет заповнено, записуємо в БД
        if (count($batchData) >= $batchSize) {
             insertBatchIntoDatabase($pdo, $batchData);
             $batchData = [];
        }

        return null; // Повертаємо null, щоб уникнути додаткового збереження
    });

    // Записуємо залишки
    if (!empty($batchData)) {
         insertBatchIntoDatabase($pdo, $batchData);
    }

    deleteOldRecords($pdo, $ids);

    return $rowCount;
}

/**
 * Пакетний запис даних у БД
 *
 * @param PDO $pdo
 * @param array $batchData
 */
function insertBatchIntoDatabase(PDO $pdo, array $batchData): void
{
    $columns = ['region', 'district_old', 'district', 'city', 'code', 'region_slug', 'district_slug', 'city_slug', 'office_name', 'office_slug', 'office_code'];
    $columnsStr = implode(',', $columns);


    $columnsForUpdateStr = implode(',', array_map(function ($name) {
        return "$name=VALUES($name)";
    }, $columns));

    $sql = "INSERT INTO postal_codes ($columnsStr) VALUES ";
    $sql_end = " ON DUPLICATE KEY UPDATE $columnsForUpdateStr;";


    $pdo->exec($sql . implode(',', $batchData) . $sql_end);

    echo "Inserted " . count($batchData) . " rows into the database." . PHP_EOL;

}

function deleteOldRecords(PDO $pdo, array $ids = []): void
{
    // Ітераційно отримуємо ID з бази частинами
    $batchSize = 200;  // Розмір партії для отримання даних
    $offset = 0; // Початковий офсет
    $total = 0;

    do {
        // Отримуємо частину записів з бази даних
        $stmt = $pdo->prepare("SELECT code FROM postal_codes LIMIT :offset, :batchSize");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':batchSize', $batchSize, PDO::PARAM_INT);
        $stmt->execute();

        // Отримуємо всі ID з бази
        $recordsInDB = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Якщо є ID для видалення, порівнюємо їх з масивом з файлу
        $idsToDelete = array_diff($recordsInDB, $ids);

        // Якщо є ID для видалення, виконуємо запит на видалення
        if (!empty($idsToDelete)) {
            echo implode(',',$idsToDelete);
            // Запит для видалення цих записів
            $idsToDeleteStr = implode(',', $idsToDelete);
            $deleteStmt = $pdo->prepare("DELETE FROM postal_codes WHERE code IN ($idsToDeleteStr)");
            $deleteStmt->execute();

            $total += count($idsToDelete);
        }

        // Оновлюємо офсет для наступної партії
        $offset += $batchSize;

    } while (count($recordsInDB) === $batchSize);

    echo "Видалено " . $total . " записів." . PHP_EOL;
}

/**
 * Видалення папки
 *
 * @param string $dir
 * @return bool
 */
function deleteDirectory(string $dir = RUNTIME_PATH): bool
{

    if (!is_dir($dir)) {
        return false;
    }

    // Відкриваємо
    $files = array_diff(scandir($dir), array('.', '..'));

    // Перебираємо всі файли та папки в директорії
    foreach ($files as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;

        // Якщо це файл, видаляємо його
        if (is_file($filePath)) {
            unlink($filePath);
        } // Якщо це директорія, викликаємо функцію рекурсивно
        elseif (is_dir($filePath)) {
            deleteDirectory($filePath);
        }
    }

    // Після того, як всі файли та підкаталоги видалені, видаляємо саму директорію
    rmdir($dir);

    return true;
}