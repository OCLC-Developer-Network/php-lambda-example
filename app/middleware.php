<?php
$auth_mw = function ($request, $response, $next) {
	try {
		$_SESSION['accessToken'] = $this->get("wskey")->getAccessTokenWithClientCredentials($this->get("config")['prod']['institution'], $this->get("config")['prod']['institution'], $this->get("user"));
		$response = $next($request, $response);
		return $response;
	}catch (Exception $e){
		$this->logger->addInfo('Failed to get Access Token' . $e->getMessage());
		return $this->view->render($response, 'error.html', [
				'error' => $e->getMessage()
		]);
	}
};