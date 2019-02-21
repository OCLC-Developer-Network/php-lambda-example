<?php
require_once('vendor/autoload.php');
session_start();

$config = require __DIR__ . '/app/settings.php';

$app = new \Slim\App($config);

// Set up dependencies
require __DIR__ . '/app/dependencies.php';
// Register middleware
require __DIR__ . '/app/middleware.php';
// Register routes
require __DIR__ . '/app/routes.php';

// Get container
$container = $app->getContainer();
	
// Run application
$app->run();

