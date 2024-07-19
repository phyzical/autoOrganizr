<?php
$app->get('/plugins/autoOrganizr/settings', function ($request, $response, $args) {
	autoOrganizrPlugin = new autoOrganizrPlugin();
	if (autoOrganizrPlugin->checkRoute($request)) {
		if (autoOrganizrPlugin->qualifyRequest(1, true)) {
			$GLOBALS['api']['response']['data'] = autoOrganizrPlugin->_autoOrganizrPluginGetSettings();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
