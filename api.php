<?php
$app->get('/plugins/autoorganizr/settings', function ($request, $response, $args) {
	$autoOrganizrPlugin = new autoOrganizrPlugin();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/autoorganizr/launch', function ($request, $response, $args) {
	$sonarrThrottlingPlugin = new autoOrganizrPlugin();
	if ($sonarrThrottlingPlugin->checkRoute($request)) {
		$sonarrThrottlingPlugin->_autoOrganizrPluginLaunch();
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/autoorganizr/synctabs', function ($request, $response, $args) {
	$autoOrganizrPlugin = new autoOrganizrPlugin();
	$autoOrganizrPlugin->_autoOrganizrPluginSyncTabs();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
