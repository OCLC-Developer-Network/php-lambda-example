<?php
use OCLC\Auth\WSKey;
use OCLC\User;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Aws\Kms\KmsClient;
use Aws\S3\S3Client;

// DIC configuration
$container = $app->getContainer();
// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

$container['logger'] = function($c) {
	$logger = new Logger('my_logger');
	$file_handler = new StreamHandler('php://stderr');
	$logger->pushHandler($file_handler);
	return $logger;
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