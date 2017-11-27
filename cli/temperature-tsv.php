<?php

//echo `bzcat temperature.bz2`;

//echo `bash temperatures.sh`;

//die();

$files = [
	__DIR__.'/../data/temperature-veranda.tsv.bz2',
	__DIR__.'/../data/temperature-salon.tsv.bz2',
	__DIR__.'/../data/temperature-entree.tsv.bz2',
];

$data = [];
$headers = [
	'time',
];

$precision = 1800; // en secondes

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

echo implode(' ', $headers)."\n";
foreach ($data as $time => $line) {
	$data = [];

	foreach ($headers as $index => $key) {
		if (isset($line[$key])) {
			$data[$key] = $line[$key];
		} else {
			$data[$key] = "-";
		}
	}
	echo implode(' ', $data)."\n";
}

