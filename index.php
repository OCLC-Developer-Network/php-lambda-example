<?php
require_once('vendor/autoload.php');
use Aws\Kms\KmsClient;
use Aws\Exception\AwsException;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// echo "Hello Lambda";

// $client = new Aws\Kms\KmsClient(['region' => 'us-east-1', 'version' => 'latest']);


// $keyId = 'arn:aws:kms:us-east-1:624367162282:key/8fbdc8c4-b702-40c2-bef6-f47bd2614f98';

// try {
//     $result = $client->describeKey(['KeyId' => $keyId]);
//     var_dump($result);
// } catch (AwsException $e) {
//     // output error message if fails
//     echo $e->getMessage();
//     echo "\n";
// }

$cwClient = new CloudWatchLogsClient(['region' => 'us-east-1', 'version' => 'latest']);
// Log group name, will be created if none
$cwGroupName = '/aws/lambda/my-test-php-app-production-api';
// Log stream name, will be created if none
$cwStreamNameInstance = "php-logs";
$handler = new CloudWatch($cwClient, $cwGroupName, $cwStreamNameInstance, 10, 10000, [ 'application' => 'php-test-app' ]);
$logger = new Logger('my_logger');
$logger->pushHandler($handler);

$logger->warning('warning something bad');
$logger->info('Yes were writing stuff to CloudWatch');
$logger->debug('debug CW');
$logger->notice('API request');
$logger->error('whoops some login failure');


