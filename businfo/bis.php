<?php
$platform_type = $_GET['platform']; //앱 플랫폼
$app_version = $_GET['app_version']; //앱 버전
$app_version_code = $_GET['app_version_code']; //앱 버전 코드
$country = $_GET['country']; // 해당 지역 BIS 코드

if(($platform_type == 'android' && $country == '1' && $app_version = '2.1.0') ||
    ($platform_type == 'android' && $country == '1' && $app_version_code = '210')) {
    include_once("./geoje/210.php");
} else {
    echo "잘못된 요청";
}

?>
