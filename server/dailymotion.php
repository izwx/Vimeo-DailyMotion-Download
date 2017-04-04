<?php
// Daily Motion 動画検索
// 2016/7/23 Shin Izawa
//
// Usage：php dailymotion.php [uuencodeされた検索語]

echo "Daily Motion 動画検索: ".date('r').PHP_EOL;

require_once 'aws/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$aws = Aws\Common\Aws::factory('../config/config.php');
// Get the client from the builder by namespace
$s3 = $aws->get('S3');

// 検索語
$searchword = urldecode ($argv[1]);
echo "検索語: ".$searchword.PHP_EOL;



$url = "https://api.dailymotion.com/videos?fields=created_time,title,url,&search=".$word."&sort=relevance&limit=50";
$json = file_get_contents($url);
$searchResponse = json_decode($json,true);
	
	$seq = 0;
	$itemlist = array();
	
	//echo "Responces: ".var_dump($searchResponse).PHP_EOL;
		
	// 検索結果からJSONを作っていく
	foreach ($searchResponse['body']['data'] as $searchResult) {
		
		$idarray = explode( '/',$searchResult['uri'] );
		$id = $idarray[2];
		$imageurl = $searchResult['pictures']['sizes'][count($searchResult['pictures']['sizes'])-1]['link'];
				
		$itemlist[] = array(
			"videoid" => $id,
			"title" => $searchResult['name'],
			"image-url" => $imageurl,
			"source-url" => $searchResult['link'],
			"source-date" => $searchResult['created_time']
		);
		echo "[".$seq."] ".$searchResult['name'].PHP_EOL;
		$seq++;
	}
	$videolist = array("videos" => $itemlist); 
	
	$bucket = "stargrabber";
	$key = $searchword."/DailyMotion.json";
	
	echo "ファイル名: ".$key.PHP_EOL;
	
	if($s3->doesObjectExist($bucket,$key)) {
		$s3->deleteObject(array("Bucket"    => $bucket,"Key"    => $key));
	}
	$rParams                = array(
		'Bucket'		=> $bucket,
		'Key' 			=> $key,
		'Body'   => json_encode( $videolist ) ,
		'ACL'        => 'public-read',
		'ContentType'   => 'application/json; charset=utf-8'
	);
	//echo "S3 Params: ".var_dump($rParams).PHP_EOL;
	
	$result = $s3->putObject($rParams);
	$url    = $result['ObjectURL'];
	echo $seq."件を".$url."に追加しました。".date('r').PHP_EOL;

?>
