<?php
if (false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
set_time_limit(0);

require 'custom/include/ExportImportTree/src/ExportTreeToJson.php';

$db = \DBManagerFactory::getInstance();

$id = $_REQUEST['id']; // $db->quoted();
$module = $_REQUEST['export_module']; // $db->quoted();

header('Content-type: application/json');
header("Content-Description: File Transfer");
// header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"{$module}.{$id}.json\"");

echo json_encode((new ExportTreeToJson)->export($module, $id));
