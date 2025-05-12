#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

$options = getopt("", [
	"night::",
	"day::",
	"start::",
	"stop::",
	"legend::",
	"fps::",
	"interval::",
	"place:",
	"quality::",
]);

if (!isset($options['place'])) {
	echo "--place option missing\n";
	die();
}

$place_id = $options['place'];

$place = new Place();
if (!$place->load(['id' => $place_id])) {
	echo "Place #$place_id does no exist.\n";
	die();
}

$night = (isset($options['night']) and $options['night'] == "on");
$day = (isset($options['day']) and $options['day'] == "on");
$legend = (isset($options['legend']) and $options['legend'] == "on");
$fps = isset($options['fps']) ? (int)$options['fps'] : 50;
$interval = isset($options['interval']) ? (int)$options['interval'] : 900;
$quality = isset($options['quality']) ? $options['quality'] : "hd";

$start = 0;
if (isset($options['start']) and $options['start']) {
	if (is_numeric($options['start'])) {
		$start = $options['start'];
	} else {
		$start = strtotime($options['start']);
	}
}

$stop = time();
if (isset($options['stop']) and $options['stop']) {
	if (is_numeric($options['stop'])) {
		$stop = $options['stop'];
	} else {
		$stop = strtotime($options['stop']);
	}
}

$video = new Video();
$video->place_id = $place->id;
$video->fps = $fps;
$video->start = $start;
$video->stop = $stop;
$video->quality = $quality;
$video->set_filename($place->id."-".$start."-".$stop);
$conditions = [
	'place_id' => $place->id,
	'timestamp BETWEEN '.(int)$video->start.' AND '.(int)$video->stop,
];
if (!$night) {
	$conditions[] = 'period != "night"';
}
if ($day) {
	$conditions[] = 'period = "day"';
}
$video->photos = Photo::select_monotonous($interval, $conditions, 'timestamp ASC');
if ($legend) {
	$video->make_with_legend();
} else {
	$video->make();
}
$video->insert();

