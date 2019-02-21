<?php
// Add routes

//display form
$app->get('/', function ($request, $response, $args) {
	return $this->view->render($response, 'search_form.html');
})->setName('display_search_form');

//display bib route
$app->get('/bib[/{oclcnumber}]', function ($request, $response, $args){
	if (isset($args['oclcnumber'])){
		$oclcnumber = $args['oclcnumber'];
		$_SESSION['route'] = $this->get('router')->pathFor($request->getAttribute('route')->getName(), ['oclcnumber' => $args['oclcnumber']]);
	} elseif ($request->getParam('oclcnumber')) {
		$oclcnumber = $request->getParam('oclcnumber');
		$_SESSION['route'] = $this->get('router')->pathFor($request->getAttribute('route')->getName()) ."?" . http_build_query($request->getQueryParams());
	} else {
		$this->logger->addInfo("No OCLC Number present");
		return $this->view->render($response, 'error.html', [
				'error' => 'No OCLC Number present',
				'error_message' => 'Sorry you did not pass in an OCLC Number'
		]);
	}
	$bib = Bib::find($oclcnumber, $_SESSION['accessToken']);
	
	if (is_a($bib, "Bib")){
		
		return $this->view->render($response, 'bib.html', [
				'bib' => $bib
		]);
	}else {
		$this->logger->addInfo("API Call failed " . $bib->getCode() . " " . $bib->getMessage());
		return $this->view->render($response, 'error.html', [
				'error' => $bib->getCode(),
				'error_message' => $bib->getMessage(),
				'error_detail' => $bib->getDetail(),
				'oclcnumber' => $args['oclcnumber']
		]);
	}
})->setName('display_bib')->add($auth_mw);