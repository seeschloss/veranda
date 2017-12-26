<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Alert extends Record {
	public static $table = "alerts";
	public static $relations = ['sensor_id' => 'Sensor'];

	public $id = 0;
	public $name = "";
	public $type = "";
	public $dest = "";
	public $sensor_id = 0;
	public $min = null;
	public $max = null;
	public $created = 0;
	public $updated = 0;

	static function grid_row_header_admin() {
		return [
			'name' => __('Name'),
			'sensor' => __('Sensor'),
			'conditions' => __('Conditions'),
			'type' => __('Type'),
			'dest' => __('To'),
			'status' => __('Status'),
			'last-alert' => __('Last alert'),
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			$status_class = "";

			if ($last_alert = $this->last_alert()) {
				switch ($this->status()) {
					case "":
						$status_class = "inactive";
						break;
					case "ok":
						$status_class = "";
						break;
					default:
						$status_class = "alert";
						break;
				}

				$seconds = time() - $last_alert;
				$last_update = new DateTime();
				$last_update->setTimestamp($last_alert);

				$lag = $last_update->diff(new DateTime());

				$last_updated_string = "";
				if ($lag->m > 0) {
					$last_updated_string = $last_update->format("Y-m-d");
				} else if ($lag->d > 0) {
					$last_updated_string = $lag->format("%dd, %hh, %im, %ss");
				} else if ($lag->h > 0) {
					$last_updated_string = $lag->format("%hh, %im, %ss");
				} else if ($lag->i > 0) {
					$last_updated_string = $lag->format("%im, %ss");
				} else if ($lag->s > 0) {
					$last_updated_string = $lag->format("%ss");
				}
			} else {
				$last_updated_string = __("never");
			}

			$conditions = [];
			if ($this->min !== null) {
				$conditions[] = "&lt; {$this->min}{$this->sensor->unit()}";
			}

			if ($this->max !== null) {
				$conditions[] = "&gt; {$this->max}{$this->sensor->unit()}";
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/admin/alert/{$this->id}'>{$this->name}</a>",
				'sensor' => "<a href='{$GLOBALS['config']['base_path']}/admin/sensor/{$this->sensor_id}'>{$this->sensor->name}</a>",
				'conditions' => join(' ', $conditions),
				'type' => _a('alert-types', $this->type),
				'dest' => $this->dest,
				'status' => [
					'value' => $this->status_text(),
					'attributes' => [
						'class' => $status_class,
					],
				],
				'last-alert' => [
					'value' => $last_updated_string,
				],
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/alert/{$this->id}'>".__('Add a new alert')."</a>",
					'attributes' => ['colspan' => 7],
				],
			];
		}
	}

	function form() {
		$form = new HTML_Form();
		$form->attributes['class'] = "dl-form";

		if ($this->sensor_id) {
			$sensor = new Sensor();
			$sensor->load(['id' => $this->sensor_id]);
			$unit = $sensor->unit();
		} else {
			$unit = "";
		}

		$form->fields['id'] = new HTML_Input("alert-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "alert[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['name'] = new HTML_Input("alert-name");
		$form->fields['name']->name = "alert[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = __("Name");

		$form->fields['sensor_id'] = new HTML_Select("alert-sensor-id");
		$form->fields['sensor_id']->name = "alert[sensor_id]";
		$form->fields['sensor_id']->value = $this->sensor_id;
		$form->fields['sensor_id']->options = array_map(function($sensor) { return $sensor->name; }, Sensor::select());
		$form->fields['sensor_id']->label = __("Sensor");

		$form->fields['type'] = new HTML_Select("alert-type");
		$form->fields['type']->name = "alert[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = _a('alert-types');
		$form->fields['type']->label = __("Type");

		$form->fields['dest'] = new HTML_Input("alert-dest");
		$form->fields['dest']->name = "alert[dest]";
		$form->fields['dest']->value = $this->dest;
		$form->fields['dest']->label = __("Recipient");

		$form->fields['min'] = new HTML_Input("alert-min");
		$form->fields['min']->name = "alert[min]";
		$form->fields['min']->type = "number";
		$form->fields['min']->value = $this->min;
		$form->fields['min']->label = __("Minimum");
		if ($unit) {
			$form->fields['min']->suffix = $unit;
		}

		$form->fields['max'] = new HTML_Input("alert-max");
		$form->fields['max']->name = "alert[max]";
		$form->fields['max']->type = "number";
		$form->fields['max']->value = $this->max;
		$form->fields['max']->label = __("Maximum");
		if ($unit) {
			$form->fields['max']->suffix = $unit;
		}

		$form->fields['timeout'] = new HTML_Input("alert-timeout");
		$form->fields['timeout']->name = "alert[timeout]";
		$form->fields['timeout']->type = "number";
		$form->fields['timeout']->value = $this->timeout;
		$form->fields['timeout']->label = __("Timeout");
		$form->fields['timeout']->suffix = "minutes";

		$form->actions['save'] = new HTML_Button("alert-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("alert-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to delete this alert?");

		return $form->html();
	}

	function from_form($data) {
		if (isset($data['id'])) {
			$this->id = (int)$data['id'];

			if ($this->id) {
				$this->load(['id' => $this->id]);
			}
		}

		if (isset($data['name'])) {
			$this->name = $data['name'];
		}

		if (isset($data['sensor_id'])) {
			$this->sensor_id = $data['sensor_id'];
		}

		if (isset($data['type'])) {
			$this->type = $data['type'];
		}

		if (isset($data['dest'])) {
			$this->dest = $data['dest'];
		}

		if (isset($data['min'])) {
			$this->min = $data['min'] === "" ? null : $data['min'];
		}

		if (isset($data['max'])) {
			$this->max = $data['max'] === "" ? null : $data['max'];
		}

		if (isset($data['timeout'])) {
			$this->timeout = $data['timeout'] === "" ? null : $data['timeout'];
		}
	}

	function save() {
		return $this->id > 0 ? $this->update() : $this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'name' => $db->escape($this->name),
			'type' => $db->escape($this->type),
			'dest' => $db->escape($this->dest),
			'sensor_id' => (int)$this->sensor_id,
			'min' => $this->min === null ? 'NULL' : (int)$this->min,
			'max' => $this->max === null ? 'NULL' : (int)$this->max,
			'created' => time(),
			'updated' => time(),
		];

		$query = 'INSERT INTO alerts (' . implode(',', array_keys($fields)) . ') '.
		                     'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function update() {
		$db = new DB();

		$fields = [
			'name' => $db->escape($this->name),
			'type' => $db->escape($this->type),
			'dest' => $db->escape($this->dest),
			'sensor_id' => (int)$this->sensor_id,
			'min' => $this->min === null ? 'NULL' : (int)$this->min,
			'max' => $this->max === null ? 'NULL' : (int)$this->max,
			'updated' => time(),
		];

		$query = 'UPDATE alerts SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM alerts WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function status() {
		if (!$this->sensor_id) {
			return "";
		}

		$status = "ok";

		$sensor_value = $this->sensor->value_at(time());

		if ($this->timeout !== null and time() - $this->timeout < $this->sensor->last_updated()) {
			$status = "timeout";
		} else if ($this->min !== null and $this->min > $sensor_value) {
			$status = "low";
		} else if ($this->max !== null and $this->max < $sensor_value) {
			$status = "high";
		}

		return $status;
	}

	function status_text() {
		if (!$this->sensor_id) {
			return "";
		}

		return "{$this->status()} ({$this->sensor->value_text()})";
	}

	function status_at($timestamp) {
		$db = new DB();

		$query = 'SELECT status, timestamp, value '.
		           'FROM alert_events '.
				  'WHERE timestamp <= '.(int)$timestamp.' '.
				    'AND alert_id = '.(int)$this->id.' '.
				  'ORDER BY timestamp DESC '.
				  'LIMIT 1';

		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			return $record['status'];
		}

		return '';
	}

	function update_status() {
		$db = new DB();

		$fields = [
			'alert_id' => (int)$this->id,
			'status' => $db->escape($this->status()),
			'value' => (float)$this->sensor->value_at(time()),
			'timestamp' => time(),
		];

		$query = 'INSERT INTO alert_events (' . implode(',', array_keys($fields)) . ') '.
		                           'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		return $db->insert_id();
	}

	function last_alert() {
		$db = new DB();

		$query = 'SELECT status, timestamp, value '.
		           'FROM alert_events '.
				  'WHERE timestamp <= '.(int)time().' '.
				    'AND alert_id = '.(int)$this->id.' '.
				    'AND status <> \'ok\' '.
				  'ORDER BY timestamp DESC '.
				  'LIMIT 1';

		$result = $db->query($query);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			return $record['timestamp'];
		}

		return '';

		return 0;
	}

	function check() {
		$previous_status = $this->status_at(time());
		$current_status = $this->status();

		if ($current_status != "ok" and $current_status != $previous_status) {
			$this->send();
		}

		if ($current_status != $previous_status) {
			$this->update_status();
		}
	}

	function send() {
		switch ($this->type) {
			case 'email':
				return $this->send_mail();
			case 'sms':
				return $this->send_sms();
			default:
				return true;
		}
	}

	function send_mail() {
		debug::dump($this->status());
		if ($this->dest) {
			$status = $this->status();
			$value = $this->sensor->value_text();
			$subject = __('Veranda alert: %s - %s - %s', $this->name, $status, $value);
			$message = __('Alert on sensor "%s": %s', $this->sensor->name, $status)."\r\n\r\n";
			$message .= __('Last value: %s at %s', $value, date('r', $this->sensor->last_updated()));

			$headers = "From: veranda@seos.fr";

			return mail($this->dest, $subject, $message, $headers);
		}
	}

	function send_sms() {
		debug::dump($this->status());
	}
}
