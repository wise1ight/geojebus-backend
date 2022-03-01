<?php
//DB include
include('./_auth.php');
include('./_common.php');

//OpenAPI 연결
libxml_use_internal_errors(true);
$key_code = $_GET['key_code'];
$url = "http://bis.geoje.go.kr/OpenAPI/busLineList.jsp?key_code=$key_code";
//인코딩 변환
$response = iconv("EUC-KR","UTF-8",file_get_contents($url));
$response = preg_replace('~\s*(<([^-->]*)>[^<]*<!--\2-->|<[^>]*>)\s*~','$1',$response);

$xml = simplexml_load_string($response);
if ($xml === false) {
    echo "XML 파싱 실패: "; //서버 간 통신 안됨
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
} else {
	if ($xml->header->resultCode == "00")
	{
		$count = count($xml->body->data->list->ars_id);
		echo $count . " 개 발견됨.";
		$list_result = "";
		$li=0;
		for(;$li<$count - 1;$li++)
		{
			$list_result = $list_result . $xml->body->data->list->busstop_id[$li] . ",";
		}
		$list_result = $list_result . $xml->body->data->list->busstop_id[$li];
		
		$sql_common = "	rt_stop_list = '$list_result'";
		
		$sql = " update bus_route set $sql_common where rt_country = '1' and rt_num = '$key_code' ";
				
		mysqli_query($connect_db, $sql);
		
		echo "정상 처리됨";
	} else if ($xml->header->resultCode == "99") {
		echo "BIS에서 정보를 제공하지 않음";
	}
}
 ?> 