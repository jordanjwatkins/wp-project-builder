<?php

class WP_Project
{
	private $project_name;
	private $project_slug;
	private $project_domain;
	private $db_connection;
	private $wordpress;
	private $patch_roots;
	private $plugins;
	private $project_path;
	private $site_path;
	private $theme_path;
	private $plugin_path;

	public function __construct($project_name, $project_slug, $project_path_prefix, $project_domain, $wordpress, $patch_roots, $plugins)
	{
		// set properties
		$this->project_name = $project_name;
		$this->project_slug = $project_slug;
		$this->project_domain = $project_domain;
		$this->wordpress = $wordpress;
		$this->patch_roots = $patch_roots;
		$this->plugins = $plugins;

		// setup paths
		$this->project_path = $project_path_prefix.$project_slug;
		$this->site_path = $this->project_path.'/'.$project_slug.'/';
		$this->theme_path = $this->project_path.'/'.$project_slug.'/wp-content/themes/'.$project_slug.'/';
		$this->plugin_path = $this->project_path.'/'.$project_slug.'/wp-content/plugins/';

		// connect to database
		$this->db_connect();
	}

	public function validate()
	{
		$errors = array();

		// check if target database already exists
		$db_selected = mysqli_select_db($this->db_connection, $this->project_slug);
		if ($db_selected) { $errors[] = 'Database <a target="_blank" href="http://localhost/phpmyadmin/">"'.$this->project_slug.'"</a> already exists.'; }

		// check if target project folder already exists
		if (file_exists($this->project_path)) { $errors[] = '<a target="_blank" href="'.$this->site_path.'">"'.$this->project_name.'"</a> already exists.'; }

		return $errors;
	}

	public function build()
	{
		// create database
		$this->create_db();

		// add project if it doesn't already exist
		if (file_exists($this->project_path)) { throw new Exception('<a href="'.$this->site_path.'">"'.$this->project_name.'"</a> already exists.'); }

		// create project and site folders
		mkdir($this->project_path);
		mkdir($this->site_path);

		// create Assets folders
		mkdir($this->project_path.'/Assets/');
		mkdir($this->project_path.'/Assets/html');
		mkdir($this->project_path.'/Assets/docs');
		mkdir($this->project_path.'/Assets/comps');
		mkdir($this->project_path.'/Assets/images');

		// add WP files, Patch Roots and plugins
		$this->get_project_files();

		if ($this->wordpress) {
			// if Patch Roots is installed, brand it to fit project
			if ($this->patch_roots) { $this->brand_patch_roots(); }

			// create wp-config file
			$this->create_wp_config();

			// install WP, modify WP settings, activate theme
			$this->install_wp();
		}

		// display success message
		echo '<a href="'.$this->site_path.'">"'.$this->project_name.'"</a> was built successfully.<br />';
	}

	private function get_project_files()
	{
		if ($this->wordpress) {
			// move WP files into site root
			move_folder_contents('cache/wordpress', $this->site_path, false);

			// remove unwanted plugins
			delete_folder($this->site_path.'wp-content/plugins/akismet');
			unlink($this->site_path.'wp-content/plugins/hello.php');

			if ($this->patch_roots) {
				move_folder_contents('cache/patch-roots', $this->theme_path, false);
			}

			if ($this->plugins) {
				foreach ($this->plugins as $plugin) {
					// move WP files into site root
					move_folder_contents('cache/plugins/'.$plugin, $this->plugin_path.$plugin, false);
				}
			}

			// update sitemap path and add robots.txt
			copy('assets/robots.txt', $this->site_path.'robots.txt');
			$search_pairs = array("Sitemap: http://example.com/sitemap.xml" => "Sitemap: http://".$this->project_domain."/sitemap.xml");
			file_search_replace($this->site_path.'robots.txt', $search_pairs);

			// add default favicon
			copy('favicon.ico', $this->site_path.'favicon.ico');
		}
	}

	private function install_wp()
	{
		// setup non-relative site path
		$site_path = str_replace('..', 'http://localhost', $this->site_path);

		// prepare for WP install
		define('WP_SITEURL', $site_path);
		define('WP_INSTALLING', true);
		global $wp_db_version; // must be declared BEFORE WP is included
		require_once $this->site_path.'wp-load.php';
		require_once $this->site_path.'wp-admin/includes/upgrade.php';
		require_once $this->site_path.'wp-includes/wp-db.php';
		require_once $this->site_path.'wp-includes/version.php';
		if ($this->patch_roots) {
			require_once $this->theme_path.'lib/activation.php';
		}

		// install WP
		wp_install($this->project_name, 'admin', 'info@'.$this->project_domain, true, null, ucfirst($this->project_slug).get_config('wp_pass_suffix'));

		// set WP options
		update_option('siteurl', $site_path);
		update_option('home', $site_path);
		update_option('blogdescription', '');
		update_option('posts_per_page', '3');
		update_option('db_version', $wp_db_version);
		update_option('admin_email','info@'.$this->project_domain);

		// set admin user options
		update_user_meta(1, 'show_admin_bar_front', 0);
		update_user_meta(1, 'show_welcome_panel', 0);
	}

	private function create_wp_config()
	{
		// create a wp-config file in site root
		copy('assets/wp-config-start.php', $this->site_path.'wp-config.php');

		// read wp-config and salts into arrays
		$wp_config = file($this->site_path.'wp-config.php');
		$salts = file("https://api.wordpress.org/secret-key/1.1/salt/");

		// define values for DB_NAME, DB_PASSWORD and all salts
		$wp_config[18] = "define('DB_NAME', '".$this->project_slug."');\r\n";
		$wp_config[21] = "define('DB_USER', '".get_config('db_user')."');\r\n";
		$wp_config[24] = "define('DB_PASSWORD', '".get_config('db_pass')."');\r\n";

		for($i=44, $j=0; $i<52; $i++, $j++) {
			$wp_config[$i] = $salts[$j];
		}

		// update wp-config file
		file_put_contents($this->site_path.'wp-config.php', $wp_config);
	}

	private function brand_patch_roots()
	{
		// update style.css to match project
		$style = file($this->theme_path.'style.css');
		$style[1] = "\tTheme Name:\t\t".$this->project_name."\r\n";
		$style[2] = "\tDescription:\tCustom theme for ".$this->project_name."\r\n";
		$style[4] = "\tAuthor:\t\t\t".get_config('theme_author')."\r\n";
		file_put_contents($this->theme_path.'style.css', $style);

		// update scripts.php to point to css filename based on project slug
		$search_pairs = array('site.css' => $this->project_slug.'.css');
		file_search_replace($this->theme_path.'lib/scripts.php', $search_pairs);

		// rename site.css to filename based on the project slug
		rename($this->theme_path.'assets/css/site.css', $this->theme_path.'assets/css/'.$this->project_slug.'.css');
	}

	private function db_connect()
	{
		// attempt connection
		@$this->db_connection = mysqli_connect("localhost", get_config('db_user'), get_config('db_pass'));

		// throw exception if connection failed
		if (mysqli_connect_errno()) { throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error()); }
	}

	private function create_db()
	{
		// create database and throw exception if creation fails
		$sql = "CREATE DATABASE ".$this->project_slug;
		if (!mysqli_query($this->db_connection,$sql)) {
			throw new Exception('<a href="'.$this->site_path.'">'.$this->project_name.'</a> database already exists or something else went wrong (db name has illegal characters?).');
		}
	}
}