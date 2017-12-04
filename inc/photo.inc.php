<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Photo extends Record {
	public static $table = "photos";
	public static $relations = ['place_id' => 'Place'];
	public static $directory = __DIR__.'/../photos';

	public $id;
	public $place_id = 0;
	public $timestamp = 0;
	public $period = "";
	public $path_original = "";
	public $path_balanced = "";
	public $path_averaged = "";

	static function grid_row_header_admin() {
		return [
			'place' => __('Place'),
			'timestamp' => __('Date'),
			'link' => __('Link'),
		];
	}

	function grid_row_admin() {
		return [
			'place' => "<a href='{$GLOBALS['config']['base_path']}/admin/place/{$this->place_id}/photos'>{$this->place()->name}</a>",
			'timestamp' => date("r", $this->timestamp),
			'link' => "<a href='{$GLOBALS['config']['base_path']}/photo/{$this->place_id}/{$this->id}'>/photo/{$this->place_id}/{$this->id}</a>",
		];
	}

	function grid_row_admin_new() {
		return [
			'place' => [
				'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/photo/0'>".__('Add a new photo')."</a>",
				'attributes' => [
					'colspan' => 3,
				],
			],
		];
	}

	static function grid_row_header() {
		return [
			'place' => __('Place'),
			'timestamp' => __('Date'),
			'link' => __('Link'),
		];
	}

	function grid_row() {
		return [
			'place' => $this->place()->name,
			'timestamp' => date("r", $this->timestamp),
			'link' => "<a href='{$GLOBALS['config']['base_path']}/photo/{$this->place_id}/{$this->id}'>/photo/{$this->place_id}/{$this->id}</a>",
		];
	}

	function form() {
		$form = new HTML_Form();
		$form->attributes['class'] = "dl-form";

		$form->fields['id'] = new HTML_Input("photo-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "photo[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['place_id'] = new HTML_Select("photo-place-id");
		$form->fields['place_id']->name = "photo[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = array_map(function($place) { return $place->name; }, Place::select());
		$form->fields['place_id']->label = "Place";

		$form->fields['period'] = new HTML_Select("photo-period");
		$form->fields['period']->name = "photo[period]";
		$form->fields['period']->value = $this->period;
		$form->fields['period']->options = [
			'jour' => 'Day',
			'aube' => 'Twilight',
			'nuit' => 'Night',
		];
		$form->fields['period']->label = "Period";
		$form->fields['period']->suffix = "Twilight and night photos will be white-balanced if possible";

		$form->fields['exif'] = new HTML_Input_Checkbox("photo-exif");
		$form->fields['exif']->name = "photo[exif]";
		$form->fields['exif']->value = 1;
		$form->fields['exif']->label = "Use Exif information";

		$form->fields['timestamp'] = new HTML_Input_Datetime("photo-timestamp");
		$form->fields['timestamp']->name = "photo[timestamp]";
		$form->fields['timestamp']->value = $this->timestamp;
		$form->fields['timestamp']->label = "Time";
		$form->fields['timestamp']->suffix = "if Exif is not used or is missing";

		$form->fields['file'] = new HTML_Input("photo-file");
		$form->fields['file']->type = "file";
		$form->fields['file']->name = "photo[file]";
		$form->fields['file']->label = "Picture";

		if ($this->id > 0) {
			$form->actions['delete'] = new HTML_Button_Confirm("photo-delete");
			$form->actions['delete']->name = "action";
			$form->actions['delete']->label = "Delete";
			$form->actions['delete']->value = "delete";
			$form->actions['delete']->confirmation = "Are you sure you want to delete this photo?";
		} else {
			$form->actions['save'] = new HTML_Button("photo-save");
			$form->actions['save']->name = "action";
			$form->actions['save']->label = "Save";
			$form->actions['save']->value = "insert";
		}

		return $form->html();
	}

	function from_form($data) {
		if (isset($data['id'])) {
			$this->id = (int)$data['id'];

			if ($this->id) {
				$this->load(['id' => $this->id]);
			}
		}

		if (isset($data['place_id'])) {
			$this->place_id = $data['place_id'];
		}

		if (isset($data['timestamp'])) {
			$parts = explode('-', $data['timestamp']);
			$this->timestamp = (int)gmmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
		}

		debug::dump($this);
		die();
	}

	function place() {
		$place = new Place();
		$place->load(['id' => $this->place_id]);
		return $place;
	}

	function path($suffix = "") {
		$directory = self::$directory.'/'.date('Y-m-d', $this->timestamp);

		if (!file_exists($directory)) {
			mkdir($directory);
		}

		$filename = $this->place_id.'-'.date('Y-m-d@H:i:s', $this->timestamp);
		if ($suffix) {
			$filename .= '-'.$suffix;
		}
		$filename .= '.jpg';

		return $directory.'/'.$filename;
	}

	static function white_balance($path, $reference, $destination) {
		$histmatch = __DIR__.'/../cli/histmatch';
		`{$histmatch} -c rgb "{$reference}" "{$path}" "{$destination}"`;

		return file_exists($destination);
	}

	static function write_to($file, $data) {
		file_put_contents($file, $data);
		print "Writing ".strlen($data)." bytes to ".$file."...\n";
	}

	static function average($photos = [], $destination) {
		$list = implode(" ", $photos);
		`/usr/bin/convert {$list} -average "{$destination}"`;

		return file_exists($destination);
	}

	function save_balanced() {
		$photos = Photo::select([
			'place_id' => $this->place_id,
			'timestamp BETWEEN '.strtotime("yesterday 12:00:00", $this->timestamp).' AND '.strtotime("yesterday 13:00:00", $this->timestamp),
		], 'timestamp ASC', 1);
		
		if (count($photos)) {
			$yesterday_midday_photo = array_shift($photos);
			self::white_balance($this->path('original'), $yesterday_midday_photo->path('original'), $this->path('balanced'));
			$this->path_balanced = realpath($this->path('balanced'));
		}

		return file_exists($this->path_balanced);
	}

	function save_averaged() {
		$latest_night_photos = array_map(function($photo) {
			return $photo->path('balanced');
		}, Photo::select([
			'place_id' => $this->place_id,
			'timestamp < '.$this->timestamp,
		], 'timestamp DESC', 3));

		if (static::average($latest_night_photos, $this->path('averaged'))) {
			$this->path_averaged = realpath($this->path('averaged'));
		}

		return file_exists($this->path_averaged);
	}

	function save_file($data) {
		date_default_timezone_set("UTC");
		switch ($this->period) {
			case "aube":
				self::write_to($this->path('original'), $data);
				$this->path_original = realpath($this->path('original'));

				if ($this->save_balanced()) {
					if (file_exists($this->path())) { unlink($this->path()); }
					symlink($this->path('balanced'), $this->path());
				}

				if (!file_exists($this->path())) {
					symlink($this->path('original'), $this->path());
				}
				break;

			case "nuit":
				self::write_to($this->path('original'), $data);
				$this->path_original = realpath($this->path('original'));

				if ($this->save_balanced()) {
					if (file_exists($this->path())) { unlink($this->path()); }
					symlink($this->path('balanced'), $this->path());
				}

				if ($this->save_averaged()) {
					if (file_exists($this->path())) { unlink($this->path()); }
					symlink($this->path('averaged'), $this->path());
				}

				if (!file_exists($this->path())) {
					symlink($this->path('original'), $this->path());
				}
				break;

			case "jour":
			default:
				self::write_to($this->path(), $data);
				$this->path_original = realpath($this->path());
				break;
		}

		return true;
	}

	function best_quality() {
		if (file_exists($this->path_averaged)) {
			return $this->path_averaged;
		}

		if (file_exists($this->path_balanced)) {
			return $this->path_balanced;
		}

		if (file_exists($this->path_original)) {
			return $this->path_original;
		}

		if (file_exists($this->path())) {
			return $this->path();
		}

		return "";
	}

	function save($data) {
		$this->save_file($data);
		$this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'timestamp' => (int)$this->timestamp,
			'place_id' => (int)$this->place_id,
			'period' => $db->escape($this->period),
			'path_original' => $db->escape($this->path_original),
			'path_balanced' => $db->escape($this->path_balanced),
			'path_averaged' => $db->escape($this->path_averaged),
		];

		$query = 'INSERT INTO `'.self::$table.'` (' . implode(',', array_keys($fields))   . ') '.
		                                 'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function html() {
		return "<img src='/photo/{$this->place_id}/{$this->id}' />";
	}

	static function select_latest_by_place($conditions = []) {
		$latest_photos = [];

		foreach (Place::select($conditions) as $place) {
			if ($latest_photo = $place->photo_at(time())) {
				$latest_photos[] = $latest_photo;
			}
		}

		return $latest_photos;
	}
}

