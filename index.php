<?php
require_once('vendor/autoload.php');
use Aws\S3\S3Client;

lambda(function (array $event) {
	$client = new S3Client(['region' => 'us-east-1', 'version' => 'latest']);
	$client->registerStreamWrapper();
	$bucket = $event['Records'][0]['s3']['bucket']['name'];
	$key = $event['Records'][0]['s3']['object']['key'];
	$json = file_get_contents("s3://" . $bucket . "/" . $key);
	echo $json;
});