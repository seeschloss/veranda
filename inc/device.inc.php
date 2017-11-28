<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Device extends Record {
	public static $table = "devices";

	public $id = 0;
	public $place_id = 0;
	public $name = "";
	public $type = "";
	public $comment = "";
	public $parameters = "";
	public $created = 0;
	public $updated = 0;

	static function grid_row_header_admin() {
		return [
			'name' => 'Name',
			'place' => 'Place',
			'type' => 'Type',
			'state' => 'State',
			'updated' => 'Last update',
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			$last_updated_class = "";

			if ($last_updated = $this->last_updated()) {
				$seconds = time() - $last_updated;
				$last_update = new DateTime();
				$last_update->setTimestamp($last_updated);

				$lag = $last_update->diff(new DateTime());

				$last_updated_string = "";
				if ($lag->d > 0) {
					$last_updated_string = $lag->format("%dd, %hh, %im, %ss");
					$last_updated_class = "alert";
				} else if ($lag->h > 0) {
					$last_updated_string = $lag->format("%hh, %im, %ss");
					$last_updated_class = "alert";
				} else if ($lag->i > 0) {
					if ($lag->i > 20) {
						$last_updated_class = "alert";
					}
					$last_updated_string = $lag->format("%im, %ss");
				} else if ($lag->s > 0) {
					$last_updated_string = $lag->format("%ss");
				}
			} else {
				$last_updated_string = "never";
			}

			return [
				'name' => "<a href='/admin/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
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
					'value' => "<a href='/admin/device/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 5],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => 'Name',
			'place' => 'Place',
			'type' => 'Type',
			'state' => 'State',
			'updated' => 'Last update',
		];
	}

	function grid_row() {
		if ($this->id) {
			$last_updated_class = "";

			if ($last_updated = $this->last_updated()) {
				$seconds = time() - $last_updated;
				$last_update = new DateTime();
				$last_update->setTimestamp($last_updated);

				$lag = $last_update->diff(new DateTime());

				$last_updated_string = "";
				if ($lag->d > 0) {
					$last_updated_string = $lag->format("%dd, %hh, %im, %ss");
					$last_updated_class = "alert";
				} else if ($lag->h > 0) {
					$last_updated_string = $lag->format("%hh, %im, %ss");
					$last_updated_class = "alert";
				} else if ($lag->i > 0) {
					if ($lag->i > 20) {
						$last_updated_class = "alert";
					}
					$last_updated_string = $lag->format("%im, %ss");
				} else if ($lag->s > 0) {
					$last_updated_string = $lag->format("%ss");
				}
			} else {
				$last_updated_string = "never";
			}

			return [
				'name' => "<a href='/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
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
					'value' => "<a href='/device/{$this->id}'>{$this->name}</a>",
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
		$form->fields['place_id']->label = "Place";

		$form->fields['name'] = new HTML_Input("device-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "device[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = "Name";

		$form->fields['type'] = new HTML_Select("device-type");
		$form->fields['type']->name = "device[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = [
			'heating' => "Heating",
		];
		$form->fields['type']->label = "Type";

		$form->fields['comment'] = new HTML_Textarea("device-comment");
		$form->fields['comment']->name = "device[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = "Comment";

		switch ($this->type) {
			case "heating":
				$form = $this->form_heating($form);
				break;
			default:
				break;
		}

		$form->actions['save'] = new HTML_Button("device-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = "Save";

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("device-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = "Delete";
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = "Are you sure you want to delete this device?";

		return $form->html();
	}

	function form_heating($form) {
		$this->parameters = $this->parameters();

		$sensors = Sensor::select(['type' => 'temperature', 'place_id' => $this->place_id]);

		$form->parameters['sensor'] = [
			'label' => "Sensor to use",
			'value' => "",
		];

		$form->parameters['sensor-0'] = new HTML_Input("device-sensor-0");
		$form->parameters['sensor-0']->type = "radio";
		$form->parameters['sensor-0']->name = "device[sensor][id]";
		$form->parameters['sensor-0']->value = 0;
		$form->parameters['sensor-0']->label = "None";
		if (!isset($this->parameters['sensor']) or !$this->parameters['sensor']['id']) {
			$form->parameters['sensor-0']->attributes['checked'] = "checked";
		}

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("device-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "radio";
			$form->parameters['sensor-'.$sensor->id]->name = "device[sensor][id]";
			$form->parameters['sensor-'.$sensor->id]->value = $sensor->id;
			$form->parameters['sensor-'.$sensor->id]->label = "{$sensor->name} ({$sensor->value_text()})";
			if (isset($this->parameters['sensor']) and $this->parameters['sensor']['id'] == $sensor->id) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_min = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_min->type = "number";
			$input_min->name = "device[sensor][min]";
			if (isset($this->parameters['sensor']) and $this->parameters['sensor']['id'] == $sensor->id) {
				$input_min->value = $this->parameters['sensor']['min'];
			}


			$input_max = new HTML_Input("device-sensor-{$sensor->id}-min");
			$input_max->type = "number";
			$input_max->name = "device[sensor][max]";
			if (isset($this->parameters['sensor']) and $this->parameters['sensor']['id'] == $sensor->id) {
				$input_max->value = $this->parameters['sensor']['max'];
			}

			$form->parameters['sensor-'.$sensor->id]->suffix = "min ".$input_min->element()."°C &mdash; max ".$input_max->element()."°C";
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
			case "heating":
				$this->from_form_parameters_sensor($data);
				break;
			default:
				break;
		}
	}

	function from_form_parameters_sensor($data) {
		$this->parameters = $this->parameters();

		if (isset($data['sensor']) and is_array($data['sensor'])) {
			$this->parameters['sensor'] = [
				'id' => $data['sensor']['id'],
				'min' => (float)$data['sensor']['min'],
				'max' => (float)$data['sensor']['max'],
			];
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

	function action() {
		switch ($this->type) {
			case "heating":
				return $this->check_temperature();
			default:
				return "nop";
		}
	}

	function check_temperature() {
		$this->parameters = $this->parameters();

		if (isset($this->parameters['sensor']['id']) and $this->parameters['sensor']['id']) {
			$this->parameters = $this->parameters();

			$sensor = new Sensor();
			$sensor->load(['id' => $this->parameters['sensor']['id']]);

			$value = $sensor->data_at(time());

			if ($value['value'] < $this->parameters['sensor']['min']) {
				return "on";
			} else if ($value['value'] > $this->parameters['sensor']['max']) {
				return "off";
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
		$state = $this->state_at(time());
		if ($state) {
			return $state['timestamp'];
		}

		return 0;
	}
}
