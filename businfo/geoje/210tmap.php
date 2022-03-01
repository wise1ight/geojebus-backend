<?php
//파라미터 받기
$type = $_GET['type'];
$bis_code = $_GET['bis_code'];

function timeToSeconds($time)
{
    $timeExploded = explode(':', $time);
    if (isset($timeExploded[2])) {
        return $timeExploded[0] * 3600 + $timeExploded[1] * 60 + $timeExploded[2];
    }
    return $timeExploded[0] * 3600 + $timeExploded[1] * 60;
}

function getGap($startX,$startY,$endX,$endY)
{
    $params = array(
        'version' => 1,
        'startX' => $startX,
        'startY' => $startY,
        'endX' => $endX,
        'endY' => $endY,
        'reqCoordType' => "WGS84GEO",
        'resCoordType' => "WGS84GEO"
    );

    $url = 'https://apis.skplanetx.com/tmap/routes' . '?' . http_build_query($params, '', '&');

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

    return $totalTime;
}

//DB include
include('./_common.php');
//Api Key
$api_key = "GKU%2F%2F59MuAt1RLAvkdKpGJ%2Bm51UJp2MDRoOlAnLveR4hmSf%2FXs%2BtYsqKjC3oJ%2B9eK5uaKVKcffKK5TUyuhKzZw%3D%3D";

//OpenAPI 연결
libxml_use_internal_errors(true);
//$url = "http://data.geoje.go.kr/rfcapi/rest/geojebis/getGeojebisBusarrive?authApiKey=$api_key&pageSize=65535";
$url = "http://bis.geoje.go.kr/OpenAPI/busArrive.jsp?pageSize=65535";
$response = file_get_contents($url);
$response = iconv("EUC-KR", "UTF-8", $response);
$response = preg_replace('~\s*(<([^-->]*)>[^<]*<!--\2-->|<[^>]*>)\s*~','$1',$response);

$xml = simplexml_load_string($response);

if ($xml === false) {
    echo "XML 파싱 실패: "; //서버 간 통신 안됨
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
    return ;
} else {
    if ($xml->header->resultCode == "00")
    {
        $count = $xml->body->TotalCount;
        mysqli_query($connect_db, "TRUNCATE TABLE bus_realtime_geoje");
        for($i=0;$i<$count;$i++)
        {
            $num = $xml->body->data->list[$i]->num;
            $bus_no = $xml->body->data->list[$i]->bus_no;
            $line_name = $xml->body->data->list[$i]->line_name;
            $key_code = $xml->body->data->list[$i]->key_code;
            $lon = $xml->body->data->list[$i]->lon;
            $lat = $xml->body->data->list[$i]->lat;
            $busstop_name = $xml->body->data->list[$i]->busstop_name;
            $busstop_id = $xml->body->data->list[$i]->busstop_id;
            $ars_id = $xml->body->data->list[$i]->ars_id;
            $busstop_seq = $xml->body->data->list[$i]->busstop_seq;


            mysqli_query($connect_db, "insert into bus_realtime_geoje set re_idx = '$num',
															re_rt_num = '$key_code',
															re_st_num = '$busstop_id',
															re_st_seq = '$busstop_seq',
															re_lng = '$lon',
															re_lat = '$lat',
															re_vc_plate = '$bus_no'");

        }
    } else if ($xml->header->resultCode == "99") {
        echo "BIS에서 정보를 제공하지 않음";
        return ;
    }
}

if ($type == 'route') {
    $rows = array();
    $rows['request'] = "success";
    $query = mysqli_query($connect_db,"select re_st_num,re_st_seq,re_lng,re_lat,re_vc_plate from bus_realtime_geoje where re_rt_num = '$bis_code' ORDER BY re_st_seq ASC");
    $rows['data'] = array();
    while($r = mysqli_fetch_assoc($query)) {
        $rows['data'][] = $r;
    }
    echo json_encode($rows);
} else if ($type == 'stop') {
    $rows = array();
    $rows['request'] = "success";
    $query = mysqli_query($connect_db,"select re_rt_num,re_st_num,re_st_seq,re_lng,re_lat,re_vc_plate from bus_realtime_geoje where re_rt_num in (select rt_num as 're_rt_num' from bus_route where rt_country='1' and CONCAT(',',rt_stop_list,',') like '%,$bis_code,%')");
    $rows['data'] = array();
    //버스 도착 정보 추가
    while($r = mysqli_fetch_assoc($query)) {
        $vstop_list = mysqli_fetch_assoc(mysqli_query($connect_db, "select rt_country, rt_name, rt_stop_list from bus_route where rt_country='1' and rt_num='" . $r["re_rt_num"] . "'"));
        $vstop = explode(',', $vstop_list['rt_stop_list']);
        $bseq = count($vstop) - array_search($r["re_st_num"], array_reverse($vstop)); // 버스위치
        $sseq = count($vstop) - array_search($bis_code, array_reverse($vstop)); // 정류장 위치 -> 노선순이기 때문에 정확

        if($bseq >= $sseq)
            continue;

        $stop_info = mysqli_fetch_assoc(mysqli_query($connect_db, "select st_name from bus_stop where st_country='1' and st_num='" . $r["re_st_num"] . "'"));
        $end_stop_info = mysqli_fetch_assoc(mysqli_query($connect_db, "select st_lng,st_lat from bus_stop where st_country='1' and st_num='" . $vstop[$bseq] . "'"));
        $gap_list = mysqli_fetch_assoc(mysqli_query($connect_db, "select rt_stop_gap from bus_route_additional where rt_country='1' and rt_num='" . $r["re_rt_num"] . "'"));
        $gap = explode(',', $gap_list['rt_stop_gap']);
        $lead_time = getGap($r['re_lng'],$r['re_lat'],$end_stop_info['st_lng'],$end_stop_info['st_lat']);
        for($i=0;$i<$sseq - $bseq - 1;$i++)
        {
            $lead_time += timeToSeconds($gap[$bseq  + $i]);
        }
        $result['route_country'] = $vstop_list['rt_country'];
        $result['route_name'] = $vstop_list['rt_name'];
        $result['wait_time'] = (int)($lead_time / 60);
        $result['position'] = $stop_info['st_name'];
        $result['position_num'] = $sseq - $bseq;
        $result['vehicle_num'] = $r['re_vc_plate'];
        $result['route_bis'] = $r['re_rt_num'];
        $rows['data'][] = $result;
    }
    
    //도착 시간에 따라 정렬
    usort($rows['data'], function($a, $b) {
        return $a['wait_time'] - $b['wait_time'];
    });
    echo json_encode($rows);
}

?>