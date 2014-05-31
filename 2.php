<?php
set_time_limit(0);

header("Content-type: text/html; charset=utf-8");
require './caiji_shop.php';
$cai = new caiji();

$arr = array(
    array('http://www.nihontu.com/shopping/page/', 347, 85),
);
$cai->for_pages($arr[0],1,41);
/*foreach ($arr as $all_v) {
    $cai->count_pages($all_v);
    print $all_v[0].'*****category download over'."\n";
}*/