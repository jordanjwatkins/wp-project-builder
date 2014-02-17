<?php

function console_debug($data)
{
    $data = json_encode($data);
    echo "<script>console.dir($data)</script>";
}

function get_config($name)
{
	include 'config.php';
	return $config[$name];
}

function cache_primed()
{
	return (file_exists('cache/wordpress') && file_exists('cache/patch-roots') && file_exists('cache/plugins')) ? true : false;
}

function extract_zip($paths, $folder_extract = false, $name = '')
{
	if (@file_get_contents($paths[0])) {
		file_put_contents("temp.zip", file_get_contents($paths[0]));
		$zip = new ZipArchive;
		if ($zip->open('temp.zip') === TRUE) {
			$zip->extractTo($paths[1]);
			$zip->close();
		} else {
			throw new Exception('Extraction failed: '.$name);
		}
		unlink('temp.zip');
		if ($folder_extract) {
			move_folder_contents($paths[1].$name, $paths[1], true);
		}
	} else {
		return false;
	}
}

function move_folder_contents($src, $dst, $cut=true)
{
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				move_folder_contents($src . '/' . $file, $dst . '/' . $file, $cut);
			} else {
				copy($src . '/' . $file,$dst . '/' . $file);
				if ($cut) { unlink($src . '/' . $file); }
			}
		}
	}
	closedir($dir);
	if ($cut) { rmdir($src); }
}

function delete_folder($path)
{
	$dir = opendir($path);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($path . '/' . $file) ) {
				delete_folder($path . '/' . $file);
			} else {
				unlink($path . '/' . $file);
			}
		}
	}
	closedir($dir);
	rmdir($path);
}

function file_search_replace($file_path, $search_pairs)
{
	foreach ($search_pairs as $search => $replacement) {
		$file = array_map(function($line) use ($search, $replacement) { return str_replace($search, $replacement, $line); }, file($file_path));
	}
	file_put_contents($file_path, implode('', $file));
}

function exception_handler($exception) {
	echo $exception->getMessage();
}
set_exception_handler('exception_handler');

function post_value($name, $default=false)
{
	if ($_POST) {
		echo (!empty($_POST[$name])) ? htmlentities($_POST[$name], ENT_QUOTES, 'UTF-8') : '';
	} elseif ($default) {
		echo $default;
	}
}

function post_checked($name, $default=true)
{
	if ($_POST) {
		return (!empty($_POST[$name])) ? 'checked' : '';
	} elseif ($default === true) {
		return 'checked';
	}
}

function post_checked_multi($name, $group, $default=true)
{
	if (!empty($_POST[$group])) {
		return (in_array($name, $_POST[$group])) ? 'checked' : '';
	} elseif ($default === true) {
		return 'checked';
	}
}