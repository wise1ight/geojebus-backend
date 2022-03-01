<?php
//DB include
include('../common.php');
$db = new db_storage\db();
$connect_db = $db->connect();
//Api Key
$api_key = "GKU%2F%2F59MuAt1RLAvkdKpGJ%2Bm51UJp2MDRoOlAnLveR4hmSf%2FXs%2BtYsqKjC3oJ%2B9eK5uaKVKcffKK5TUyuhKzZw%3D%3D";

//OpenAPI 연결
libxml_use_internal_errors(true);
$url = "http://data.geoje.go.kr/rfcapi/rest/geojebis/getGeojebisBusarrive?authApiKey=$api_key&pageSize=65535";
$response = file_get_contents($url);

$xml = simplexml_load_string($response);

if ($xml === false) {
    echo "XML 파싱 실패: "; //서버 간 통신 안됨
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
} else {
	if ($xml->header->resultCode == "00")
	{
		$count = $xml->body->TotalCount;
		//테이블 데이터를 날리고
		sql_query("TRUNCATE TABLE bus_realtime_geoje");
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

			
			sql_query("insert into bus_realtime_geoje set re_idx = '$num',
															re_rt_num = '$key_code',
															re_st_num = '$busstop_id',
															re_st_seq = '$busstop_seq',
															re_lng = '$lon',
															re_lat = '$lat',
															re_vc_plate = '$bus_no'");

		}
		echo "등록 완료";
	} else if ($xml->header->resultCode == "99") {
		echo "BIS에서 정보를 제공하지 않음";
	}
}
 ?> 
