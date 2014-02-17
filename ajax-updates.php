<?php

include 'lib/helpers.php';
include 'lib/updater.php';

$updater = new Updater();

if (!empty($_GET['check'])) {
	$updater->check_versions();
}

if (!empty($_GET['update'])) {
	$updater->check_versions(true);
}