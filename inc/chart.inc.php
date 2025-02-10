<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Chart extends Record {
	public static $table = "charts";

	public $id = 0;
	public $title = "";
	public $size = "";
	public $period = "";
	public $type = "";
	public $parameters = "";
	public $updated = 0;
	public $inserted = 0;

	static function grid_row_header_admin() {
		return [
			'name' => __('Title'),
			'type' => __('Type'),
			'period' => __('Period'),
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			return [
				'title' => "<a href='{$GLOBALS['config']['base_path']}/admin/chart/{$this->id}'>{$this->title}</a>",
				'type' => _a('chart-types', $this->type),
				'period' => _a('chart-periods', $this->period),
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/chart/{$this->id}'>".__("Add a new chart")."</a>",
					'attributes' => ['colspan' => 3],
				],
			];
		}
	}

	function form_base() {
		$form = new HTML_Form();
		$form->attributes['class'] = 'dl-form';

		$form->fields['id'] = new HTML_Input("chart-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "chart[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['title'] = new HTML_Input("chart-title");
		$form->fields['title']->type = "text";
		$form->fields['title']->name = "chart[title]";
		$form->fields['title']->value = $this->title;
		$form->fields['title']->label = __("Title");

		$form->fields['type'] = new HTML_Select("chart-type");
		$form->fields['type']->name = "chart[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = _a('chart-types');
		$form->fields['type']->label = __("Type");

		$form->fields['period'] = new HTML_Select("chart-period");
		$form->fields['period']->name = "chart[period]";
		$form->fields['period']->value = $this->period;
		$form->fields['period']->options = _a('chart-periods');
		$form->fields['period']->label = __("Period");

		$form->fields['size'] = new HTML_Select("chart-size");
		$form->fields['size']->name = "chart[size]";
		$form->fields['size']->value = $this->size;
		$form->fields['size']->options = _a('dashboard-sizes');
		$form->fields['size']->label = __("Size");

		$form->actions['save'] = new HTML_Button("chart-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("chart-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to delete this chart?");

		return $form;
	}

	function form_min_max($form) {
		return $this->form_add_parameters_sensors($form);
	}

	function form_daily($form) {
		return $this->form_add_parameters_sensors($form);
	}

	function form_monthly($form) {
		return $this->form_add_parameters_sensors($form);
	}

	function form_line($form) {
		return $this->form_add_parameters_sensors($form);
	}

	function form_add_parameters_sensors($form) {
		$sensors = Sensor::select(['sensors.archived' => 0]);

		$form->parameters['sensors'] = [
			'label' => __("Sensors to display"),
			'value' => "",
		];

		foreach ($this->parameters['sensors'] as $sensor_id => $sensor_params) {
			if (!isset($sensors[$sensor_id])) {
				$sensors[$sensor_id] = Sensor::load(['id' => $sensor_id]);
			}
		}

		foreach ($sensors as $sensor) {
			foreach ($sensor->dimensions() as $dimension => $label) {
				$sensor_dimension = new Sensor_Dimension();
				$sensor_dimension->dimension = $dimension;
				$sensor_dimension->load(['id' => $sensor->id]);

				$form->parameters['sensor-'.$sensor->id.'-'.$dimension] = new HTML_Input("chart-sensor-{$sensor->id}-{$dimension}");
				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->type = "checkbox";
				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->name = "chart[sensors][{$sensor->id}][{$dimension}][enabled]";
				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->value = 1;
				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->label = $sensor_dimension->label();

				$input_color = new HTML_Input_Color("chart-sensor-{$sensor->id}-{$dimension}-color");
				$input_color->name = "chart[sensors][{$sensor->id}][$dimension][color]";
				$input_color->value = $this->parameters['sensors'][$sensor->id][$dimension]['color'];

				if (isset($this->parameters['sensors'][$sensor->id][$dimension])) {
					$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->attributes['checked'] = "checked";
				} else if ($dimension == 'value' and isset($this->parameters['sensors'][$sensor->id])) {
					$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->attributes['checked'] = "checked";

					$input_color = new HTML_Input_Color("chart-sensor-{$sensor->id}-{$dimension}-color");
					$input_color->name = "chart[sensors][{$sensor->id}][$dimension][color]";
					$input_color->value = $this->parameters['sensors'][$sensor->id]['color'];
				}

				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->suffix = $input_color->element();

				$form->parameters['sensor-'.$sensor->id.'-'.$dimension]->suffix .= " ({$sensor->place()->name} · {$sensor->value_text()})";
			}
		}

		return $form;
	}

	function form() {
		$form = $this->form_base();

		switch ($this->type) {
			case "min-max":
				$form = $this->form_min_max($form);
				break;
			case "daily":
				$form = $this->form_daily($form);
				break;
			case "monthly":
				$form = $this->form_monthly($form);
				break;
			case "line":
				$form = $this->form_line($form);
				break;
			default:
				break;
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

		if (isset($data['title'])) {
			$this->title = $data['title'];
		}

		if (isset($data['type'])) {
			$this->type = $data['type'];
		}

		if (isset($data['period'])) {
			$this->period = $data['period'];
		}

		if (isset($data['size'])) {
			$this->size = $data['size'];
		}

		switch ($this->type) {
			case "min-max":
				$this->from_form_min_max($data);
				break;
			case "daily":
				$this->from_form_daily($data);
				break;
			case "monthly":
				$this->from_form_monthly($data);
				break;
			case "line":
				$this->from_form_line($data);
				break;
			default:
				break;
		}
	}

	function from_form_min_max($data) {
		$this->from_form_parameters_sensors($data);
	}

	function from_form_daily($data) {
		$this->from_form_parameters_sensors($data);
	}

	function from_form_monthly($data) {
		$this->from_form_parameters_sensors($data);
	}

	function from_form_line($data) {
		$this->from_form_parameters_sensors($data);
	}

	function from_form_parameters_sensors($data) {
		$this->parameters = $this->parameters();

		if (isset($data['sensors']) and is_array($data['sensors'])) {
			$this->parameters['sensors'] = [];

			foreach ($data['sensors'] as $id => $sensor) {
				foreach ($sensor as $dimension => $parameters) {
					if (isset($parameters['enabled']) and $parameters['enabled']) {

						$this->parameters['sensors'][$id][$dimension] = [
							'id' => $id,
							'color' => $parameters['color'],
						];
					}
				}
			}
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

	protected function load_from_result($main_table, $aliases, $db_record, $db_fields) {
		parent::load_from_result($main_table, $aliases, $db_record, $db_fields);
		$this->parameters = $this->parameters();
	}

	function save() {
		return $this->id > 0 ? $this->update() : $this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'title' => $db->escape($this->title),
			'type' => $db->escape($this->type),
			'period' => $db->escape($this->period),
			'size' => $db->escape($this->size),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'updated' => time(),
			'inserted' => time(),
		];

		$query = 'INSERT INTO charts (' . implode(',', array_keys($fields)) . ') '.
		                     'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function update() {
		$db = new DB();

		$fields = [
			'title' => $db->escape($this->title),
			'type' => $db->escape($this->type),
			'period' => $db->escape($this->period),
			'size' => $db->escape($this->size),
			'parameters' => $db->escape(json_encode($this->parameters())),
			'updated' => time(),
		];

		$query = 'UPDATE charts SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM charts WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function data_start() {
		switch ($this->period) {
			case '1-hour':
			case '1 hour':
				return strtotime('-1 hour');
			case '1-day':
			case '1 day':
				return strtotime('today midnight - 12 hours');
			case '1-week':
			case '1 week':
				return strtotime('-7 days midnight');
			case '2-weeks':
			case '2 weeks':
				return strtotime('-15 days midnight');
			case '1-month':
			case '1 month':
				return strtotime('-31 days midnight');
			case '2-months':
			case '2 months':
				return strtotime('-60 days midnight');
			case 'all':
				return 0;
		}
	}

	function sensors_data($interval = 0) {
		$data = [];

		$start = $this->data_start();
		$stop = time();

		foreach ($this->parameters['sensors'] as $sensor_id => $dimensions) {
			$sensor = new Sensor();
			$sensor->load(['id' => $sensor_id]);

			foreach ($dimensions as $dimension => $parameters) {
				$data[$sensor->id.'-'.$dimension] = [
					'label' => $parameters['label'] ?? $sensor->name,
					'axis-label' => $parameters['axis-label'] ?? $sensor->axis_label(),
					'type' => $sensor->type,
					'unit' => $parameters['unit'] ?? $sensor->unit(),
					'place' => $sensor->place()->name,
					'color' => $parameters['color'],
					'values' => $sensor->data_between($start, $stop, $interval, null, $dimension == "battery" ? "battery" : null),
				];
			}
		}

		return $data;
	}

	function sensors_data_monthly() {
		$data = [];

		$sensors = Sensor::select(['id' => array_map(function($sensor) { return $sensor['id']; }, $this->parameters['sensors'])]);

		$start = $this->data_start();
		$stop = time();

		foreach ($this->parameters['sensors'] as $sensor_id => $dimensions) {
			foreach ($dimensions as $dimension => $parameters) {
				$sensor = new Sensor_Dimension();
				$sensor->dimension = $dimension;
				$sensor->load(['id' => $sensor_id]);

				$data[$sensor->id.'-'.$dimension] = [
					'label' => $sensor->label(),
					'axis-label' => $sensor->axis_label(),
					'type' => $sensor->type.'-'.$dimension,
					'unit' => $sensor->unit(),
					'place' => $sensor->place()->name,
					'color' => $parameters['color'],
					'values' => $sensor->data_monthly_between($start, $stop),
				];
			}
		}

		return $data;
	}

	function sensors_data_daily() {
		$data = [];

		$sensors = Sensor::select(['id' => array_map(function($sensor) { return $sensor['id']; }, $this->parameters['sensors'])]);

		$start = $this->data_start();
		$stop = time();

		foreach ($this->parameters['sensors'] as $sensor_id => $dimensions) {
			foreach ($dimensions as $dimension => $parameters) {
				$sensor = new Sensor_Dimension();
				$sensor->dimension = $dimension;
				$sensor->load(['id' => $sensor_id]);

				$data[$sensor->id.'-'.$dimension] = [
					'label' => $sensor->label(),
					'axis-label' => $sensor->axis_label(),
					'type' => $sensor->type.'-'.$dimension,
					'unit' => $sensor->unit(),
					'place' => $sensor->place()->name,
					'color' => $parameters['color'],
					'values' => $sensor->data_between($start, $stop, 60 * 5, function($values) { return array_sum($values); }),
				];
			}
		}

		return $data;
	}

	function devices_data() {
		$data = [];

		$devices = Device::select(['id' => array_map(function($device) { return $device['id']; }, $this->parameters['devices'])]);

		$start = $this->data_start();
		$stop = time();

		foreach ($devices as $device) {
			$data[$device->id] = [
				'label' => $device->name,
				'axis-label' => _a('device-types', $device->type),
				'type' => _a('device-types', $device->type),
				'place' => $device->place()->name,
				'color' => $this->parameters['devices'][$device->id]['color'],
				'values' => array_map(function($state) {
					switch ($state) {
						case 'on': return 1;
						case 'off' : return 0;
						default: return (float)$state;
					}
				}, $device->state_between($start, $stop)),
			];
		}

		return $data;
	}

	function events_data() {
		return ['events' => $this->parameters['events']];
	}

	function data($interval = 0) {
		$data = [];
		if (isset($this->parameters['sensors'])) {
			$data += $this->sensors_data($interval);
		} else {
			$data += $this->devices_data();
		}

		if (isset($this->parameters['events'])) {
			$data += $this->events_data();
		}

		return $data;
	}

	function data_monthly() {
		if (isset($this->parameters['sensors'])) {
			return $this->sensors_data_monthly();
		} else {
			return [];
		}
	}

	function data_daily() {
		if (isset($this->parameters['sensors'])) {
			return $this->sensors_data_daily();
		} else {
			return [];
		}
	}

	function html() {
		$html = "";

		switch ($this->type) {
			case "min-max":
				$data = $this->data();
				$html .= $this->html_min_max($data);
				break;
			case "daily":
				$data = $this->data(3600 * 24);
				$html .= $this->html_daily($data);
				break;
			case "monthly":
				$data = $this->data_monthly();
				$html .= $this->html_monthly($data);
				break;
			case "line":
				$data = $this->data();
				$html .= $this->html_line($data);
				break;
			case "histogram":
				$data = $this->data_daily();
				$html .= $this->html_histogram($data);
				break;
			default:
				break;
		}

		return $html;
	}

	function html_min_max($data) {
		$data_json = JSON::encode($data);

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_min_max_display('chart-{$this->id}', '{$this->title}', {$data_json});
	</script>
HTML;

		return $html;
	}

	function html_daily($data) {
		$data_json = JSON::encode($data);

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_line_display('chart-{$this->id}', '{$this->title}', {$data_json}, false);
	</script>
HTML;

		return $html;
	}

	function html_monthly($data) {
		$data_json = JSON::encode($data);

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_histogram_display('chart-{$this->id}', '{$this->title}', {$data_json}, false);
	</script>
HTML;

		return $html;
	}

	function html_line($data) {
		$data_json = JSON::encode($data);

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_line_display('chart-{$this->id}', '{$this->title}', {$data_json});
	</script>
HTML;

		return $html;
	}

	function html_histogram($data) {
		$data_json = JSON::encode($data);

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_histogram_display('chart-{$this->id}', '{$this->title}', {$data_json});
	</script>
HTML;

		return $html;
	}
}
