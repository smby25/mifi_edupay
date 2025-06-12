<?php
$host = "localhost";
$user = "rogeliosp";
$pass = "@Rogelio2002";
$dbname = "mifi_db";

// Check if file was uploaded
if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['import_file']['tmp_name'];
    $fileName = $_FILES['import_file']['name'];

    // Make sure it's a .sql file
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    if ($fileExtension !== 'sql') {
        die("Error: Only .sql files are allowed.");
    }

    // Move the file to a temporary path
    $tempPath = __DIR__ . '/temp_' . uniqid() . '.sql';
    move_uploaded_file($fileTmpPath, $tempPath);

    // Path to mysql.exe
    $mysql = "C:\\xampp\\mysql\\bin\\mysql.exe";

    // Build the import command
    $command = "\"{$mysql}\" --user={$user} --password=\"{$pass}\" --host={$host} {$dbname} < \"{$tempPath}\"";

    // Execute the command
    $output = null;
    $returnVar = null;
    exec($command, $output, $returnVar);

    // Remove the temp file
    unlink($tempPath);

    if ($returnVar === 0) {
        echo "<script>alert('Database import successful.'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Database import failed. Error code: {$returnVar}'); window.location.href='../index.php';</script>";
    }
} else {
    echo "<script>alert('No file uploaded or upload failed.'); window.location.href='../index.php';</script>";
}
?>
