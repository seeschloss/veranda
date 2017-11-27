<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Sensor extends Record {
	public static $table = "sensors";

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
			'value' => 'Value',
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
				'name' => "<a href='/admin/sensor/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
				'value' => $this->value_text(),
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
					'value' => "<a href='/admin/sensor/{$this->id}'>{$this->name}</a>",
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
			'value' => 'Value',
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
				'name' => "<a href='/sensor/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
				'value' => $this->value_text(),
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
					'value' => "<a href='/sensor/{$this->id}'>{$this->name}</a>",
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

		$form->fields['id'] = new HTML_Input("sensor-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "sensor[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['place_id'] = new HTML_Select("sensor-place-id");
		$form->fields['place_id']->name = "sensor[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = array_map(function($place) { return $place->name; }, Place::select());
		$form->fields['place_id']->label = "Place";

		$form->fields['name'] = new HTML_Input("sensor-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "sensor[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = "Name";

		$form->fields['type'] = new HTML_Select("sensor-type");
		$form->fields['type']->name = "sensor[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = [
			'temperature' => "Temperature",
			'humidity' => "Humidity",
			'brightness' => "Brightness",
		];
		$form->fields['type']->label = "Type";

		$form->fields['comment'] = new HTML_Textarea("sensor-comment");
		$form->fields['comment']->name = "sensor[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = "Comment";

		$form->actions['save'] = new HTML_Button("sensor-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = "Save";

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("sensor-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = "Delete";
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = "Are you sure you want to delete this sensor?";

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

		if (isset($data['name'])) {
			$this->name = $data['name'];
		}

		if (isset($data['type'])) {
			$this->type = $data['type'];
		}

		if (isset($data['comment'])) {
			$this->comment = $data['comment'];
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

	function record_data($value, $timestamp) {
		$db = new DB();

		$fields = [
			'sensor_id' => (int)$this->id,
			'place_id' => (int)$this->place_id,
			'value' => (float)$value,
			'timestamp' => $timestamp,
		];

		$query = 'INSERT INTO sensors_data (' . implode(',', array_keys($fields)) . ') '.
		                           'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		return $db->insert_id();
	}

	function data_at($timestamp) {
		$db = new DB();

		$query = 'SELECT value, timestamp '.
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

	function data_between($start, $stop) {
		$db = new DB();

		$query = 'SELECT value, timestamp '.
		           'FROM sensors_data '.
				  'WHERE timestamp BETWEEN '.(int)$start.' AND '.(int)$stop.' '.
				    'AND sensor_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp DESC';

		$data = [];
		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			$data[(int)$record['timestamp']] = (float)$record['value'];
		}

		return $data;
	}

	function unit() {
		switch ($this->type) {
			case 'temperature':
				return '°C';
			case 'humidity':
				return '%';
			case 'brightness':
				return 'lux';
			default:
				return '';
		}
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

	function last_updated() {
		$data = $this->data_at(time());
		if ($data) {
			return $data['timestamp'];
		}

		return 0;
	}

	function chart() {
		$chart = new Chart();
		$chart->id = "sensor-{$this->id}-line";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = "1-week";
		$chart->type = "line";
		$chart->parameters = [
			'sensors' => [
				$this->id => [
					'id' => $this->id,
					'color' => '#2F2F2F',
				],
			]
		];

		$html = $chart->html();

		$chart = new Chart();
		$chart->id = "sensor-{$this->id}-minmax";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = "all";
		$chart->type = "min-max";
		$chart->parameters = [
			'sensors' => [
				$this->id => [
					'id' => $this->id,
					'color' => '#2F2F2F',
				],
			]
		];

		$html .= $chart->html();

		return $html;
	}
}
