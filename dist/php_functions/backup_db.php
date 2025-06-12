<?php

$host = "localhost";
$user = "rogeliosp";
$pass = "@Rogelio2002";
$dbname = "mifi_db";

date_default_timezone_set('Asia/Manila');
$backupFile = "backup_{$dbname}_" . date("Ymd_His") . ".sql";

// Correct path to mysqldump
$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";

// Build the command with the correct quoting for Windows
$command = "\"{$mysqldump}\" --user={$user} --password=\"{$pass}\" --host={$host} {$dbname}";

// Set download headers
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=\"{$backupFile}\"");
header('Pragma: no-cache');
header('Expires: 0');

// Execute and stream output
passthru($command, $resultCode);

// Show error if needed
if ($resultCode !== 0) {
    echo "Error: Backup failed with code $resultCode.";
}

exit;
?>
