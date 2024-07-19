<?php
// PLUGIN INFORMATION
$GLOBALS['plugins']['autoOrganizr'] = array( // Plugin Name
	'name' => 'Auto Organizr', // Plugin Name
	'author' => 'phyzical', // Who wrote the plugin
	'category' => 'Automation', // One to Two Word Description
	'link' => 'https://github.com/phyzical/autoOrganizr', // Link to plugin info
	'license' => 'personal', // License Type use , for multiple
	'idPrefix' => 'AUTOORGANIZR', // html element id prefix (All Uppercase)
	'configPrefix' => 'AUTOORGANIZR', // config file prefix for array items without the hypen (All Uppercase)
	'version' => '0.0.1', // SemVer of plugin
	'image' => 'data/plugins/autoOrganizr/logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'bind' => true, // use default bind to make settings page - true or false
	'api' => 'api/v2/plugins/autoorganizr/settings', // api route for settings page (All Lowercase)
	'homepage' => false // Is plugin for use on homepage? true or false
);

class autoOrganizrPlugin extends Organizr
{
	public function _autoOrganizrPluginGetSettings()
	{
		return array(
			'About' => array (
				$this->settingsOption('notice', '', ['title' => 'Information', 'body' => '
				<h3 lang="en">Plugin Information</h3>
				<p>TODO</p>
			']),
			),
			'Plugin Settings' => array(
				$this->settingsOption('blank'),
			),
		);
	}

	public function _autoOrganizrPluginLaunch()
	{
		$user = $this->getUserById($this->user['userID']);
		if ($user) {
			$this->setResponse(200, 'User approved for plugin');
			return true;
		}
		$this->setResponse(401, 'User not approved for plugin');
		return false;
	}
}
