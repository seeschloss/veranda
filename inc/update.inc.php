<?php

class Update {
	static function perform() {
		$db = new DB();

		if (!$db->query('SELECT COUNT(*) FROM plants')) {
			self::to_1();
		}

		if (!$db->query('SELECT dirt_x FROM plants LIMIT 1')) {
			self::to_2();
		}

		if (!$db->query('SELECT COUNT(*) FROM waterings')) {
			self::to_3();
		}

		if (!$db->query('SELECT COUNT(*) FROM photos')) {
			self::to_4();
			self::to_5();
		}

		if (!$db->query('SELECT COUNT(*) FROM videos')) {
			self::to_6();
		}

		if (!$db->query('SELECT place_id FROM plants LIMIT 1')) {
			self::to_7();
		}

		if (!$db->query('SELECT COUNT(*) FROM places')) {
			self::to_8();
		}

		if (!$db->query('SELECT COUNT(*) FROM sensors')) {
			self::to_9();
		}

		if (!$db->query('SELECT comment FROM places LIMIT 1')) {
			self::to_10();
		}

		if (!$db->query('SELECT comment FROM sensors LIMIT 1')) {
			self::to_11();
		}

		if (!$db->query('SELECT COUNT(*) FROM sensors_data')) {
			self::to_12();
		}

		if (!$db->query('SELECT place_id FROM photos LIMIT 1')) {
			self::to_13();
		}

		if (!$db->query('SELECT COUNT(*) FROM charts')) {
			self::to_14();
		}

		if (!$db->query('SELECT COUNT(*) FROM dashboard_photos')) {
			self::to_15();
		}

		if (!$db->query('SELECT public FROM places LIMIT 1')) {
			self::to_16();
		}

		if (!$db->query('SELECT COUNT(*) FROM devices')) {
			self::to_17();
		}

		if (!$db->query('SELECT COUNT(*) FROM devices_state')) {
			self::to_18();
		}

		if (!$db->query('SELECT place_id FROM videos LIMIT 1')) {
			self::to_19();
		}

		if (!$db->query('SELECT COUNT(*) FROM plant_notes')) {
			self::to_20();
		}

		if ($db->value('SELECT COUNT(*) FROM photos WHERE period=\'\'') > 0) {
			self::to_21();
		}

		if (!$db->query('SELECT COUNT(*) FROM alerts')) {
			self::to_22();
		}

		if (!$db->query('SELECT name FROM alerts LIMIT 1')) {
			self::to_23();
		}

		if (!$db->query('SELECT battery FROM sensors_data')) {
			self::to_24();
		}

		echo "All done.\n";
	}

	static function to_24() {
		echo "24. Add battery to table `sensors_data`\n";

		$db = new DB();
		$db->query('ALTER TABLE sensors_data ADD battery REAL');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_23() {
		echo "23. Add name to table `alerts`\n";

		$db = new DB();
		$db->query('ALTER TABLE alerts ADD name TEXT');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_22() {
		echo "22. Create tables `alerts` and `alert_event`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE alerts (
				id INTEGER PRIMARY KEY,
				type TEXT,
				dest TEXT,
				sensor_id INTEGER,
				min INTEGER,
				max INTEGER,
				timeout INTEGER,
				updated INTEGER,
				created INTEGER
			);'
		);
		if ($db->error()) {
			print $db->error()."\n";
		}

		$db->query(
			'CREATE TABLE alert_events (
				id INTEGER PRIMARY KEY,
				alert_id INTEGER,
				status TEXT,
				value REAL,
				timestamp INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_21() {
		echo "21. Set period to older photos\n";

		$db = new DB();
		$db->query("BEGIN TRANSACTION");

		foreach (Photo::select(['period' => '']) as $photo) {
			$photo->period = $photo->place()->period($photo->timestamp);
			$photo->update();
		}

		$db->query("END TRANSACTION");

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_20() {
		echo "20. Create table `plant_notes`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE plant_notes (
				id INTEGER PRIMARY KEY,
				plant_id INTEGER,
				note TEXT,
				timestamp INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_19() {
		echo "19. Add place_id to table `videos`\n";

		$db = new DB();
		$db->query('ALTER TABLE videos ADD place_id INTEGER');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_18() {
		echo "18. Create table `devices_state`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE devices_state (
				id INTEGER PRIMARY KEY,
				device_id INTEGER,
				place_id INTEGER,
				state TEXT,
				timestamp INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_17() {
		echo "17. Create table `devices`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE devices (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				name TEXT,
				type TEXT,
				comment TEXT,
				parameters TEXT,
				updated INTEGER,
				created INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_16() {
		echo "16. Add public to table `places`\n";

		$db = new DB();
		$db->query('ALTER TABLE places ADD public INTEGER');

		if ($db->error()) {
			print $db->error()."\n";
		}

		$db->query('UPDATE places SET public=1');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_15() {
		echo "15. Create table `dashboard_photos`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE dashboard_photos (
				id INTEGER PRIMARY KEY,
				place_id TEXT,
				size TEXT,
				updated INTEGER,
				inserted INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_14() {
		echo "14. Create table `charts`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE charts (
				id INTEGER PRIMARY KEY,
				title TEXT,
				size TEXT,
				period TEXT,
				type TEXT,
				parameters TEXT,
				updated INTEGER,
				inserted INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_13() {
		echo "13. Add place_id to table `photos`\n";

		$db = new DB();
		$db->query('ALTER TABLE photos ADD place_id INTEGER');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_12() {
		echo "12. Create table `sensors_data`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE sensors_data (
				id INTEGER PRIMARY KEY,
				sensor_id INTEGER,
				place_id INTEGER,
				value REAL,
				timestamp INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_11() {
		echo "11. Add comment to table `sensors`\n";

		$db = new DB();
		$db->query('ALTER TABLE sensors ADD comment TEXT');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_10() {
		echo "10. Add comment to table `places`\n";

		$db = new DB();
		$db->query('ALTER TABLE places ADD comment TEXT');

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_9() {
		echo "9. Create table `sensors`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE sensors (
				id INTEGER PRIMARY KEY,
				place_id INTEGER,
				name TEXT,
				type TEXT,
				parameters TEXT,
				updated INTEGER,
				created INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_8() {
		echo "8. Create table `places`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE places (
				id INTEGER PRIMARY KEY,
				name TEXT,
				type TEXT,
				updated INTEGER,
				created INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_7() {
		echo "7. Add place_id to table `plants`\n";

		$db = new DB();
		$db->query('ALTER TABLE plants ADD place_id INTEGER');

		if ($db->error()) {
			print $db->error()."\n";
		}

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_6() {
		echo "6. Create table `videos`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE videos (
				id INTEGER PRIMARY KEY,
				start INTEGER,
				stop INTEGER,
				path TEXT,
				fps INTEGER,
				quality TEXT
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_5() {
		echo "5. Initialize table `photos`\n";
		$photos = glob($GLOBALS['config']['pictures-dir'].'/*/*:??.jpg');

		$db = new DB();
		$db->query("BEGIN TRANSACTION");

		foreach ($photos as $simple_path) {
			$photo = new Photo();
			$photo->period = "jour";

			$basename = basename($simple_path, '.jpg');
			$photo->timestamp = DateTime::createFromFormat("Y-m-d@H:i:s", $basename)->getTimestamp();

			$hour = gmdate('H', $photo->timestamp);
			if ($hour == 6 || $hour == 18) {
				$photo->period = "twilight";
			} else if ($hour < 6 || $hour > 18) {
				$photo->period = "night";
			} else {
				$photo->period = "day";
			}

			$original_path = str_replace('.jpg', '-original.jpg', $simple_path);
			$balanced_path = str_replace('.jpg', '-balanced.jpg', $simple_path);
			$averaged_path = str_replace('.jpg', '-averaged.jpg', $simple_path);

			if (file_exists($original_path)) {
				$photo->path_original = realpath($original_path);
			} else {
				$photo->path_original = realpath($simple_path);
			}

			if (file_exists($balanced_path)) {
				$photo->path_balanced = realpath($balanced_path);
			}

			if (file_exists($averaged_path)) {
				$photo->path_averaged = realpath($averaged_path);
			}

			$photo->insert();
		}

		$db->query("END TRANSACTION");
	}

	static function to_4() {
		echo "4. Create table `photos`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE photos (
				id INTEGER PRIMARY KEY,
				timestamp INTEGER,
				period TEXT,
				path_original TEXT,
				path_balanced TEXT,
				path_averaged TEXT
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_3() {
		echo "3. Create table `waterings`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE waterings (
				id INTEGER PRIMARY KEY,
				plant_id INTEGER,
				date INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_2() {
		echo "2. Add dirt_{x,y,width,height} to table `plants`\n";

		$db = new DB();
		$db->query('ALTER TABLE plants ADD dirt_x INTEGER');
		$db->query('ALTER TABLE plants ADD dirt_y INTEGER');
		$db->query('ALTER TABLE plants ADD dirt_width INTEGER');
		$db->query('ALTER TABLE plants ADD dirt_height INTEGER');

		if ($db->error()) {
			print $db->error()."\n";
		}

		$db->query(
			'UPDATE plants SET dirt_x = box_x
			                 , dirt_y = box_y
			                 , dirt_width = box_width
			                 , dirt_height = box_height'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}

	static function to_1() {
		echo "1. Create table `plants`\n";

		$db = new DB();
		$db->query(
			'CREATE TABLE plants (
				id INTEGER PRIMARY KEY,
				name TEXT,
				latin_name TEXT,
				planted INTEGER,
				comment TEXT,
				box_x INTEGER,
				box_y INTEGER,
				box_width INTEGER,
				box_height INTEGER,
				updated INTEGER,
				created INTEGER
			);'
		);

		if ($db->error()) {
			print $db->error()."\n";
		}
	}
}
