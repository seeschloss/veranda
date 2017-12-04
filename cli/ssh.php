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

		if (file_exists($GLOBALS['config']['latest-picture']['path'])) {
			unlink($GLOBALS['config']['latest-picture']['path']);
		}
		symlink(realpath($photo->path()), $GLOBALS['config']['latest-picture']['path']);
		break;

	case "video":
		if ($args[2] == "total") {
			$video = new Video();
			$video->place_id = 1;
			$video->fps = 50;
			$video->start = 0;
			$video->stop = time();
			$video->set_filename("boite-total");
			$video->photos = array_filter(
				array_values(Photo::select(['place_id' => 1, 'period != "nuit" OR path_balanced != ""'], 'timestamp ASC')),
				function($key) {
					return $key % 10 == 0;
				},
				ARRAY_FILTER_USE_KEY
			);
			//$video->blur = true;
			$video->make();
			$video->insert();
			
			if (file_exists($GLOBALS['config']['latest-complete-video']['path'])) {
				unlink($GLOBALS['config']['latest-complete-video']['path']);
			}
			symlink($video->path, $GLOBALS['config']['latest-complete-video']['path']);
		} else if ($args[2] == "total-legend") {
			$video = new Video();
			$video->place_id = 1;
			$video->fps = 50;
			$video->start = 0;
			$video->stop = time();
			$video->set_filename("boite-legend");
			$video->photos = array_filter(
				array_values(Photo::select(['place_id' => 1, 'period != "nuit" OR path_balanced != ""'], 'timestamp ASC')),
				function($key) {
					return $key % 10 == 0;
				},
				ARRAY_FILTER_USE_KEY
			);
			$video->make_with_legend();
			$video->insert();
			
			if (file_exists($GLOBALS['config']['latest-complete-video']['path'])) {
				unlink($GLOBALS['config']['latest-complete-video']['path']);
			}
			symlink($video->path, $GLOBALS['config']['latest-complete-video']['path']);
		} else {
			$timestamp = $args[2];
			$video = new Video();
			$video->place_id = 1;
			$video->fps = 10;
			$video->start = strtotime("today midnight UTC", $timestamp);
			$video->stop = $timestamp;
			$video->photos = Photo::select([
				'place_id' => 1,
				'period != "nuit" OR path_balanced != ""',
				'timestamp BETWEEN '.(int)$video->start.' AND '.(int)$video->stop,
			], 'timestamp ASC');
			$video->make();
			$video->insert();

			if (file_exists($GLOBALS['config']['latest-video']['path'])) {
				unlink($GLOBALS['config']['latest-video']['path']);
			}
			symlink($video->path, $GLOBALS['config']['latest-video']['path']);

			$timestamp = $args[2];
			$video = new Video();
			$video->place_id = 1;
			$video->fps = 10;
			$video->quality = "hd";
			$video->start = strtotime("today midnight UTC", $timestamp);
			$video->stop = $timestamp;
			$video->photos = Photo::select([
				'place_id' => 1,
				'period != "nuit" OR path_balanced != ""',
				'timestamp BETWEEN '.(int)$video->start.' AND '.(int)$video->stop,
			], 'timestamp ASC');
			$video->make();
			$video->insert();

			if (file_exists($GLOBALS['config']['latest-hd-video']['path'])) {
				unlink($GLOBALS['config']['latest-hd-video']['path']);
			}
			symlink($video->path, $GLOBALS['config']['latest-hd-video']['path']);
		}
		break;
}

