<?php
set_time_limit(0);

header("Content-type: text/html; charset=utf-8");
require './caiji.php';
$cai = new caiji();

$arr = array(
    array('http://www.nihontu.com/gourmet/page/', 276, 85),
    array('http://www.nihontu.com/sightseeing/page/', 337, 85),
);
$cai->for_pages($arr[1],1,47);
/*foreach ($arr as $all_v) {
    $cai->count_pages($all_v);
    print $all_v[0].'*****category download over'."\n";
}*/