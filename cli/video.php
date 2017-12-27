#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

switch ($argv[1]) {
	case "total":
		if (isset($argv[2])) {
			$place_id = $argv[2];
		} else {
			$place_id = 1;
		}

		$place = new Place();
		if ($place->load(['id' => $place_id])) {
			$video = new Video();
			$video->place_id = $place->id;
			$video->fps = 50;
			$video->start = 0;
			$video->stop = time();
			$video->set_filename($place->id."-total-".time());
			$video->photos = Photo::select_monotonous(1800, [
					'place_id' => $place->id,
					'period != "night" OR path_balanced != ""',
				], 'timestamp ASC');
			$video->make();
			$video->insert();
		}
		break;
	case "total-legend":
		if (isset($argv[2])) {
			$place_id = $argv[2];
		} else {
			$place_id = 1;
		}

		$place = new Place();
		if ($place->load(['id' => $place_id])) {
			$video = new Video();
			$video->place_id = $place->id;
			$video->fps = 50;
			$video->start = 0;
			$video->stop = time();
			$video->set_filename($place->id."-total-legend-".time());
			$video->photos = Photo::select_monotonous(1800, [
					'place_id' => $place->id,
					'period != "night"',
					//'period != "night" OR path_balanced != ""',
				], 'timestamp ASC');
			$video->make_with_legend();
			$video->insert();
		}
		break;
	case "plant":
		$plant_id = $argv[2];
		$plant = new Plant();
		$plant->load(['id' => $plant_id]);
		$plant->make_video();
		break;
	default:
		$timestamp = time();
		$video = new Video();
		$video->place_id = 1;
		$video->fps = 10;
		$video->start = strtotime("today midnight UTC", $timestamp);
		$video->stop = $timestamp;
		$video->photos = Photo::select([
			'place_id' => 1,
			'period != "night" OR path_balanced != ""',
			'timestamp BETWEEN '.(int)$video->start.' AND '.(int)$video->stop,
		], 'timestamp ASC');
		$video->make();
		$video->insert();

		$video = new Video();
		$video->place_id = 1;
		$video->fps = 10;
		$video->quality = "hd";
		$video->start = strtotime("today midnight UTC", $timestamp);
		$video->stop = $timestamp;
		$video->photos = Photo::select([
			'place_id' => 1,
			'period != "night" OR path_balanced != ""',
			'timestamp BETWEEN '.(int)$video->start.' AND '.(int)$video->stop,
		], 'timestamp ASC');
		$video->make();
		$video->insert();
		break;
}


