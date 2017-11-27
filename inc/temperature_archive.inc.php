<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Temperature_Archive {
	public $files = [];
	public $precision = 1800;

	public $records = [];
	public $headers = [];


	function __construct() {
	}

	function merge() {
		$this->headers[] = 'time';

		foreach ($this->files as $file) {
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

			$this->headers = array_merge($this->headers, array_slice($header, 1));

			foreach ($lines as $line) {
				$line_fields = explode(' ', trim($line));

				$line_fields[$keys['time']] = floor($line_fields[$keys['time']] / $this->precision) * $this->precision;

				$line_data = [];
				foreach ($header as $index => $key) {
					if (isset($line_fields[$index])) {
						$line_data[$key] = $line_fields[$index];
					} else {
						$line_data[$key] = "-";
					}
				}

				if (!isset($this->records[$line_fields[$keys['time']]])) {
					$this->records[$line_fields[$keys['time']]] = [];
				}

				$this->records[$line_fields[$keys['time']]] += $line_data;
			}
		}

		ksort($this->records);

		return count($this->records);
	}

	function tsv() {
		$tsv = implode(' ', $this->headers)."\n";

		foreach ($this->records as $time => $line) {
			$data = [];

			foreach ($this->headers as $index => $key) {
				if (isset($line[$key])) {
					$data[$key] = $line[$key];
				} else {
					$data[$key] = "-";
				}
			}
			$tsv .= implode(' ', $data)."\n";
		}

		return $tsv;
	}
}
