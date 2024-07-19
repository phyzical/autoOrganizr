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
	public function _autoOrganizrPluginSyncTabs()
	{
		// TODO: how can we know we are referencing a tab based on container? i.e removal/name change
		$containers = $this->getDockerContainers();
		print_r($containers);

		$tabs = $this->mapContainersToTabs($containers);

		print_r($tabs);

		$containers = [
			[
			"name" => "test", 
			"url" => "url",
			"group_id" => "group_id",
			'category_id' => 'category_id',
			'enabled' => 'enabled',
			'default' => 'default',
			'type' => 'type',
			'order' => 'order',
			'image' => 'image'
			]
			];

		$existingTabs = $this->getAllTabs()["tabs"];
		foreach ($containers as $container) {
			$foundTabs = array_filter($existingTabs, function($obj) use ($container) {
				return $obj["name"] == $container["name"];
			});
			$foundTab = reset($foundTabs);

			if ($foundTab) {
				$this->updateTab($foundTab["id"], $container);
				continue;
			} 
			$this->addTab($container);
		}
	}

	const LABEL_PREFIX = "organizr.tab";
	private function getDockerContainers() {
		// TODO: make host+port configurable
		$response = Requests::get("http://docker:2375/containers/json");
		if ($response->success) {
			$containers = array_map(function($container) {
				// TODO: grab the first external port, use a new env of default domain
				//	TODO: only do this if url isnt provided
				// TODO: net.unraid.docker.icon use this for image if exist and image wasnt provided
				$labels = array_filter($container["Labels"], function( $val, $key) {
					return str_contains($key, "organizr");
				}, ARRAY_FILTER_USE_BOTH);
				return [
					"name" => ltrim(reset($container["Names"]), "/"),
					"labels" => $labels,
				];
			}, json_decode($response->body,true));
			return array_filter($containers, function($container) {
				return array_key_exists(self::LABEL_PREFIX.".enabled", $container["labels"]);
			});
		} else {
			$this->logger->warning('Unable to query docker',$response);
			$this->setResponse(409, 'Docker socket error');
			return false; 
		}
	}

	private function mapContainersToTabs($containers) {
		return array_map(function($container) {
			return array_filter([
				"name" => $container["name"], 
				"url" => $container["self::LABEL_PREFIX.url"],
				"group_id" => $container["self::LABEL_PREFIX.group_id"],
				'category_id' => $container["self::LABEL_PREFIX.category_id"],
				'enabled' => $container[self::LABEL_PREFIX.".enabled"],
				'default' => $container["self::LABEL_PREFIX.default"],
				'type' => $container["self::LABEL_PREFIX.type"],
				'order' => $container["self::LABEL_PREFIX.order"],
				'image' => $container["self::LABEL_PREFIX.image"]
			]);
		}, $containers);
	}

	public function _autoOrganizrPluginGetSettings()
	{
		return array(
			'About' => array (
				$this->settingsOption('notice', '', [
				'title' => 'Information', 
				'body' => '
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
