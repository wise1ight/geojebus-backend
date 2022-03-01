<?php
//DB include
include('./_auth.php');
include('./_common.php');

$url = "http://appdata.geojebus.kr/admin/geoje/routelistcopy.php?key_code=";

$query = mysqli_query($connect_db,"select * from bus_route where rt_country = '1'");

while ($row = mysqli_fetch_assoc($query))
{
    $curl = curl_init ();
    curl_setopt ($curl, CURLOPT_URL, $url . $row['rt_num']);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($curl, CURLOPT_USERPWD, "kuvh:[password]");
    curl_setopt ($curl, CURLOPT_POST, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec ($curl);
}

?>
