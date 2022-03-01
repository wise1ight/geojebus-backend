<?php
//DB include
include('../common.php');

$db = new db_storage\db();
$connect_db = $db->connect();

$st_country = $_GET['st_country'];
$st_num = $_GET['st_num'];

$query = mysqli_query($connect_db,"select * from bus_stop_roadview where st_country = '$st_country' and st_num = '$st_num'");
$row = mysqli_fetch_assoc($query);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
html, body, #map {margin: 0; padding: 0; width: 100%; height: 100%}
</style>
<script type="text/javascript" src="http://apis.daum.net/maps/maps3.js?apikey=24b1ba272b15ef2bc01d0241f18740a481b1240c" charset="utf-8"></script>
<script type="text/javascript">
function init() {
var p = new daum.maps.LatLng(0, 0);
var rc = new daum.maps.RoadviewClient();
var rv = new daum.maps.Roadview(document.getElementById("map"));

rc.getNearestPanoId(p, 50, function(panoid) {
rv.setPanoId(<?=$row['st_panoId'] ?>, p);
rv.setViewpoint({ pan: <?=$row['st_pan'] ?>, tilt: <?=$row['st_tilt'] ?>, zoom: <?=$row['st_zoom'] ?> });
});
}
</script>
</head>
<body onload="init()">
	<div id="map"></div>
</body>
</html>
