<?php
//DB include
include('./_auth.php');
include('../../common.php');

$db = new db_storage\db();
$connect_db = $db->connect();

function get_content($url) {
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
    $curlsession = curl_init ();
    curl_setopt ($curlsession, CURLOPT_URL,             $url);
    curl_setopt ($curlsession, CURLOPT_HEADER,          0);
    curl_setopt ($curlsession, CURLOPT_RETURNTRANSFER,  1);
    curl_setopt ($curlsession, CURLOPT_POST,            0);
    curl_setopt ($curlsession, CURLOPT_USERAGENT,       $agent);
    curl_setopt ($curlsession, CURLOPT_REFERER,         "");
    curl_setopt ($curlsession, CURLOPT_TIMEOUT,         3);

    $buffer = curl_exec ($curlsession);
    $cinfo = curl_getinfo($curlsession);
    curl_close($curlsession);

    if ($cinfo['http_code'] != 200)
    {
        return "";
    }

    return $buffer;
}

$target_code = $_GET['target_code'];
$bis_code = $_GET['bis_code'];

//노선 시간표 복사
$route_timetable = "";
$route_timetable_data = get_content("http://bis.geoje.go.kr/map/realTimeBusInfo.do?action=vehicleTime&searchLineId=" . $bis_code);
preg_match_all("'<td class=\"tltle03\">(.+)</td>'iU",$route_timetable_data, $route_timetable_i);
for ($k=1; $k<count($route_timetable_i[0]); $k = $k + 2)
{
    $route_timetable = $route_timetable . $route_timetable_i[1][$k] . ":00";
    if($k < count($route_timetable_i[0]) - 1)
        $route_timetable = $route_timetable . ",";
}

echo $route_timetable;
$sql = " update bus_route_additional set rt_depart_time = '$route_timetable' where rt_country = '1' and rt_num = '$target_code' ";
mysqli_query($connect_db,$sql);
echo "처리되었습니다.";

?>