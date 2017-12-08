<!DOCTYPE html>
<html>

<head>
<meta charset="utf-8">
<meta name="robots" content="noindex">
</head>
<body>
<?php
header ("Content-Type: text/html;charset=utf-8");


$site = array('https://samarapb.ru/');//синонимы домена в одинарных кавычках через запятую

//скрипт определяет позиции сайта в гугле по поисковым запросам из файла. глубина поиска 100
//версия 0.2
//автор phpdreamer , отредактировано в связи с изменениями в google (07.05.2010)

//@set_time_limit(0);
set_time_limit(10000); 
@error_reporting(E_ALL);

$keywords = file('words.txt'); //файл с ключевыми словами


foreach($keywords as $word)
{
    echo '<small><i>' . $word.'...</i></small><br>';
    $lk = getGoogleLinks(trim($word));
    foreach($lk as $n=>$url)
        if(IsMyDomen($url, $site))
        {
            echo '<b>Фраза: </b>' . $word . ' <b>Место: </b>' ;
            echo colorate($n+1) ;
            @flush();
        }
//print_r( $lk);
//if(!IsMyDomen($url, $site)){ echo ' <b>Фраза: </b>' . $word . ' <b>Место: 0</b><br/>'; }
}

function getGoogleLinks($keyword)
{
    $countPage = 25;
    $pageNum = 1;

/*stream_context_set_default(
array(
    'http' => array(
        'proxy' => 'tcp://192.168.4.10:3128',
        'request_fulluri' => true,
    ))
);
*/
    $url = 'http://www.google.ru/search?q=' . urlencode( $keyword) . '&num='.$countPage.'&hl=ru&start=' . $pageNum . '&ie=utf8&oe=utf8';
    $page = file_get_contents($url);
//print_r($page);

//$url = "http://www.pirob.com/";
//print_r( get_headers($url) );
//echo file_get_contents($url);

//$cxContext = stream_context_create($aContext);
//$page = file_get_contents("http://www.google.com", False, $cxContext);


//echo "555";

    if(!$page)
        $page = curlgoogle($url);
       
    if(!$page)
    {
        echo 'Page didn\'t downloaded<br>';
        return array();
    }
    else
    {
//echo "555";
//echo $page;
//echo "555";       
        if(preg_match_all('#<h3 class="r"><a href="(.*?)".*?</h3>#', $page, $match)) 
{ 
//echo "<p style='color:red'>";
//print_r($match['1']);
//echo "</p>";

//        if(preg_match_all('#<h3 class="r"><a href="(.*?)"></a></h3>#', $page, $match)) 
//        if(preg_match_all('/<h3 class="r"><a href="/url?q=(.+?)"/is', $page, $match))
// if (!preg_match('/^https?/', $page) && preg_match('/q=(.+)&amp;sa=/U', $page, $match) && preg_match('/^https?/', $match[1])) 

       //    print_r($match[1]);
            return $match['1']; 
}
        else
          print('По запросу "'.$keyword.'" линков в гугле нет ?<br>');
          return array();
    }
}

function IsMyDomen($url, $Array)
{
    $U1 = explode('/', $url);
//print_r($U1);
    foreach($Array as $url2)
    {
        $U2 = explode('/', $url2);
//Меняем
//        if($U1['3'] == $U2['2'])
        if($U1['3'] == $U2['2'])
            return true;
    }
    return false;
}

function colorate($int)
{
    $color = '#FF0000';  //красный
    if($int<=10)
        $color = '#008E00'; //зеленый
    if($int>10  && $int<=20)
        $color = '#FFE500'; //желтый
    return '<span style="color: ' . $color . '">' . $int . '</span><br />';
}

function curlgoogle($url)
{

    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
//    curl_setopt($curl, CURLOPT_PROXY, "192.168.4.10:3128");
//    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); 
//    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/7.0.1');
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,300);
            curl_setopt($curl, CURLOPT_COOKIEFILE, getcwd() . '/cookieG.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR,  getcwd() . '/cookieG.txt');
//    curl_setopt($curl, CURLOPT_COOKIE, "login=some;password=123456");
//curl_setopt($cl, CURLOPT_COOKIEJAR, realpath('cookie.txt'));  

    return curl_exec($curl);
}
?>
</body>
</html>