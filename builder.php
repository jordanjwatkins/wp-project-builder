<?php

	include 'lib/helpers.php';
	include 'lib/updater.php';
	include 'lib/wp-project.php';

	if (empty($_SESSION['updated']) || !empty($_GET['update']) || !cache_primed()) {
		$_SESSION['updated'] = true;
		echo '<input type="hidden" id="update-flag" value="update" />';
	}

	if ($_POST) {
		$errors = array();
		extract($_POST);

		if (empty($name)) {
			$errors['name'] = "Project name is required!";
		}

		if (empty($shortname)) {
			$errors['shortname'] = "Project short name is required!";
		}

		if (empty($path)) {
			$path = get_config('default_project_root');
		}

		if (empty($domain)) {
			$domain = "site.com";
		}

		if (empty($patch_roots)) {
			$patch_roots = false;
		}

		if (empty($plugins)) {
			$plugins = null;
		} else {
			$plugins = (!empty($selected_plugins)) ? $selected_plugins : null;
		}

		if (empty($wordpress)) {
			$wordpress = false;
		}

		if (empty($errors)) {
			$project = new WP_Project($name, $shortname, $path, $domain, $wordpress, $patch_roots, $plugins);
			$errors = $project->validate();
		}

		if (empty($errors)) {
			$project->build();
		}
	}

	if (!empty($errors) || !$_POST) {
?>
		<form id="builder-form" action="" method="POST">
			<div class="inner">
				<div id="loading"></div>
				<div id="updates"></div>
				<h1>WP Project Builder</h1>
				<?php
					if (!empty($errors)) {
						echo '<div class="errors">';
						foreach ($errors as $error) {
							echo $error."<br />";
						}
						echo '</div>';
					}
				?>
				<div class="form-row">
					<label for="name" class="required" >Project Name</label>
					<input type="text" id="name" name="name" placeholder="Test Project" value="<?php post_value('name'); ?>" data-validation-engine="validate[required]" />
				</div>
				<div class="form-row">
					<label for="shortname" class="required">Project Slug</label>
					<input type="text" id="shortname" name="shortname"  placeholder="testproject" value="<?php post_value('shortname'); ?>" data-validation-engine="validate[required]" />
				</div>
				<div class="form-row">
					<label for="path">Path to Projects Root (relative to this page)</label>
					<input type="text" name="path" placeholder="<?php echo get_config('default_project_root'); ?>" value="<?php post_value('path', get_config('default_project_root')); ?>" />
				</div>
				<div class="form-row">
					<label for="domain">Domain</label>
					<input type="text" name="domain" placeholder="site.com" value="<?php post_value('domain'); ?>" />
				</div>
				<div class="form-row row">
					<input type="checkbox" id="wordpress" name="wordpress" value="true" <?php echo post_checked('wordpress'); ?> />
					<label for="wordpress">WordPress</label>
				</div>
				<div class="form-row row">
					<input type="checkbox" id="patch-roots" name="patch_roots" value="true" <?php echo post_checked('patch_roots'); ?> />
					<label for="patch-roots">Patch Roots</label>
				</div>
				<div class="form-row row">
					<input type="checkbox" id="plugins" name="plugins" value="true" <?php echo post_checked('plugins'); ?> />
					<label for="plugins">Plugins </label> <span id="toggle-plugins" class="show"></span>
					<ul class="plugin-selection">
						<?php
							foreach (get_config('plugins') as $key => $plugin) {
								$default = ($key < get_config('default_plugins')) ? true : false;
								echo '<li><input type="checkbox" id="plugin-'.$plugin['slug'].'" name="selected_plugins[]" value="'.$plugin['slug'].'" '.post_checked_multi($plugin['slug'], 'selected_plugins', $default).'  /><label><a href="http://wordpress.org/plugins/'.$plugin['slug'].'" target="_blank">'.$plugin['name']."</a></label></li>";
							}
						?>
					</ul>
				</div>
				<div class="form-row row">
					<input type="submit" value="Build" />
				</div>
			</div>
		</form>
<?php } ?>