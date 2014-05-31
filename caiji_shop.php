<?php
require './include/myIconv.class.php';

class caiji{

    var $con;

    function  __construct(){

        $this->con = mysql_connect("localhost", "root", "root");
        if (!$this->con)
        {
            die('Could not connect: ' . mysql_error());
        }

        $db_selected=mysql_select_db("caiji", $this->con);
        mysql_query("set names utf8;");

        if (!$db_selected)
        {
            die ("Can\'t use test_db : " . mysql_error());
        }

    }
    function  caiji(){

        $this->__construct();
    }

    function insert($array)
    {
        return mysql_query("INSERT INTO `cits_collect`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')");
    }

    function curl_contents($durl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_REFERER,$durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    function get_title($con){
        preg_match_all("/pre_tit\">(.*)<\/span>/isU", $con, $rs);
        preg_match_all("/<span id=\"maintitle\">(.*)<\/span>/isU", $con, $rs1);
        $news= $rs[1][0].' '.$rs1[1][0];
        return $news;
    }
    function replace_html_tag($string, $tagname, $clear = false){
        $re = $clear ? '' : '1';
        $sc = '/<' . $tagname . '(?:s[^>]*)?>([sS]*?)?</' . $tagname . '>/i';
        return preg_replace($sc, $re, $string);
    }
    function get_content($con){
        preg_match_all("/the_ratings(.*)id=\"placedata\">/isU", $con, $rc);
        preg_match_all("/<dl>(.*)class=\"cont\">/isU", $rc[1][0], $rcc);
        preg_match_all("/<dd>(.*)<\/dd>/isU", $rcc[1][0], $rs);

        preg_match_all("/<table id=\"placedata\">(.*)<\/table>/isU", $con, $rt);
        $string=$rs[1][0].'<br>'.$rt[0][0];

        //去除a標籤 保留內容
        $string =preg_replace("#<a[^>]*>(.*?)</a>#is", "$1",$string);
        //去除 onclick 標籤
        //$string =preg_replace("#onclick=\"[^>]*\"#is", "$1", $string);
        //    clear class
        $string =preg_replace("#class=\"[^>]*\"#is", "$1", $string);
        //    print_r($string);
        return $string;
    }

    function get_href_all($con){
        preg_match_all("/<a href=\"(.*)\"/isU", $con, $rs);
        return $rs[1];
    }
    function get_sin_href_all($con){
        preg_match_all("/<a href=\'(.*)\'/isU", $con, $rs);
        return $rs[1];
    }

    function  get_title_href($con){
        preg_match_all("/<dd class=\"pre_tit\"><a href=\"(.*)\"/isU", $con, $rs);
        return $rs[1];
    }

    function get_img($con){
        preg_match_all("/<img src=\"(.*)\"/isU", $con, $rs);
        return $rs[1];
    }
    function get_src($con){

        preg_match_all("/src=\"(.*)\"/isU", $con, $rs);
        return $rs[1];
    }

    function check_path($filename){
        //检查路径
        $arr_path = explode ( '/', $filename );
        $path = '';
        $cnt = count ( $arr_path ) - 1;
        if($cnt >= 0 && $arr_path[0] == '')
            chdir('/');
        for($i = 0; $i < $cnt; $i ++) {
            if($arr_path [$i] == '') continue;
            $path .= $arr_path [$i] . '/';
            if (! is_dir ( $path ))
                if(!mkdir ( $path ,0755))
                    return false;
        }
        return true;
    }

    function create_img_p($ctrip_path){
        $arr = explode('/', $ctrip_path);
        $filename = end($arr);
        $find_ext= explode('.',$filename);
        $file_ext=end($find_ext);
//    生成path
        $sec_str= substr(strtoupper(md5(md5($ctrip_path))),0,20);
        $cms_filepath='news2/48430FB0657E665E/'.$sec_str.'-'.substr($sec_str,0,1).'/'.$sec_str.'.'.$file_ext;
        return $cms_filepath;
    }


    function page_col_img($ctrip_path){
        if(!$ctrip_path) { exit('cant read it');}
        $arr = explode('/', $ctrip_path);
        $filename = end($arr);
        $find_ext= explode('.',$filename);
        $file_ext=end($find_ext);
//    生成path
        $sec_str= substr(strtoupper(md5(md5($ctrip_path))),0,20);
        $cms_filepath='news2/48430FB0657E665E/'.$sec_str.'-'.substr($sec_str,0,1).'/'.$sec_str.'.'.$file_ext;

        $save_filepath = '/wamp/www/caiji/'.$cms_filepath;
        if(!file_exists($save_filepath))
        {
            $this->check_path($save_filepath);
            $upload_func = 'copy';
            if(@$upload_func($ctrip_path, $save_filepath))
            {
                $ispass = true;
            }
            else
            {
                exit('down lose');
            }
        }
        return $cms_filepath;
    }

    function get_img_con($con){

        preg_match_all("/<div id=\"sub\"(.*)<\/div>/isU", $con, $rs);
//    var_dump($rs);
        return $rs[1][0];
    }

    function down_load($v,$catid,$areaid){

        $sm=$this->curl_contents($v);

        $myIconv = new myIconv();
        $sm=$myIconv->gbk2big5($sm);

//      匹配內容標籤
        $last['title']=$this->get_title($sm);
        $last['link']=$v;
        $last['content']=trim($this->get_content($sm));
//      圖片採集
        $img_conc= $this->get_img_con($sm);
        $pics=$this->get_src($img_conc);
        if($pics){
            $sav_pa='';
            foreach($pics as $pic){
                $sav_pa.=$cms_pa=$this->page_col_img($pic).';';
                $last['content']='<center> <img src="'.$cms_pa.'"></center><br>'.$last['content'];
                if($cms_pa){
                    print 'ok img down'."\n";
                }else{
                    print 'lose down'."\n";
                }
                sleep(0.1);
            }
            $last['pics']=$sav_pa;
        }else{
            print 'no photo'."\n";
        }
//            photo end
        $last['catid']=$catid;
        $last['areaid']=$areaid;
//print_r($last);exit;

        $lolo=$this->insert($last);
//$RE?(print $v.'--導入成功'):(print $v.'--失敗');
        return $lolo;
    }

//採集url
//獲取某個範圍url
    function get_url_all($url,$catid,$areaid){
        $con= $this->curl_contents($url);
        preg_match_all("/class=\"box01_img\"><a href=\"(.*)\"/isU", $con, $rs);
//        $hdrefs22222=$this->get_title_href($rs[1][0]);
        $hdrefs22222=$rs[1];
        print_r($hdrefs22222);
        if($hdrefs22222){
//        var_dump($hrefs);
            foreach($hdrefs22222 as $fufufufu){

                $lolo=$this->down_load($fufufufu,$catid,$areaid);
                $lolo?(print $fufufufu.'--success'."\n"):print $fufufufu.'--false'."\n";
                sleep(0.1);
            }
            print count($hdrefs22222)."download over \n";
        }else{
            print 'page contents downloads lose'."\n";
        }
    }

    function count_pages($add){

        $url=$add[0];
        $catid=$add[1];
        $areaid=$add[2];
        $this->get_url_all($url,$catid,$areaid);
        if($con= $this->curl_contents($url)) {
            print '1 page over'."\n";
        }
        preg_match_all("/previouspostslink(.*)extend/isU", $con, $rs);
        if($rs[1]){
            $hfuck=$this->get_sin_href_all($rs[1][0]);
            print_r($hfuck);
            $cccc_c=1;
            foreach($hfuck as $count_k=> $count_v){
                $this->get_url_all($count_v,$catid,$areaid);
                print ++$cccc_c.'page **'.$count_v."download over \n";
                sleep(0.3);
            }
        }
    }

    function for_pages($add,$start,$all){
        $url=$add[0];
        $catid=$add[1];
        $areaid=$add[2];

        for($i=$start;$i<=$all;++$i){
            $this->get_url_all($url.$i.'/',$catid,$areaid);
            print $i.'page down load over***'.$url.$i.'/'."\n";
        }

    }
    function  __destruct(){
        mysql_close($this->con);
    }

}

