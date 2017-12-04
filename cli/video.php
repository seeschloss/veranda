#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

switch ($argv[1]) {
	case "total":
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
	case "total-legend":
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
	default:
		$timestamp = time();
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
		break;
}


