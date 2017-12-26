#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

if (getenv('SSH_ORIGINAL_COMMAND')) {
	$args = explode(" ", getenv('SSH_ORIGINAL_COMMAND'));
	// SSH_ORIGINAL_COMMAND does not preserve quoting, so beware

	array_unshift($args, $argv[0]);
} else {
	$args = $argv;
}

//var_dump($args);

switch ($args[1]) {
	case "temperature":
		$site = $args[2];

		if (!isset($GLOBALS['config']['local-temperatures']['archives'][$site])) {
			echo "Temperature data coming from unknown site `{$site}'.\n";
			die(1);
		}

		$data = file_get_contents('php://stdin');
		file_put_contents($GLOBALS['config']['local-temperatures']['archives'][$site], $data);

		$archive = new Temperature_Archive();
		$archive->files = $GLOBALS['config']['local-temperatures']['archives'];
		$archive->merge();
		file_put_contents($GLOBALS['config']['local-temperatures']['path'], $archive->tsv());
		break;

	case "photo":
		$timestamp = $args[2];
		$period = $args[3];

		$data = file_get_contents('php://stdin');
		$photo = new Photo();
		$photo->place_id = 1;
		$photo->timestamp = $timestamp;
		$photo->period = $period;
		$photo->save($data);
		break;
}

