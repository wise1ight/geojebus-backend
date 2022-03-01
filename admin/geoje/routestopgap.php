<?php
//DB include
include('./_auth.php');
include('./_common.php');

function getGap($startX,$startY,$endX,$endY)
{
    $params = array(
        'version'		=>	1,
        'startX'		=>	$startX,
        'startY'		=>	$startY,
        'endX'			=>	$endX,
        'endY'			=>	$endY,
        'reqCoordType'	=>	"WGS84GEO",
        'resCoordType'	=>	"WGS84GEO"
    );

    $url = 'https://apis.skplanetx.com/tmap/routes'.'?'.http_build_query($params, '', '&');

    $curl_session = curl_init();
    curl_setopt($curl_session, CURLOPT_URL, $url);
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_session, CURLOPT_HTTPHEADER, array(
        'appkey: 8b949149-0d5f-3df0-bc2b-f50e9112d13d',
        'Content-Type: application/json'
    ));

    $result = curl_exec($curl_session);
    $result = json_decode($result);

    $totalTime = $result->features[0]->properties->totalTime * 0.9;
    curl_close($curl_session);

    return gmdate("H:i:s", $totalTime);
}

$key_code = $_GET['rt_num'];
$row = mysqli_fetch_assoc(mysqli_query($connect_db,"select * from bus_route where rt_country = '1' and rt_num = '$key_code'"));

//while ($row = sql_fetch_array($query))
//{
    $rt_stop_list = $row['rt_stop_list'];
    $stops = explode(',' ,$rt_stop_list);
    $rt_stop_gap = "";
    for($i=0; $i < count($stops) - 1; $i++)
    {
        $start = mysqli_fetch_assoc(mysqli_query($connect_db,"select * from bus_stop where st_country = '1' and st_num = '$stops[$i]'"));
        $estop = $stops[$i+1];
        $stop = mysqli_fetch_assoc(mysqli_query($connect_db,"select * from bus_stop where st_country = '1' and st_num = '$estop'"));
        $startX = $start['st_lng'];
        $startY = $start['st_lat'];
        $stopX = $stop['st_lng'];
        $stopY = $stop['st_lat'];
        $gap = getGap($startX,$startY,$stopX,$stopY);
        if($i < count($stops) - 2) {
            $rt_stop_gap = $rt_stop_gap . $gap . ",";
        } else {
            $rt_stop_gap = $rt_stop_gap . $gap;
        }
    }

    $sql = " update bus_route_additional set rt_stop_gap = '$rt_stop_gap' where rt_country = '1' and rt_num = '$key_code' ";
    echo "$key_code 노선 생성함<br>";
    mysqli_query($connect_db,$sql);


//}

?>