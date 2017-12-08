<?php
header ("Content-Type: text/html;charset=utf-8");
include("settings.php");

set_time_limit(10000); 
$pause2 = 1;  // pause between sites
$pause1 = 1;  // pause between keys
$raws = file_get_contents ("keys.txt");


if (PHP_SAPI === 'cli') {
    $yandex_region = $argv[1];
    $raws = file_get_contents ($argv[2]);
}
else {
	$yandex_region = htmlspecialchars($_GET["lr"]);

}
  echo "Region: $yandex_region<br/>";
$log = fopen( 'log.html' ,"a"); $date = date('j.m.Y G:i'); fputs( $log, $date." >> Start parsing Yandex XML<br/>" ); fclose($log);


/////////////////////  go check positions
$raw = explode( '=' , $raws ); 
foreach ($raw as $x) 
{
    $part = explode( '::', $x ); $domain = trim($part[0]);
    $keys = explode( ':' , $part[1] );
    array_pop($keys);
print_r($keys);
    $was = file_get_contents ( 'yaxml/' . $domain ); 
    $fyaxml = fopen( 'yaxml/' . $domain ,"w"); 
    $log = fopen( 'log.html' ,"a"); $date = date('j.m.Y G:i'); fputs( $log, $date." Picking up keywords for domain: ".$domain."<br/>" ); fclose($log);

    foreach( $keys as $key )
    {
        $key = trim($key);
        if($yandex_region>1) $i = yapos( $domain , $key , $yandex_region ); else $i = yapos( $domain , $key );
        fputs( $fyaxml , $i . ':' ); 
        sleep($pause1);
        $log = fopen( 'log.html' ,"a"); $date = date('j.m.Y G:i'); fputs( $log, $date." Key: ".$key." completed<br/>" ); fclose($log);
    }
    $add = explode( ':=' , $was ); 
    fputs( $fyaxml , '=' . $add[0] ); 
    fclose( $fyaxml ); 
    $date = date('j.m.Y G:i'); $fdat = fopen( 'date/_' . $domain ,"w"); fputs( $fdat , $date ); fclose( $fdat );
    sleep($pause2);
}
/////////////////////  --------------

$log = fopen( 'log.html' ,"a"); $date = date('j.m.Y G:i'); fputs( $log, $date." >> Finished: parsing Yandex XML<br/>" ); fclose($log);
echo "Yandex XML parsing completed " . date('j.m.Y G:i');





/////////////////////  functions


function  yapos ( $host , $query_esc , $geo=1)  
{

$host = preg_replace("[^https://|www\.]", '', $host);

$exit = 0;

$page  = 0;
$found = 0;
$pages = 1;
$error = false;

while (!$exit && $page < $pages && $host)
{

    $doc = <<<DOC
<?xml version='1.0' encoding='utf-8'?>
<request>
    <query>$query_esc</query>
    <page>$page</page>
    <maxpassages>0</maxpassages>
    <groupings>
        <groupby attr='d' mode='deep' groups-on-page='100' docs-in-group='1' curcateg='-1'/>
    </groupings>
</request>
DOC;

    $context = stream_context_create(array(
        'http' => array(
            'method'=>"POST",
            'header'=>"Content-type: application/xml\r\n" .
                      "Content-length: " . strlen($doc),
            'content'=>$doc
        )
    ));
    $response = file_get_contents('https://yandex.ru/search/xml?user={Write Username Here}&key={Write Key Here}&lr='.$geo, true, $context);


    if ( $response ) {
        //print $response->getBody();
        
        $xmldoc = new SimpleXMLElement($response);

        $xmlresponce = $xmldoc->response;

        if ($xmlresponce->error) {
            print "Возникла следующая ошибка:" . $xmlresponce->error . "<br/>\n";
            $exit  = 1;
            $error = true;
            break;
        }
        

        $pos = 1;
        $nodes = $xmldoc->xpath('/yandexsearch/response/results/grouping/group/doc/url');
        foreach ($nodes as $node) {
            // если URL начинается с имени хоста, выходим из цикла
            if ( preg_match("/^https:\/\/(www\.)?$host/i", $node) ) {
               $found = $pos + $page * 20;
                $exit = 1;
                break;
            }
        
            $pos++;
        }
    
        $page++;
    } else {
        print "внутренняя ошибка сервера\n";
        $exit = 1;
    }

  }
 //   if (!$error) {   if ($found)  return $found;  else return 0;  } else { return -1; }



if (!$error) {
    // если что-то найдено, то выводим результат
    if ($found) {
        $found = colorate($found);
        print "<p>сайт «$host » находится на месте № $found по запросу «$query_esc »</p>";
 $log = fopen( 'log-results-'.$geo.'.html' ,"a"); $date = date('j.m.Y G:i'); fputs( $log, $date." Key ".$query_esc.": ".$found." <br/>" ); fclose($log);
    } elseif ($host) {
        print "<p>сайт « $host » находится далее, чем на ". $pages*100 ." месте в выдаче «Яндекса» по запросу « $query_esc »</p>\n";
    }
}
}

    // end function [yapos]

function colorate($int)
{
    $color = '#FF0000';  //красный
    if($int<=10)
        $color = '#008E00'; //зеленый
    if($int>10  && $int<=20)
        $color = '#FFE500'; //желтый
    return '<span style="color: ' . $color . '">' . $int . '</span>';
}


    



?>