<?php
class DB {
	static private $resource;

	function schema() {
		return [
			'CREATE TABLE plants (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				name TEXT,
				latin_name TEXT,
				planted INTEGER,
				comment TEXT,
				box_x INTEGER,
				box_y INTEGER,
				box_width INTEGER,
				box_height INTEGER,
				dirt_x INTEGER,
				dirt_y INTEGER,
				dirt_width INTEGER,
				dirt_height INTEGER,
				updated INTEGER,
				created INTEGER
			);',
			'CREATE TABLE waterings (
				id INTEGER PRIMARY KEY,
				plant_id INTEGER,
				date INTEGER
			);',
			'CREATE TABLE photos (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				timestamp INTEGER,
				period TEXT,
				path_original TEXT,
				path_balanced TEXT,
				path_averaged TEXT
			);',
			'CREATE TABLE videos (
				id INTEGER PRIMARY KEY,
				start INTEGER,
				stop INTEGER,
				path TEXT,
				fps INTEGER,
				quality TEXT
			);',
			'CREATE TABLE places (
				id INTEGER PRIMARY KEY,
				name TEXT,
				type TEXT,
				public INTEGER,
				comment TEXT,
				updated INTEGER,
				created INTEGER
			);',
			'CREATE TABLE sensors (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				name TEXT,
				type TEXT,
				comment TEXT,
				parameters TEXT,
				updated INTEGER,
				created INTEGER
			);',
			'CREATE TABLE sensors_data (
				id INTEGER PRIMARY KEY,
				sensor_id INTEGER,
				place_id INTEGER,
				value REAL,
				timestamp INTEGER
			);',
			'CREATE TABLE charts (
				id INTEGER PRIMARY KEY,
				title TEXT,
				size TEXT,
				period TEXT,
				type TEXT,
				parameters TEXT,
				updated INTEGER,
				inserted INTEGER
			);',
			'CREATE TABLE dashboard_photos (
				id INTEGER PRIMARY KEY,
				place_id TEXT,
				size TEXT,
				updated INTEGER,
				inserted INTEGER
			);',
			'CREATE TABLE devices (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				name TEXT,
				type TEXT,
				comment TEXT,
				parameters TEXT,
				updated INTEGER,
				created INTEGER
			);',
			'CREATE TABLE devices_state (
				id INTEGER PRIMARY KEY,
				device_id INTEGER,
				place_id INTEGER,
				state TEXT,
				timestamp INTEGER
			);',
		];
	}

	function init_schema() {
		foreach ($this->schema() as $table) {
			$this->query($table);
		}
	}

	function __construct() {
		if (!isset(self::$resource)) {
			self::$resource = new PDO($GLOBALS['config']['database']['dsn']);

			$result = $this->query("SELECT COUNT(*) FROM plants");
			if (!$result) {
				$this->init_schema();
			}
		}
	}

	function error() {
		$error = self::$resource->errorInfo();

		return is_array($error) ? $error[2] : "";
	}

	function query($query) {
		$result = self::$resource->query($query);
		if (!$result) {
			$error = $this->error();
			if (class_exists('Logger')) {
				Logger::error($error);
				Logger::error("Query was: ".$query);
			}
			else {
				trigger_error($error);
				trigger_error("Query was: ".$query);
			}
		}
		return $result;
	}

	function value($query) {
		$result = $this->query($query);
		if ($result) while ($row = $result->fetch()) {
			return $row[0];
		}

		return '';
	}

	function escape($string) {
		return self::$resource->quote($string);
	}

	function insert_id() {
		return self::$resource->lastInsertId();
	}
}

