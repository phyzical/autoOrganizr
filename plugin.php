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

$GLOBALS['cron'][] = [
	'class' => 'autoOrganizrPlugin', // Class name of plugin (case-sensitive)
	'enabled' => 'AUTOORGANIZR-cronRunEnabled', // Config item for job enable
	'schedule' => 'AUTOORGANIZR-cronRunSchedule', // Config item for job schedule
	'function' => '_autoOrganizrPluginSyncTabs', // Function to run during job
];

class autoOrganizrPlugin extends Organizr
{
	public function _autoOrganizrPluginSyncTabs()
	{
		$containers = $this->getDockerContainers();
		$tabs = $this->mapContainersToTabs($containers);
		$existingTabs = $this->getExistingTabs();
		$actions = array_merge(
			$this->removeTabs($tabs, $existingTabs),
			$this->addTabs($tabs, $existingTabs)
		);

		return $actions;
	}

	private function addTabs($tabs, $existingTabs)
	{
		$actions = [];
		foreach ($tabs as $tab) {
			$foundTabs = array_filter($existingTabs, function ($obj) use ($tab) {
				return $obj["name"] == $tab["name"];
			});
			$foundTab = reset($foundTabs);

			if ($foundTab) {
				$this->updateTab($foundTab["id"], $tab);
				array_push($actions, ["type" => "Updated", "name" => $tab["name"]]);
				continue;
			}
			array_push($actions, ["type" => "Added", "name" => $tab["name"]]);
			$this->addTab($tab);
		}
		return $actions;
	}

	private function removeTabs($tabs, $existingTabs)
	{
		$tabNames = array_map(function ($tab) {
			return $tab['name'];
		}, $tabs);

		$tabsForRemoval = array_filter($existingTabs, function ($tab) use ($tabNames) {
			return !in_array($tab["name"], $tabNames);
		});
		$actions = [];
		foreach ($tabsForRemoval as $tab) {
			$this->deleteTab($tab["id"]);
			array_push($actions, ["type" => "Removed", "name" => $tab["name"]]);
		}
		return $actions;
	}

	private function getExistingTabs()
	{
		return array_filter($this->getAllTabs()["tabs"], function ($tab) {
			return $tab["category_id"] == $this->findOrCreateAutoOrganizrCategoryID();
		});
	}

	const LABEL_PREFIX = "organizr.tab";
	const PLUGIN_PREFIX = "AUTOORGANIZR";
	const CATEGORY_NAME = "Auto Organizr";

	private function getDockerContainers()
	{
		$dockerProxyHost = $this->config[self::PLUGIN_PREFIX . '-dockerProxyHost'];
		$response = Requests::get("$dockerProxyHost/containers/json");
		if ($response->success) {
			$containers = array_map(function ($container) {
				$labels = array_filter($container["Labels"], function ($val, $key) {
					return str_contains($key, "organizr");
				}, ARRAY_FILTER_USE_BOTH);
				$port = reset($container["Ports"])["PublicPort"];
				$name = ltrim(reset($container["Names"]), "/");
				$domain = $this->config[self::PLUGIN_PREFIX . '-defaultDomain'];
				$url = null;
				if ($domain) {
					$url = "https://$name.$domain";
				}
				return [
					"name" => $name,
					"labels" => $labels,
					"url" => $url,
					"local_url" => "http://$name:$port",
					"image" => $container["Labels"]["net.unraid.docker.icon"]
				];
			}, json_decode($response->body, true));
			return array_filter($containers, function ($container) {
				return array_key_exists(self::LABEL_PREFIX . ".enabled", $container["labels"]);
			});
		} else {
			$this->logger->warning('Unable to query docker', $response);
			$this->setResponse(409, 'Docker socket error');
			return false;
		}
	}

	private function mapContainersToTabs($containers)
	{
		return array_map(function ($container) {
			$type = $container[self::LABEL_PREFIX . ".type"];
			if ($type) {
				switch ($type) {
					case "organizr":
						$type = 0;
						break;
					case "iframe":
						$type = 1;
						break;
					case "newWindow":
						$type = 2;
						break;
					default:
						$type = 1;
				}
			}

			return array_filter([
				"name" => $container[self::LABEL_PREFIX . ".name"] ?: $container["name"],
				"url" => $container[self::LABEL_PREFIX . ".url"] ?: $container["url"],
				"local_url" => $container[self::LABEL_PREFIX . ".local_url"] ?: $container["local_url"],
				"group_id" => $container[self::LABEL_PREFIX . ".group_id"],
				'category_id' => $this->findOrCreateAutoOrganizrCategoryID(),
				'enabled' => $container[self::LABEL_PREFIX . ".enabled"] || true,
				'default' => $container[self::LABEL_PREFIX . ".default"],
				'type' => $type,
				'order' => $container[self::LABEL_PREFIX . ".order"],
				'image' => $container[self::LABEL_PREFIX . ".image"] ?: $container["image"],
			]);
		}, $containers);
	}

	private function findOrCreateAutoOrganizrCategoryID()
	{
		$existingCategories = $this->getAllTabs()["categories"];
		$foundCategories = array_filter($existingCategories, function ($category) {
			return $category["category"] == self::CATEGORY_NAME;
		});
		$foundCategory = reset($foundCategories);
		if (!$foundCategory) {
			$foundCategory = $this->addCategory([
				"category" => self::CATEGORY_NAME,
				"image" => "data/plugins/autoOrganizr/logo.png"
			]);
		}
		return $foundCategory["category_id"];
	}


	public function _autoOrganizrPluginGetSettings()
	{
		return array(
			'About' => array(
				$this->settingsOption('notice', '', [
					'title' => 'Information',
					'body' => '
				<h3 lang="en">Plugin Information</h3>
				<link rel="stylesheet" type="text/css" href="data/plugins/autoOrganizr/autoorganizr.css">
				<p>This plugin communicates with the provided docker proxy</p>
				<p>It pulls all containers will auto create a tab for each enabled</p>
				<table class="autoOrganizr">
					<tr>
						<th>Label</th>
						<th>Description</th>
						<th>Default</th>
						<th>Required</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.enabled</th>
						<th>This is the bare minimum label to start creating a tab</th>
						<th><code class="elip hidden-xs">false</code></th>
						<th>true</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.image</th>
						<th>This is the Image to use for the tab</th>
						<th>
							Defaults to <code class="elip hidden-xs">net.unraid.docker.icon</code> label otherwise 
							<code class="elip hidden-xs">null</code> if not provided
						</th>
						<th>Only if <code class="elip hidden-xs">net.unraid.docker.icon</code> doesn\'t exist</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.url</th>
						<th>The url to use for the tab</th>
						<th>
							If Domain is set defaults to <code class="elip hidden-xs">https://{container_name}.{DOMAIN}</code>
							otherwise it is required
						</th>
						<th>Only if domain isn\'t set</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.name</th>
						<th>The name of the tab</th>
						<th><code class="elip hidden-xs">{container_name}</code></th>
						<th>false</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.local_url</th>
						<th>The local url to use for the tab</th>
						<th>Defaults to <code class="elip hidden-xs">http://{container_name}:{FIRST_LOCAL_PORT}</code></th>
						<th>false</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.group_id</th>
						<th>This is the id of the group to be added</th>
						<th><code class="elip hidden-xs">null</code></th>
						<th>false</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.default</th>
						<th>This is the default</th>
						<th><code class="elip hidden-xs">null</code></th>
						<th>false</th>
					</tr>
					<tr>
						<th>' . self::LABEL_PREFIX . '.type</th>
						<th>The type of tab can provide <code class="elip hidden-xs">organizr, iframe or newWindow</code></th>
						<th><code class="elip hidden-xs">iframe</code></th>
						<th>false</th>
					</tr>
						<tr>
						<th>' . self::LABEL_PREFIX . '.order</th>
						<th>This is the order of the tab</th>
						<th><code class="elip hidden-xs">null</code></th>
						<th>false</th>
					</tr>
				</table>
			'
				]),
			),
			'Plugin Settings' => array(
				$this->settingsOption('input', self::PLUGIN_PREFIX . '-defaultDomain', ['label' => 'Default domain to be used when auto guessing urls']),
				$this->settingsOption('input', self::PLUGIN_PREFIX . '-dockerProxyHost', ['label' => 'Docker proxy host to use to auto discover containers']),
				$this->settingsOption('boolean', self::PLUGIN_PREFIX . '-cronRunEnabled', ['label' => 'Enable the cron run']),
				$this->settingsOption('input', self::PLUGIN_PREFIX . '-cronRunSchedule', ['label' => 'Enter a cron schedule i.e `0 * * *` for once an hour ']),
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
	}
}
