<?php
//DB include
include('./_auth.php');
include('./_common.php');

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

$query = mysqli_query($connect_db, "select * from bus_route");

while ($row = mysqli_fetch_assoc($query))
{
    $key_code = $row['rt_num'];

    $c = mysqli_fetch_assoc(mysqli_query($connect_db,"select count(*) from bus_route_additional where rt_country = '1' and rt_num = '$key_code'"));
    $sql = "";
    $sql_common = "	rt_country		= '1',
					rt_type	= '1'";
	if($c["count(*)"] < 1) {
        $sql = " insert into bus_route_additional set rt_num = '$key_code', $sql_common ";
        echo $sql;
    } else {
        $sql = " update bus_route_additional set $sql_common where rt_country = '1' and rt_num = '$key_code' ";
    }
    echo "$key_code 노선 생성함<br>";
    mysqli_query($connect_db,$sql);

}

?>