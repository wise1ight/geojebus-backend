<?php
$params = array(
	'version'		=>	1,
	'startX'		=>	"126.98217734415019",
	'startY'		=>	"37.56468648536046",
	'endX'			=>	"129.07579349764512",
	'endY'			=>	"35.17883196265564",
	'reqCoordType'	=>	"WGS84GEO",
	'resCoordType'	=>	"WGS84GEO"
);

$url = 'https://apis.skplanetx.com/tmap/routes'.'?'.http_build_query($params, '', '&');

$curl_settion = curl_init();
curl_setopt($curl_settion, CURLOPT_URL, $url);
curl_setopt($curl_settion, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_settion, CURLOPT_HTTPHEADER, array(
		'appkey: 8b949149-0d5f-3df0-bc2b-f50e9112d13d',
		'Content-Type: application/json'
));

$result = curl_exec($curl_settion);
$result = json_decode($result);

$totalTime = $result->features[0]->properties->totalTime;
$totalDistance = $result->features[0]->properties->totalDistance;
echo "총 소요시간 : " . gmdate("H시 i분 s초", $totalTime);
echo "<br> 총 거리 : " . $totalDistance / 1000 . "km";
curl_close($curl_session); 

?>