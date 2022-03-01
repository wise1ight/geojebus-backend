<?php
//DB include
include('./_auth.php');
include('./_common.php');

//OpenAPI 연결
libxml_use_internal_errors(true);
$url = "http://bis.geoje.go.kr/OpenAPI/busLine.jsp";
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
				$rt_country = "1";
				$num = $xml->body->data->list[$i]->num;
				$line_name = $xml->body->data->list[$i]->line_name;
				$dir_up_name = $xml->body->data->list[$i]->dir_up_name;
				$dir_down_name = $xml->body->data->list[$i]->dir_down_name;
				$key_code = $xml->body->data->list[$i]->key_code;
			
				$sql_common = "	rt_country		= '$rt_country',
								rt_name			= '$line_name',
								rt_name_extra	= '$dir_up_name~$dir_down_name'";
				
				$row = mysqli_fetch_assoc(mysqli_query($connect_db,"select count(*) from bus_route where rt_country = '1' and rt_num = '$key_code'"));
				$sql = "";
				if($row["count(*)"] < 1) {
					$sql = " insert into bus_route set rt_num = '$key_code', $sql_common ";
				} else {
					$sql = " update bus_route set $sql_common where rt_num = '$key_code' ";
				}
				echo "$key_code $line_name 노선 생성함<br>";
				mysqli_query($connect_db,$sql);
			}
		}
		
		
		
		echo "정상 처리됨";
	} else if ($xml->header->resultCode == "99") {
		echo "BIS에서 정보를 제공하지 않음";
	}
}
 ?> 