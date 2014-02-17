<?php

class Updater
{
	private $versions;

	public function __construct()
	{
		if (!file_exists('cache/')) {
			mkdir('cache/');
		}
		touch('cache/versions.json'); // create versions.json if missing
		$this->versions = $this->get_versions();
	}

	private function get_versions()
	{
		return json_decode(file_get_contents('cache/versions.json'));
	}

	private function save_versions()
	{
		return file_put_contents('cache/versions.json', json_encode($this->versions));
	}

	public function latest_wp_version()
	{
		return json_decode(file_get_contents('http://api.wordpress.org/core/version-check/1.7/'))->offers[0]->version;
	}

	public function latest_plugin_version($plugin)
	{
		return unserialize(file_get_contents('http://api.wordpress.org/plugins/info/1.0/'.$plugin))->version;
	}

	public function latest_patch_roots_version()
	{
		return json_decode(file_get_contents('http://rootspatch:patchroots@patchworkmultimedia.com/web-assets/wordpress/patch-roots-version.json'))->version;
	}

	public function check_versions($update=null)
	{
		$updates = "";

		if (empty($this->versions->wordpress) || $this->versions->wordpress !== $this->latest_wp_version() || !file_exists('cache/wordpress')) {
			if ($update) {
				$this->update_wordpress();
			}
			$updates .= 'WordPress<br />';
		}

		if (empty($this->versions->patch_roots) || $this->versions->patch_roots !== $this->latest_patch_roots_version() || !file_exists('cache/patch-roots')) {
			if ($update) {
				$this->update_patch_roots();
			}
			$updates .=  'Patch Roots<br />';
		}

		if (!empty($this->versions->plugins)) {
			$plugin_count = 0;
			foreach (get_config('plugins') as $plugin) {
				$current_version = $this->versions->plugins->{$plugin['slug']};
				if (empty($current_version) || strcmp($current_version, $this->latest_plugin_version($plugin['slug'])) < 0 || !file_exists('cache/plugins/'.$plugin['slug'])) {
					if ($update) {
						$this->update_plugin($plugin['slug']);
					}
					$plugin_count++;
				}
			}
			$updates .= ($plugin_count > 0) ? $plugin_count.' Plugins<br />' : '';
		} else {
			$plugin_count = 0;
			foreach (get_config('plugins') as $plugin) {
				if ($update) {
					$this->update_plugin($plugin['slug']);
				}
				$plugin_count++;
			}
			$updates .= ($plugin_count > 0) ? $plugin_count.' Plugins<br />' : '';
		}

		echo (!empty($updates)) ? '<strong>Updating...</strong><br />'.$updates : '';

		$this->save_versions();
	}

	public function update_plugin($plugin)
	{
		if (file_exists('cache/plugins/'.$plugin)) {
			delete_folder('cache/plugins/'.$plugin);
		}
		if (extract_zip(array('http://downloads.wordpress.org/plugin/'.$plugin.'.'.$this->latest_plugin_version($plugin).'.zip', 'cache/plugins/'), false, $plugin)) {
			$this->versions->plugins->{$plugin} = $this->latest_plugin_version($plugin);
		} else {
			extract_zip(array('http://downloads.wordpress.org/plugin/'.$plugin.'.zip', 'cache/plugins/'), false, $plugin);
			$this->versions->plugins->{$plugin} = $this->latest_plugin_version($plugin);
		}
	}

	public function update_wordpress()
	{
		if (file_exists('cache/wordpress/')) {
			delete_folder('cache/wordpress/');
		}
		extract_zip(array('http://wordpress.org/latest.zip', 'cache/'), false, 'wordpress');
		$this->versions->wordpress = $this->latest_wp_version();
	}

	public function update_patch_roots()
	{
		if (file_exists('cache/patch-roots/')) {
			delete_folder('cache/patch-roots/');
		}
		extract_zip(array('http://rootspatch:patchroots@patchworkmultimedia.com/web-assets/wordpress/patch-roots.zip', 'cache/'), false, 'patch-roots');
		$this->versions->patch_roots = $this->latest_patch_roots_version();
	}
}