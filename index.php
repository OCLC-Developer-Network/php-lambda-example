<?php
require_once('vendor/autoload.php');
use Aws\Kms\KmsClient;
use Aws\Exception\AwsException;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Aws\S3\S3Client;


$client = new Aws\Kms\KmsClient(['region' => 'us-east-1', 'version' => 'latest']);

// try {
//     $result = $client->decrypt(file_get_contents("test_encrypt.txt"));
//     echo $result['Plaintext'];
// } catch (AwsException $e) {
//     // output error message if fails
//     echo $e->getMessage();
//     echo "\n";
// }

$cwClient = new CloudWatchLogsClient(['region' => 'us-east-1', 'version' => 'latest']);
// Log group name, will be created if none
$cwGroupName = '/aws/lambda/my-test-php-app';
// Log stream name, will be created if none
$cwStreamNameInstance = "php-logs";
$cloudhandler = new CloudWatch($cwClient, $cwGroupName, $cwStreamNameInstance, 10, 10000, [ 'application' => 'php-test-app' ]);

$client = new S3Client(['region' => 'us-east-1', 'version' => 'latest']);
$client->registerStreamWrapper();
$handler = new StreamHandler("s3://php-app-logs/logs/app.log");

$logger = new Logger('my_logger');
$logger->pushHandler($handler);
$logger->pushHandler($cloudhandler);

$logger->warning('warning something bad');
$logger->info('Yes were writing stuff to CloudWatch');
$logger->debug('debug CW');
$logger->notice('API request');
$logger->error('whoops some login failure');

echo "Logging stuff I hope";


