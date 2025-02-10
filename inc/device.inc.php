<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Device extends Record {
	public static $table = "devices";
	public static $relations = ['place_id' => 'Place'];

	public $id = 0;
	public $place_id = 0;
	public $name = "";
	public $type = "";
	public $comment = "";
	public $parameters = "";
	public $created = 0;
	public $updated = 0;

	private $_last_updated = null;

	static function grid_row_header_admin() {
		return [
			'name' => __('Name'),
			'place' => __('Place'),
			'type' => __('Type'),
			'state' => __('State'),
			'updated' => __('Last update'),
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			$last_updated_string = Time::format_last_updated($this->last_updated());
			$last_updated_class = "";
			if (time() - $this->last_updated() > 60 * 20) {
				$last_updated_class = "alert";
			}
			if (time() - $this->last_updated() > 3600 * 24 * 30) {
				$last_updated_class = "inactive";
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/admin/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => _a('device-types', $this->type),
				'state' => $this->state_at(time())['state'],
				'updated' => [
					'value' => $last_updated_string,
					'attributes' => [
						'class' => $last_updated_class,
					],
				],
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/device/{$this->id}'>".__('Add a new device')."</a>",
					'attributes' => ['colspan' => 5],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => __('Name'),
			'place' => __('Place'),
			'type' => __('Type'),
			'state' => __('State'),
			'updated' => __('Last update'),
		];
	}

	function grid_row() {
		if ($this->id) {
			$last_updated_string = Time::format_last_updated($this->last_updated());
			$last_updated_class = "";
			if (time() - $this->last_updated() > 60 * 20) {
				$last_updated_class = "alert";
			}
			if (time() - $this->last_updated() > 3600 * 24 * 30) {
				$last_updated_class = "inactive";
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => _a('device-types', $this->type),
				'state' => $this->state_at(time())['state'],
				'updated' => [
					'value' => $last_updated_string,
					'attributes' => [
						'class' => $last_updated_class,
					],
				],
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/device/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 5],
				],
			];
		}
	}

	function place() {
		$place = new Place();
		$place->load(['id' => $this->place_id]);
		return $place;
	}

	function form() {
		$form = new HTML_Form();
		$form->attributes['class'] = "dl-form";

		$form->fields['id'] = new HTML_Input("device-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "device[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['place_id'] = new HTML_Select("device-place-id");
		$form->fields['place_id']->name = "device[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = array_map(function($place) { return $place->name; }, Place::select());
		$form->fields['place_id']->label = __("Place");

		$form->fields['name'] = new HTML_Input("device-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "device[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = __("Name");

		$form->fields['type'] = new HTML_Select("device-type");
		$form->fields['type']->name = "device[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = _a('device-types');
		$form->fields['type']->label = __("Type");

		$form->fields['comment'] = new HTML_Textarea("device-comment");
		$form->fields['comment']->name = "device[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = __("Comment");

		switch ($this->type) {
			case "intermittent":
				$form = $this->form_intermittent($form);
				break;
			case "humidifier":
				$form = $this->form_humidifier($form);
				break;
			case "heating":
				$form = $this->form_heating($form);
				break;
			case "lighting":
				$form = $this->form_lighting($form);
				break;
			case "ventilation":
				$form = $this->form_ventilation($form);
				break;
			case "microcontroller":
				$form = $this->form_microcontroller($form);
				break;
			default:
				break;
		}

		$form->actions['save'] = new HTML_Button("device-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("device-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to delete this device?");

		return $form->html();
	}

	function form_intermittent($form) {
		$this->parameters = $this->parameters();

		$form->parameters['span-on'] = new HTML_Input("device-span-on");
		$form->parameters['span-on']->type = "number";
		$form->parameters['span-on']->name = "device[span][on]";
		$form->parameters['span-on']->value = (int)$this->parameters['span']['on'];
		$form->parameters['span-on']->label = __("Turn on for (seconds)");

		$form->parameters['span-off'] = new HTML_Input("device-span-off");
		$form->parameters['span-off']->type = "number";
		$form->parameters['span-off']->name = "device[span][off]";
		$form->parameters['span-off']->value = (int)$this->parameters['span']['off'];
		$form->parameters['span-off']->label = __("Turn off for (seconds)");

		$form->parameters['span-night-on'] = new HTML_Input("device-span-night-on");
		$form->parameters['span-night-on']->type = "number";
		$form->parameters['span-night-on']->name = "device[span-night][on]";
		$form->parameters['span-night-on']->value = (int)$this->parameters['span-night']['on'];
		$form->parameters['span-night-on']->label = __("Turn on for (seconds) at night");

		$form->parameters['span-night-off'] = new HTML_Input("device-span-night-off");
		$form->parameters['span-night-off']->type = "number";
		$form->parameters['span-night-off']->name = "device[span-night][off]";
		$form->parameters['span-night-off']->value = (int)$this->parameters['span-night']['off'];
		$form->parameters['span-night-off']->label = __("Turn off for (seconds) at night");

		return $form;
	}

	function form_humidifier($form) {
		$this->parameters = $this->parameters();

		$sensors = Sensor::select(['type' => 'humidity', 'place_id' => $this->place_id]);

		$form->parameters['sensor'] = [
			'label' => __("Sensors"),
			'value' => "",
		];

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("device-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "checkbox";
			$form->parameters['sensor-'.$sensor->id]->name = "device[sensor][{$sensor->id}][id]";
			$form->parameters['sensor-'.$sensor->id]->value = $sensor->id;
			$form->parameters['sensor-'.$sensor->id]->label = "{$sensor->name} ({$sensor->value_text()})";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_min = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_min->type = "number";
			$input_min->attributes['step'] = "1";
			$input_min->name = "device[sensor][{$sensor->id}][min]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_min->value = $this->parameters['sensors'][$sensor->id]['min'];
			}


			$input_max = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_max->type = "number";
			$input_max->attributes['step'] = "1";
			$input_max->name = "device[sensor][{$sensor->id}][max]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_max->value = $this->parameters['sensors'][$sensor->id]['max'];
			}

			$form->parameters['sensor-'.$sensor->id]->suffix = "min ".$input_min->element()."% &mdash; max ".$input_max->element()."%";
		}

		return $form;
	}

	function form_heating($form) {
		$this->parameters = $this->parameters();

		$form->parameters['power'] = new HTML_Input("device-power");
		$form->parameters['power']->type = "number";
		$form->parameters['power']->name = "device[power]";
		$form->parameters['power']->value = $this->parameters['power'];
		$form->parameters['power']->label = __("Power");
		$form->parameters['power']->suffix = "W";

		$form->parameters['night'] = new HTML_Input("device-night-drop");
		$form->parameters['night']->type = "number";
		$form->parameters['night']->attributes['step'] = "0.1";
		$form->parameters['night']->name = "device[night-drop]";
		$form->parameters['night']->value = $this->parameters['night-drop'];
		$form->parameters['night']->label = __("Night temperature drop");
		$form->parameters['night']->suffix = "°C";

		$inputs = array();
		for ($hour = 0; $hour < 24; $hour += 1) {
			$input = new HTML_Input("device-night-hours-".$hour);
			$input->type = "checkbox";
			$input->name = "device[night-hours][{$hour}]";
			$input->value = 1;
			$input->attributes['title'] = $hour."h";
			if (isset($this->parameters['night-hours'][$hour])) {
				$input->attributes['checked'] = "checked";
			}
			$inputs[$hour] = $input->element();
		}

		$form->parameters['night-hours'] = [
			'label' => __("Night hours"),
			'value' => join("", $inputs),
		];

		$sensors = Sensor::select(['type' => 'temperature', 'place_id' => $this->place_id]);

		$form->parameters['sensor'] = [
			'label' => __("Sensors"),
			'value' => "",
		];

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("device-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "checkbox";
			$form->parameters['sensor-'.$sensor->id]->name = "device[sensor][{$sensor->id}][id]";
			$form->parameters['sensor-'.$sensor->id]->value = $sensor->id;
			$form->parameters['sensor-'.$sensor->id]->label = "{$sensor->name} ({$sensor->value_text()})";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_min = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_min->type = "number";
			$input_min->attributes['step'] = "0.1";
			$input_min->name = "device[sensor][{$sensor->id}][min]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_min->value = $this->parameters['sensors'][$sensor->id]['min'];
			}


			$input_max = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_max->type = "number";
			$input_max->attributes['step'] = "0.1";
			$input_max->name = "device[sensor][{$sensor->id}][max]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_max->value = $this->parameters['sensors'][$sensor->id]['max'];
			}

			$form->parameters['sensor-'.$sensor->id]->suffix = "min ".$input_min->element()."°C &mdash; max ".$input_max->element()."°C";
		}

		return $form;
	}

	function form_lighting($form) {
		$this->parameters = $this->parameters();

		$form->parameters['period'] = [
			'label' => __("Lighting period"),
			'value' => "",
		];

		$form->parameters['period-start'] = new HTML_Input("device-period-start");
		$form->parameters['period-start']->type = "time";
		$form->parameters['period-start']->name = "device[period][start]";
		$form->parameters['period-start']->value = gmdate('H:i:s', $this->parameters['period']['start']);
		$form->parameters['period-start']->label = __("Turn on at");

		$form->parameters['period-stop'] = new HTML_Input("device-period-stop");
		$form->parameters['period-stop']->type = "time";
		$form->parameters['period-stop']->name = "device[period][stop]";
		$form->parameters['period-stop']->value = gmdate('H:i:s', $this->parameters['period']['stop']);
		$form->parameters['period-stop']->label = __("Turn off at");

		return $form;
	}

	function form_ventilation($form) {
		$this->parameters = $this->parameters();

		$form->parameters['min-speed'] = new HTML_Input("device-min-speed");
		$form->parameters['min-speed']->type = "number";
		$form->parameters['min-speed']->step = "1";
		$form->parameters['min-speed']->name = "device[speed][min]";
		$form->parameters['min-speed']->value = $this->parameters['speed']['min'];
		$form->parameters['min-speed']->label = __("Minimum speed");

		$sensors = Sensor::select(['type' => ['humidity'], 'place_id' => $this->place_id]);

		$form->parameters['sensor'] = [
			'label' => __("Sensors"),
			'value' => "",
		];

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("device-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "checkbox";
			$form->parameters['sensor-'.$sensor->id]->name = "device[sensor][{$sensor->id}][id]";
			$form->parameters['sensor-'.$sensor->id]->value = $sensor->id;
			$form->parameters['sensor-'.$sensor->id]->label = "{$sensor->name} ({$sensor->value_text()})";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_min = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_min->type = "number";
			$input_min->attributes['step'] = "1";
			$input_min->name = "device[sensor][{$sensor->id}][min]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_min->value = $this->parameters['sensors'][$sensor->id]['min'];
			}


			$input_max = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_max->type = "number";
			$input_max->attributes['step'] = "1";
			$input_max->name = "device[sensor][{$sensor->id}][max]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_max->value = $this->parameters['sensors'][$sensor->id]['max'];
			}

			$form->parameters['sensor-'.$sensor->id]->suffix = "min ".$input_min->element()."% &mdash; max ".$input_max->element()."%";
		}

		$sensors = Sensor::select(['type' => ['gas']]);
		//$sensors = Sensor::select(['type' => ['humidity', 'gas'], 'place_id' => $this->place_id]);

		$form->parameters['sensor'] = [
			'label' => __("Sensors"),
			'value' => "",
		];

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("device-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "checkbox";
			$form->parameters['sensor-'.$sensor->id]->name = "device[sensor][{$sensor->id}][id]";
			$form->parameters['sensor-'.$sensor->id]->value = $sensor->id;
			$form->parameters['sensor-'.$sensor->id]->label = "{$sensor->name} ({$sensor->value_text()})";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_min = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_min->type = "number";
			$input_min->attributes['step'] = "1";
			$input_min->name = "device[sensor][{$sensor->id}][min]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_min->value = $this->parameters['sensors'][$sensor->id]['min'];
			}


			$input_max = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_max->type = "number";
			$input_max->attributes['step'] = "1";
			$input_max->name = "device[sensor][{$sensor->id}][max]";
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$input_max->value = $this->parameters['sensors'][$sensor->id]['max'];
			}

			$form->parameters['sensor-'.$sensor->id]->suffix = "min ".$input_min->element()." ppm &mdash; max ".$input_max->element()." ppm";
		}

		return $form;
	}

	function form_microcontroller($form) {
		$this->parameters = $this->parameters();

		$form->parameters['api-key'] = new HTML_Input("device-api-key");
		$form->parameters['api-key']->type = "text";
		$form->parameters['api-key']->name = "device[api-key]";
		$form->parameters['api-key']->value = $this->parameters['api-key'];
		$form->parameters['api-key']->label = __("API key");

		$form->parameters['interval'] = new HTML_Input("device-interval");
		$form->parameters['interval']->type = "number";
		$form->parameters['interval']->name = "device[interval]";
		$form->parameters['interval']->value = (int)$this->parameters['interval'];
		$form->parameters['interval']->label = __("Wake-up interval (seconds)");

		$form->parameters['jpeg-quality'] = new HTML_Input("device-jpeg-quality");
		$form->parameters['jpeg-quality']->type = "number";
		$form->parameters['jpeg-quality']->name = "device[jpeg-quality]";
		$form->parameters['jpeg-quality']->value = (int)$this->parameters['jpeg-quality'];
		$form->parameters['jpeg-quality']->label = __("JPEG quality (0-100)");

		$form->parameters['firmware-version'] = new HTML_Input("device-firmware-version");
		$form->parameters['firmware-version']->type = "text";
		$form->parameters['firmware-version']->name = "device[firmware-version]";
		$form->parameters['firmware-version']->value = $this->parameters['firmware-version'];
		$form->parameters['firmware-version']->label = __("Latest firmware version");

		$form->parameters['firmware'] = new HTML_Input("device-firmware");
		$form->parameters['firmware']->type = "file";
		$form->parameters['firmware']->name = "device[firmware]";
		$form->parameters['firmware']->label = __("Latest firmware");

		if (!empty($this->parameters['firmware'])) {
			$file = new File();
			$file->load(['id' => $this->parameters['firmware']]);

			$form->parameters['firmware']->suffix = '<a href="'.$file->url().'">'.$file->name.'</a>';
		}

		return $form;
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

		if (isset($data['name'])) {
			$this->name = $data['name'];
		}

		if (isset($data['type'])) {
			$this->type = $data['type'];
		}

		if (isset($data['comment'])) {
			$this->comment = $data['comment'];
		}

		switch ($this->type) {
			case "intermittent":
				$this->from_form_parameters_intermittent($data);
				break;
			case "humidifier":
				$this->from_form_parameters_sensor($data);
				break;
			case "heating":
				$this->from_form_parameters_power($data);
				$this->from_form_parameters_night($data);
				$this->from_form_parameters_night_hours($data);
				$this->from_form_parameters_sensor($data);
				break;
			case "lighting":
				$this->from_form_parameters_lighting($data);
				break;
			case "ventilation":
				$this->from_form_parameters_ventilation($data);
				$this->from_form_parameters_sensor($data);
				break;
			case "microcontroller":
				$this->from_form_parameters_microcontroller($data);
				break;
			default:
				break;
		}
	}

	function from_form_parameters_ventilation($data) {
		$this->parameters = $this->parameters();

		if (isset($data['speed'])) {
			$this->parameters['speed']['min'] = (int)$data['speed']['min'];
		}
	}

	function from_form_parameters_intermittent($data) {
		$this->parameters = $this->parameters();

		if (isset($data['span']) and is_array($data['span'])) {
			$this->parameters['span'] = [
				'on' => $data['span']['on'],
				'off' => $data['span']['off'],
			];
		}

		if (isset($data['span-night']) and is_array($data['span-night'])) {
			$this->parameters['span-night'] = [
				'on' => $data['span-night']['on'],
				'off' => $data['span-night']['off'],
			];
		}
	}

	function from_form_parameters_power($data) {
		$this->parameters = $this->parameters();

		if (isset($data['power'])) {
			$this->parameters['power'] = (float)$data['power'];
		}
	}

	function from_form_parameters_night($data) {
		$this->parameters = $this->parameters();

		if (isset($data['night-drop'])) {
			$this->parameters['night-drop'] = (float)$data['night-drop'];
		}
	}

	function from_form_parameters_night_hours($data) {
		$this->parameters = $this->parameters();

		if (isset($data['night-hours']) and is_array($data['night-hours'])) {
			$this->parameters['night-hours'] = [];

			foreach ($data['night-hours'] as $hour => $night) {
				$this->parameters['night-hours'][$hour] = true;
			}
		}
	}

	function from_form_parameters_sensor($data) {
		$this->parameters = $this->parameters();

		if (isset($data['sensor']) and is_array($data['sensor'])) {
			$this->parameters['sensors'] = [];

			foreach ($data['sensor'] as $id => $sensor_data) {
				if (isset($sensor_data['id']) and $sensor_data['id']) {
					$this->parameters['sensors'][$id] = [
						'id' => $id,
						'min' => $sensor_data['min'] !== "" ? (float)$sensor_data['min'] : null,
						'max' => $sensor_data['max'] !== "" ? (float)$sensor_data['max'] : null,
					];
				}
			}
		}
	}

	function from_form_parameters_lighting($data) {
		$this->parameters = $this->parameters();

		if (isset($data['period']) and is_array($data['period'])) {
			list($start_h, $start_m) = explode(':', $data['period']['start']);
			list($stop_h, $stop_m) = explode(':', $data['period']['stop']);
			$this->parameters['period'] = [
				'start' => $start_h * 3600 + $start_m * 60,
				'stop' => $stop_h * 3600 + $stop_m * 60
			];
		}
	}

	function from_form_parameters_microcontroller($data) {
		$this->parameters = $this->parameters();

		foreach (['api-key', 'interval', 'jpeg-quality', 'firmware-version'] as $parameter) {
			if (isset($data[$parameter])) {
				$this->parameters[$parameter] = $data[$parameter];
			}
		}

		if (!empty($_FILES['device']['tmp_name']['firmware'])) {
			$contents = file_get_contents($_FILES['device']['tmp_name']['firmware']);
			$name = $_FILES['device']['name']['firmware'];

			$file = new File();
			$file->name = $name;
			$file->save($contents);

			$this->parameters['firmware'] = $file->id;
		}
	}

	function parameters() {
		if (is_array($this->parameters)) {
			return $this->parameters;
		} else if ($this->parameters and $parameters = json_decode($this->parameters, true) and is_array($parameters)) {
			$this->parameters = $parameters;
			return $this->parameters;
		} else {
			$this->parameters = [];
			return $this->parameters;
		}
	}

	function save() {
		return $this->id > 0 ? $this->update() : $this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'name' => $db->escape($this->name),
			'type' => $db->escape($this->type),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'comment' => $db->escape($this->comment),
			'created' => time(),
			'updated' => time(),
		];

		$query = 'INSERT INTO devices (' . implode(',', array_keys($fields)) . ') '.
		                      'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function update() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'name' => $db->escape($this->name),
			'type' => $db->escape($this->type),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'comment' => $db->escape($this->comment),
			'updated' => time(),
		];

		$query = 'UPDATE devices SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM devices WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function action_json() {
		$action = ['action' => $this->action()];

		switch ($this->type) {
			case "ventilation":
				$action['speed'] = $this->ventilation_speed();
		}

		return json_encode($action);
	}

	function action() {
		switch ($this->type) {
			case "intermittent":
				return $this->check_span();
			case "humidifier":
				return $this->check_humidity_inside_range();
			case "heating":
				return $this->check_temperature();
			case "lighting":
				return $this->check_period();
			case "ventilation":
				return $this->check_humidity_outside_range();
			default:
				return "nop";
		}
	}

	function check_span() {
		$this->parameters = $this->parameters();

		$span_on = 0;
		$span_off = 0;
		if (Time::artificial_period(time()) == "night") {
			if (isset($this->parameters['span-night']['on']) and $this->parameters['span-night']['on'] and
				isset($this->parameters['span-night']['off']) and $this->parameters['span-night']['off']) {
				$span_on = $this->parameters['span-night']['on'];
				$span_off = $this->parameters['span-night']['off'];
			}
		} else {
			if (isset($this->parameters['span']['on']) and $this->parameters['span']['on'] and
				isset($this->parameters['span']['off']) and $this->parameters['span']['off']) {
				$span_on = $this->parameters['span']['on'];
				$span_off = $this->parameters['span']['off'];
			}
		}

		if ($span_on > 0 and $span_off > 0) {
			$state_now = $this->state_at(time())['state'];
			$new_state = 'nop';
			if ($state_now == 'on') {
				$states_past = $this->state_between(time() - $span_on, time());

				if (array_search('off', $states_past) === false) {
					$new_state = 'off';
				}
			} else if ($state_now == 'off') {
				$states_past = $this->state_between(time() - $span_off, time());

				if (array_search('on', $states_past) === false) {
					$new_state = 'on';
				}
			} else {
				$new_state = 'on';
			}

			return $new_state;
		}

		return "nop";
	}

	function check_temperature() {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $id]);

				$value = $sensor->data_at(time());

				$min = isset($parameters['min']) ? $parameters['min'] : null;
				$max = isset($parameters['max']) ? $parameters['min'] : null;

				if (isset($this->parameters['night-drop']) and $this->parameters['night-drop'] != 0) {
					if (is_array($this->parameters['night-hours']) and !empty($this->parameters['night-hours'])) {
						if (isset($this->parameters['night-hours'][(int)Time::hour()])) {
							$min -= $this->parameters['night-drop'];
							$max -= $this->parameters['night-drop'];
						}
					} else if (Time::period(time()) == "night") {
						$min -= $this->parameters['night-drop'];
						$max -= $this->parameters['night-drop'];
					}
				}

				if (isset($min) and $value['value'] < $min) {
					return "on";
				} else if (isset($max) and $value['value'] > $max) {
					return "off";
				}
			}
		}

		return "nop";
	}

	function check_period_future($time) {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['period']['start']) and $this->parameters['period']['start'] and
		    isset($this->parameters['period']['stop']) and $this->parameters['period']['stop']) {

			$time = gmdate('H', $time) * 3600 + gmdate('i', $time) * 60 + gmdate('s', $time);

			if ($time >= $this->parameters['period']['start'] and $time < $this->parameters['period']['stop']) {
				return 'on';
			} else {
				return 'off';
			}
		}

		return "off";
	}

	function check_period() {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['period']['start']) and $this->parameters['period']['start'] and
		    isset($this->parameters['period']['stop']) and $this->parameters['period']['stop']) {

			$time_now = gmdate('H') * 3600 + gmdate('i') * 60 + gmdate('s');
			$state_now = $this->state_at(time())['state'];

			if ($time_now >= $this->parameters['period']['start'] and $time_now < $this->parameters['period']['stop']) {
				if ($state_now != 'on') {
					return 'on';
				}
			} else {
				if ($state_now != 'off') {
					return 'off';
				}
			}
		}

		return "nop";
	}

	function check_humidity_outside_range() {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $id]);

				$value = $sensor->data_at(time());

				$min = isset($parameters['min']) ? $parameters['min'] : null;
				$max = isset($parameters['max']) ? $parameters['min'] : null;

				if (isset($min) and $value['value'] < $min) {
					return "off";
				} else if (isset($max) and $value['value'] > $max) {
					return "on";
				}
			}
		}

		return "nop";
	}

	function ventilation_speed() {
		$this->parameters = $this->parameters();

		$speed = $this->parameters['speed']['min'];
		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $id]);

				$value = $sensor->data_at(time());

				$min = isset($parameters['min']) ? $parameters['min'] : null;
				$max = isset($parameters['max']) ? $parameters['max'] : null;

				if (isset($min) and $value['value'] < $min) {
					$sensor_speed = $this->parameters['speed']['min'];
				} else if (isset($max) and $value['value'] > $max) {
					$sensor_speed = 100;
				} else {
					$sensor_speed = $this->parameters['speed']['min'] + floor((($value['value'] - $min) / ($max - $min)) * (100 - $this->parameters['speed']['min']));
				}

				$speed = max($speed, $sensor_speed);
			}
		}

		return $speed;
	}

	function check_humidity_inside_range() {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $id]);

				$value = $sensor->data_at(time());

				$min = isset($parameters['min']) ? $parameters['min'] : null;
				$max = isset($parameters['max']) ? $parameters['min'] : null;

				if (isset($min) and $value['value'] < $min) {
					return "on";
				} else if (isset($max) and $value['value'] > $max) {
					return "off";
				}
			}
		}

		return "nop";
	}

	function record_state($state, $timestamp) {
		$db = new DB();

		$fields = [
			'device_id' => (int)$this->id,
			'place_id' => (int)$this->place_id,
			'state' => $db->escape($state),
			'timestamp' => $timestamp,
		];

		$query = 'INSERT INTO devices_state (' . implode(',', array_keys($fields)) . ') '.
		                            'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		return $db->insert_id();
	}

	function state_at($timestamp) {
		$db = new DB();

		$query = 'SELECT state, timestamp '.
		           'FROM devices_state '.
				  'WHERE timestamp <= '.(int)$timestamp.' '.
				    'AND device_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp DESC '.
				  'LIMIT 1';

		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			return $record;
		}
	}

	function state_between($start, $stop) {
		$db = new DB();

		$query = 'SELECT state, timestamp '.
		           'FROM devices_state '.
				  'WHERE timestamp BETWEEN '.(int)$start.' AND '.(int)$stop.' '.
				    'AND device_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp DESC';

		$state = [];
		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			$state[(int)$record['timestamp']] = $record['state'];
		}

		return $state;
	}

	function last_updated() {
		if ($this->_last_updated === null) {
			$this->_last_updated = 0;
			$state = $this->state_at(time());
			if ($state) {
				$this->_last_updated = $state['timestamp'];
			}
		}


		return $this->_last_updated;
	}

	function chart() {
		$chart = new Chart();
		$chart->id = "device-{$this->id}-line";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = "1-day";
		$chart->type = "line";
		$chart->parameters = [
			'devices' => [
				$this->id => [
					'id' => $this->id,
					'color' => '#2F2F2F',
				],
			]
		];

		$html = $chart->html();

		return $html;
	}

	function details_intermittent() {
		$this->parameters = $this->parameters();

		$html = "";

		$html = $this->chart();

		return $html;
	}

	function details_humidity() {
		$this->parameters = $this->parameters();

		$html = "";

		$html = $this->chart();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $sensor_id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $sensor_id]);
				$html .= $sensor->chart_line("1-day");
			}
		}

		return $html;
	}

	function details_heating() {
		$this->parameters = $this->parameters();

		$html = "";

		$html = $this->chart();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $sensor_id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $sensor_id]);
				$html .= $sensor->chart_line("1-day");
			}
		}

		return $html;
	}

	function details_lighting() {
		$this->parameters = $this->parameters();

		$html = "";

		$html = $this->chart();

		return $html;
	}

	function details_ventilation() {
		$this->parameters = $this->parameters();

		$html = "";

		$html = $this->chart();

		if (isset($this->parameters['sensors'])) {
			foreach ($this->parameters['sensors'] as $sensor_id => $parameters) {
				$sensor = new Sensor();
				$sensor->load(['id' => $sensor_id]);
				$html .= $sensor->chart_line("1-day");
			}
		}

		return $html;
	}

	function details() {
		$html = "";

		switch ($this->type) {
			case "intermittent":
				$html .= $this->details_intermittent();
				break;
			case "humidifier":
				$html .= $this->details_humidity();
				break;
			case "heating":
				$html .= $this->details_heating();
				break;
			case "lighting":
				$html .= $this->details_lighting();
				break;
			case "ventilation":
				$html .= $this->details_ventilation();
				break;
		}

		return $html;
	}
}
