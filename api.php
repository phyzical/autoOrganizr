<?php
$app->get('/plugins/autoorganizr/settings', function ($request, $response, $args) {
	$autoOrganizrPlugin = new autoOrganizrPlugin();
	if ($autoOrganizrPlugin->checkRoute($request)) {
		if ($autoOrganizrPlugin->qualifyRequest(1, true)) {
			$GLOBALS['api']['response']['data'] = $autoOrganizrPlugin->_autoOrganizrPluginGetSettings();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/autoorganizr/launch', function ($request, $response, $args) {
	$autoOrganizrPlugin = new autoOrganizrPlugin();
	if ($autoOrganizrPlugin->checkRoute($request)) {
		$autoOrganizrPlugin->_autoOrganizrPluginLaunch();
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/plugins/autoorganizr/synctabs', function ($request, $response, $args) {
	$autoOrganizrPlugin = new autoOrganizrPlugin();
	if ($autoOrganizrPlugin->checkRoute($request)) {
		$GLOBALS['api']['response']['data'] = $autoOrganizrPlugin->_autoOrganizrPluginSyncTabs();
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
