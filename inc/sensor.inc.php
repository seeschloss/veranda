<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Sensor extends Record {
	public static $table = "sensors";
	public static $relations = ['place_id' => 'Place'];

	public $id = 0;
	public $place_id = 0;
	public $name = "";
	public $type = "";
	public $comment = "";
	public $parameters = "";
	public $created = 0;
	public $updated = 0;
	public $archived = 0;

	static function filter($filters, $forced = []) {
		$fields = [];

		if (isset($filters['place']) and $filters['place'] > 0) {
			$fields['place_id'] = (int)$filters['place'];
		}

		if (isset($filters['type']) and $filters['type']) {
			$fields['type'] = $filters['type'];
		}

		if (isset($filters['archived']) and $filters['archived']) {
			$fields['archived'] = 1;
		} else {
			$fields['archived'] = 0;
		}

		$fields += $forced;

		return self::select($fields);
	}

	static function filters() {
		$filters = [];

		$filters['place_id'] = new HTML_Select("plant-place-id");
		$filters['place_id']->name = "place";
		$filters['place_id']->options = [0 => ''] + array_map(function($place) { return $place->name; }, Place::select());
		$filters['place_id']->label = __("Place");
		if (isset($_REQUEST['place'])) {
			$filters['place_id']->value = $_REQUEST['place'];
		}

		$filters['type'] = new HTML_Select("sensor-type");
		$filters['type']->name = "type";
		$filters['type']->options = ['' => ''] + _a('sensor-types');
		$filters['type']->label = __("Type");
		if (isset($_REQUEST['type'])) {
			$filters['type']->value = $_REQUEST['type'];
		}

		$filters['archived'] = new HTML_Input_Checkbox("sensor-archived");
		$filters['archived']->name = "archived";
		$filters['archived']->label = __("Archived");
		if (isset($_REQUEST['archived'])) {
			$filters['archived']->value = $_REQUEST['archived'];
		}

		return $filters;
	}

	static function grid_row_header_admin() {
		return [
			'name' => __('Name'),
			'place' => __('Place'),
			'type' => __('Type'),
			'value' => __('Value'),
			'battery' => __('Battery'),
			'updated' => __('Last update'),
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
				if ($lag->y > 0 or $lag->m > 0) {
					$last_updated_string = $last_update->format("Y-m-d");
					$last_updated_class = "inactive";
				} else if ($lag->d > 0) {
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
				$last_updated_string = __("never");
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/admin/sensor/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => _a('sensor-types', $this->type),
				'value' => $this->value_text(),
				'battery' => $this->battery_text(),
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
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/sensor/{$this->id}'>".__('Add a new sensor')."</a>",
					'attributes' => ['colspan' => 6],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => __('Name'),
			'place' => __('Place'),
			'type' => __('Type'),
			'value' => __('Value'),
			'battery' => __('Battery'),
			'updated' => __('Last update'),
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
				if ($lag->m > 0) {
					$last_updated_string = $last_update->format("Y-m-d");
					$last_updated_class = "inactive";
				} else if ($lag->d > 0) {
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
				$last_updated_string = __("never");
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/sensor/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => _a('sensor-types', $this->type),
				'value' => $this->value_text(),
				'battery' => $this->battery_text(),
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
					'value' => "<a href='{$GLOBALS['config']['base_path']}/sensor/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 6],
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

		$form->fields['id'] = new HTML_Input("sensor-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "sensor[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['place_id'] = new HTML_Select("sensor-place-id");
		$form->fields['place_id']->name = "sensor[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = array_map(function($place) { return $place->name; }, Place::select());
		$form->fields['place_id']->label = __("Place");

		$form->fields['name'] = new HTML_Input("sensor-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "sensor[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = __("Name");

		$form->fields['type'] = new HTML_Select("sensor-type");
		$form->fields['type']->name = "sensor[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = _a('sensor-types');
		$form->fields['type']->label = __("Type");

		$form->fields['comment'] = new HTML_Textarea("sensor-comment");
		$form->fields['comment']->name = "sensor[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = __("Comment");

		$form->fields['archived'] = new HTML_Input_Checkbox("sensor-archived");
		$form->fields['archived']->name = "sensor[archived]";
		$form->fields['archived']->value = $this->archived;
		$form->fields['archived']->label = __("Archived");

		switch ($this->type) {
			case "electricity":
				$form = $this->form_electricity($form);
				break;
			default:
				break;
		}

		$form->actions['save'] = new HTML_Button("sensor-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("sensor-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to delete this sensor?");

		return $form->html();
	}

	function form_electricity($form) {
		$this->parameters = $this->parameters();

		$form->parameters['price'] = [
			'label' => __("Price"),
			'value' => "",
		];

		$form->parameters['price-value'] = new HTML_Input("sensor-price");
		$form->parameters['price-value']->type = "number";
		$form->parameters['price-value']->attributes['step'] = "0.00001";
		$form->parameters['price-value']->name = "sensor[price]";
		$form->parameters['price-value']->value = $this->parameters['price'];
		$form->parameters['price-value']->label = "€/kwH";

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

		if (isset($data['archived'])) {
			$this->archived = $data['archived'];
		} else {
			$this->archived = 0;
		}

		switch ($this->type) {
			case "electricity":
				$this->from_form_parameters_electricity($data);
			default:
				break;
		}
	}

	function from_form_parameters_electricity($data) {
		$this->parameters = $this->parameters();

		if (isset($data['price'])) {
			$this->parameters['price'] = $data['price'];
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
			'comment' => $db->escape($this->comment),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'archived' => (int)$this->archived,
			'created' => time(),
			'updated' => time(),
		];

		$query = 'INSERT INTO sensors (' . implode(',', array_keys($fields)) . ') '.
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
			'comment' => $db->escape($this->comment),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'archived' => (int)$this->archived,
			'updated' => time(),
		];

		$query = 'UPDATE sensors SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM sensors WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function check_electricity_data_consistency($value, $timestamp) {
		// value is in kWh

		$new_data = (float)$value;
		$previous_data = $this->data_at($timestamp - 1);

		if ($previous_data == $new_data) {
			return true;
		} else if (!$previous_data) {
			return true;
		} else if (!empty($previous_data['value'])) {
			$difference = $new_data - $previous_data['value'];
			$span_h = ($timestamp - $previous_data['timestamp']) / 3600;

			if ($difference < 0) {
				// let's keep negative moves for now since anomalously high
				// wrong values have to get corrected at one point

				// no, let's not keep them after all
				return false;
			}

			$kw = $difference/$span_h;

			if ($kw > 9) {
				// more than 9 kW over any period is probably wrong
				return false;
			}
		}
		
		return true;
	}

	function check_temperature_data_consistency($value, $timestamp) {
		// the most frequent problem is getting a 0 when temperature is
		// actually not exactly 0°C

		if ($this->value != 0) {
			return true;
		}

		$new_data = (float)$value;
		$previous_data = $this->data_at($timestamp - 1);

		if (!$previous_data) {
			return true;
		}

		// if there have been more than 12 hours since the last value,
		// we can't assume much
		if ($timestamp - $previous_data['timestamp'] > 3600 * 12) {
			return true;
		}

		// we will allow 0°C values only when the previous recorded
		// values were between -4°C and +4°C, a range within which
		// a spurious 0°C value doesn't matter that much anyway.
		if ($previous_data['value'] < 4 or $previous_data['value'] > -4) {
			return true;
		}

		return false;
	}

	function check_data_consistency($value, $timestamp) {
		switch ($this->type) {
			case 'electricity':
				return $this->check_electricity_data_consistency($value, $timestamp);
			case 'temperature':
				return $this->check_temperature_data_consistency($value, $timestamp);
			default:
				return true;
		}
	}

	function record_data($value, $timestamp, $battery = null) {
		$db = new DB();

		$raw = $value;

		if (!$this->check_data_consistency($value, $timestamp)) {
			return 0;
		}

		$fields = [
			'sensor_id' => (int)$this->id,
			'place_id' => (int)$this->place_id,
			'value' => (float)$value,
			'raw' => (float)$raw,
			'timestamp' => $timestamp,
			'battery' => $battery === null ? 'NULL' : (float)$battery,
		];

		$query = 'INSERT OR IGNORE INTO sensors_data (' . implode(',', array_keys($fields)) . ') '.
		                                     'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		return $db->insert_id();
	}

	function data_at($timestamp) {
		$db = new DB();

		$query = 'SELECT value, raw, battery, timestamp '.
		           'FROM sensors_data '.
				  'WHERE timestamp <= '.(int)$timestamp.' '.
				    'AND sensor_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp DESC '.
				  'LIMIT 1';

		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			return $record;
		}
	}

	function data_after($timestamp) {
		$db = new DB();

		$query = 'SELECT value, raw, battery, timestamp '.
				   'FROM sensors_data '.
				  'WHERE timestamp > '.(int)$timestamp.' '.
					'AND sensor_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp ASC '.
				  'LIMIT 1';

		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			return $record;
		}
	}

	function data_between($start, $stop, $interval = 0) {
		$db = new DB();

		$query = 'SELECT value, timestamp '.
		           'FROM sensors_data '.
				  'WHERE timestamp BETWEEN '.(int)$start.' AND '.(int)$stop.' '.
				    'AND sensor_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp ASC';

		$data = [];
		$result = $db->query($query);
		$tare = 0;
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			$value = (float)$record['value'];

			if ($this->type == "weight") {
				if ($value < 3) {
					$tare = $value;
				}

				$value -= $tare;
			}

			$data[(int)$record['timestamp']] = $value;
		}

		if ($interval) {
			$data = $this->group_data($data, $interval);
		}

		return $data;
	}

	function data_monthly_between($start, $stop) {
		$data = [];

		if ($start === 0) {
			$first_data = $this->data_after(0);

			if (!$first_data) {
				return [];
			}

			$start = max($first_data['timestamp'], $start);
		}

		$start = strtotime("first day of this month", $start);
		$stop = strtotime("last day of this month", $stop);

		for ($day = $start; $day < $stop; $day = strtotime("first day of next month", $day)) {
			$period_start = $day;
			$period_stop = strtotime("first day of next month", $day);

			$value_start = $this->interpolated_value_at($period_start);
			$value_stop = $this->interpolated_value_at($period_stop);

			$value = $value_stop - $value_start;

			$data[$day] = $value;
		}

		return $data;
	}

	function group_data($data, $interval) {
		$grouped_data = [];

		foreach ($data as $timestamp => $value) {
			$grouped_timestamp = floor($timestamp/$interval) * $interval;

			if (!isset($grouped_data[$grouped_timestamp])) {
				$grouped_data[$grouped_timestamp] = [];
			}

			$grouped_data[$grouped_timestamp][] = $value;
		}

		foreach ($grouped_data as $timestamp => $values) {
			$grouped_data[$timestamp] = Math::mean($values, 15);
		}

		return $grouped_data;
	}

	function unit() {
		switch ($this->type) {
			case 'temperature':
				return '°C';
			case 'humidity':
				return '%';
			case 'brightness':
				return 'lux';
			case 'rx-power':
				return 'dBm';
			case 'weight':
				return 'Kg';
			case 'electricity':
				return 'kWh';
			case 'gas':
				return 'ppm';
			default:
				return '';
		}
	}

	function value_at($timestamp) {
		$data = $this->data_at($timestamp);
		if ($data) {
			return $data['value'];
		}

		return null;
	}

	function interpolated_value_at($timestamp) {
		$data_before = $this->data_at($timestamp - 1);
		$data_after = $this->data_after($timestamp + 1);
		if ($data_before and $data_after) {
			$value = $data_before['value'] + ($timestamp - $data_before['timestamp']) * ($data_after['value'] - $data_before['value']) / ($data_after['timestamp'] - $data_before['timestamp']);
			return $value;
		} else if ($data_before) {
			return $data_before['value'];
		} else if ($data_after) {
			return $data_after['value'];
		}

		return null;
	}

	function value_text() {
		$text = "";

		$data = $this->data_at(time());
		if ($data) {
			$text = $data['value'];

			if ($this->type != "temperature" and $this->type != "humidity") {
				$text .= " ";
			}
			
			$text .= $this->unit();
		}

		return $text;
	}

	function battery_text() {
		$text = "";

		$data = $this->data_at(time());
		if ($data and $data['battery'] != "") {
			$text = $data['battery']."%";
		}

		return $text;
	}

	function last_updated() {
		$data = $this->data_at(time());
		if ($data) {
			return $data['timestamp'];
		}

		return 0;
	}

	function chart() {
		return $this->chart_line("1-week").$this->chart_minmax("all");
	}

	function chart_line($period = "1-week") {
		$chart = new Chart();
		$chart->id = "sensor-{$this->id}-line";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = $period;
		$chart->type = "line";
		$chart->parameters = [
			'sensors' => [
				$this->id => [
					'id' => $this->id,
					'color' => '#2F2F2F',
				],
			]
		];

		return $chart->html();
	}

	function chart_minmax($period = "all") {
		$chart = new Chart();
		$chart->id = "sensor-{$this->id}-minmax";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = $period;
		$chart->type = "min-max";
		$chart->parameters = [
			'sensors' => [
				$this->id => [
					'id' => $this->id,
					'color' => '#2F2F2F',
				],
			]
		];

		return $chart->html();
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
}
