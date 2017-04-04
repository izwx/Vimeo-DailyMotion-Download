<?php
// Vimeo 動画検索
// 2015/8/21 Shin Izawa
// 2016/7/16 S3対応版
//
// Usage：php vimeo.php [uuencodeされた検索語]

echo "Vimeo 動画検索: ".date('r').PHP_EOL;

require_once 'aws/vendor/autoload.php';
require_once 'Vimeo/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

use Vimeo\Vimeo;

$aws = Aws\Common\Aws::factory('../config/config.php');
// Get the client from the builder by namespace
$s3 = $aws->get('S3');

// 検索語
$searchword = urldecode ($argv[1]);
echo "検索語: ".$searchword.PHP_EOL;

// Vimeo用　認証キー
$CLIENT_ID = 'xxxxx';
$CLIENT_SECRET = 'yyyy';
$ACCESS_TOKEN = 'zzzz';

// Vimeo SDKを初期化
$vimeo = new Vimeo($CLIENT_ID,$CLIENT_SECRET,$ACCESS_TOKEN);

	// 与えられた検索語で検索
	$query = array(
		'page' => 1,
		'per_page' => 50,
		'query' => $searchword,
		'sort' => 'relevant',
		'direction' => 'asc'
	);
	
	//echo "Query: ".var_dump($query).PHP_EOL;
	
	$searchResponse = $vimeo->request('/videos', $query, $method = 'GET');

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
	$key = $searchword."/Vimeo.json";
	
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
