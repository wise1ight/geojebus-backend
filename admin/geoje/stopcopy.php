<?php
//DB include
include('./_auth.php');
include('./_common.php');

//OpenAPI 연결
libxml_use_internal_errors(true);
$url = "http://bis.geoje.go.kr/OpenAPI/busstop.jsp";
//인코딩 변환
$response = iconv("EUC-KR","UTF-8",file_get_contents($url));

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
		$pageSize = $xml->body->pageSize;

		for($index=1;$index<=ceil($count/$pageSize);$index++)
		{
			$response = iconv("EUC-KR","UTF-8",file_get_contents($url . "?startPage=$index"));
			$response = preg_replace('~\s*(<([^-->]*)>[^<]*<!--\2-->|<[^>]*>)\s*~','$1',$response);
			$xml = simplexml_load_string($response);
			$c = count($xml->body->data->list);
			for($i=0;$i<$c;$i++)
			{
				$st_country = "1";
				$num = $xml->body->data->list[$i]->num;
				$ars_id = $xml->body->data->list[$i]->ars_id;
				$name = $xml->body->data->list[$i]->name;
				$busstop_id = $xml->body->data->list[$i]->busstop_id;
				$lon = $xml->body->data->list[$i]->lon;
				$lat = $xml->body->data->list[$i]->lat;
				//$nextbusstop = $xml->body->data->list[$i]->nextbusstop;

				$sql_common = "	st_country	= '$st_country',
								st_name		= '$name',
								st_ars		= '$ars_id',
								st_lng		= '$lon',
								st_lat		= '$lat'";

				$row = mysqli_fetch_assoc(mysqli_query($connect_db,"select count(*) from bus_stop where st_num = '$busstop_id'"));
				$sql = "";
				if($row["count(*)"] < 1) {
					$sql = " insert into bus_stop set st_num = '$busstop_id', $sql_common ";
				} else {
					$sql = " update bus_stop set $sql_common where st_num = '$busstop_id' ";
				}
				echo "$busstop_id $name 정류장 생성함<br>";
				mysqli_query($connect_db,$sql);
			}
		}

		echo "정상 처리됨";
	} else if ($xml->header->resultCode == "99") {
		echo "BIS에서 정보를 제공하지 않음";
	}
}
 ?> 