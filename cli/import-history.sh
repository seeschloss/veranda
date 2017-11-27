#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

$files = [
	__DIR__.'/../data/temperature-veranda.tsv.bz2',
	__DIR__.'/../data/temperature-salon.tsv.bz2',
	__DIR__.'/../data/temperature-entree.tsv.bz2',
];

$data = [];
$headers = [
	'time',
];

$precision = 1; // en secondes

foreach ($files as $file) {
	$contents = "";

	/*
	if ($bz = fopen($file, "rb")) {
		stream_filter_append($bz, "bzip2.decompress", STREAM_FILTER_READ, array("concatenated" => true));
		while (!feof($bz)) {
		  $contents .= fread($bz, 4096);
		}
		fclose($bz);
	}
	*/

	$contents = `bzcat $file`;

	$lines = explode("\n", trim($contents));

	$header = explode(' ', trim(array_shift($lines)));
	$keys = array_flip($header);

	$headers = array_merge($headers, array_slice($header, 1));

	foreach ($lines as $line) {
		$line_fields = explode(' ', trim($line));

		$line_fields[$keys['time']] = floor($line_fields[$keys['time']] / $precision) * $precision;

		$line_data = [];
		foreach ($header as $index => $key) {
			if (isset($line_fields[$index])) {
				$line_data[$key] = $line_fields[$index];
			} else {
				$line_data[$key] = "-";
			}
		}

		if (!isset($data[$line_fields[$keys['time']]])) {
			$data[$line_fields[$keys['time']]] = [];
		}

		$data[$line_fields[$keys['time']]] += $line_data;
	}
}

ksort($data);

$sensors = Sensor::select();

$db = new DB();
$db->query("BEGIN TRANSACTION");

foreach ($data as $time => $line) {
	if ($time > 1511232105) {
		continue;
	}

	foreach ($headers as $index => $key) {
		if (isset($line[$key])) {
			$value = (float)$line[$key];

			if ($value == 0) {
				continue;
			}

			switch ($key) {
				case 'temp1': // Véranda
					$sensors[3]->record_data($value - 6.50, $time);
					break;
				case 'temp3': // Véranda
					$sensors[3]->record_data($value, $time);
					break;
				case 'hum1': // Véranda
					$sensors[8]->record_data($value, $time);
					break;
				case 'temp2': // Extérieur
					$sensors[4]->record_data($value * 0.85, $time);
					break;
				case 'hum2': // Extérieur
					$sensors[7]->record_data($value, $time);
					break;
				case 'temp_salon': // Salon
					$sensors[5]->record_data($value * 0.85, $time);
					break;
				case 'hum_salon': // Salon
					$sensors[6]->record_data($value, $time);
					break;
				case 'temp_entree': // Entrée CPU
					if ($time < 1510415401) {
						$sensors[2]->record_data($value, $time);
					}
					break;
				case 'temp_entree0': // Entrée USB
					if ($time >= 1510415401) {
						$sensors[2]->record_data($value, $time);
					}
					break;
				case 'temp_boite': // Boîte
					$sensors[1]->record_data($value, $time);
					break;
			}
		}
	}
}

$db->query("END TRANSACTION");



