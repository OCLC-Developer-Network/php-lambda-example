<?php
// Add routes

//display form
$app->get('/', function ($request, $response, $args) {
	return $this->view->render($response, 'index.html');
})->setName('display_index');
