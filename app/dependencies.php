<?php
use OCLC\Auth\WSKey;
use OCLC\User;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Aws\Kms\KmsClient;

// DIC configuration
$container = $app->getContainer();
// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

$container['config'] = function ($c) {
	$kms = new KmsClient(['region' => 'us-east-1', 'version' => 'latest']);
	$file_string = file_get_contents(__DIR__ . '/config/config_encrypted.txt');
	try {
		$result = $kms->decrypt([
				'CiphertextBlob' => $file_string]);
		return Yaml::parse($result['Plaintext']);
	} catch (AwsException $e) {
	    // output error message if fails
	    echo $e->getMessage();
	}

};

$container['logger'] = function($c) {
	$logger = new Logger('my_logger');
	$file_handler = new StreamHandler('php://stderr');
	$logger->pushHandler($file_handler);
	return $logger;
};

$container['wskey'] = function ($c) {
	if (isset($_SERVER['HTTPS'])):
	$redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . "/catch_auth_code";
	else:
	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . "/catch_auth_code";
	endif;
	
	$services = array('WorldCatMetadataAPI');
	$options = array('services' => $services, 'redirectUri' => $redirect_uri);
	return new WSKey($c->get("config")['prod']['wskey'], $c->get("config")['prod']['secret'], $options);
};

$container['user'] = function ($c) {
	return new User($c->get("config")['prod']['institution'], $c->get("config")['prod']['principalID'], $c->get("config")['prod']['principalIDNS']);
};

// Register twif views on container
$container['view'] = function ($container) {
	$view = new \Slim\Views\Twig('app/views', [
			'cache' => false
	]);
	
	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
	$view->getEnvironment()->addGlobal('session', $_SESSION);
	
	return $view;
};