<?php
include_once("../common.php");

$db = new db_storage\db();
$connect_db = $db->connect();

$platform_type = $_GET['platform'];
$app_version = $_GET['app_version'];
$debug = $_GET['debug'];
$app_debug = (int)filter_var($debug, FILTER_VALIDATE_BOOLEAN); 

$latest_version_result = mysqli_fetch_assoc(mysqli_query($connect_db,"SELECT * FROM bus_app WHERE app_platform = '$platform_type' and app_debug = '$app_debug' ORDER BY app_version DESC "));
$request_version_result = mysqli_fetch_assoc(mysqli_query($connect_db,"SELECT * FROM bus_app WHERE app_platform = '$platform_type' and app_debug = '$app_debug' and app_version = '$app_version'"));

$total = array("latest_app_version" => $latest_version_result['app_version'],"app_update_force" => (bool)$request_version_result['app_update_force'],"db_version" => $request_version_result['app_db_version']);
echo json_encode( $total );
?>
