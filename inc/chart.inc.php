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

	function form_line($form) {
		return $this->form_add_parameters_sensors($form);
	}

	function form_add_parameters_sensors($form) {
		$sensors = Sensor::select();

		$form->parameters['sensors'] = [
			'label' => __("Sensors to display"),
			'value' => "",
		];

		foreach ($sensors as $sensor) {
			$form->parameters['sensor-'.$sensor->id] = new HTML_Input("chart-sensor-".$sensor->id);
			$form->parameters['sensor-'.$sensor->id]->type = "checkbox";
			$form->parameters['sensor-'.$sensor->id]->name = "chart[sensors][{$sensor->id}][enabled]";
			$form->parameters['sensor-'.$sensor->id]->value = 1;
			$form->parameters['sensor-'.$sensor->id]->label = $sensor->name;
			if (isset($this->parameters['sensors'][$sensor->id])) {
				$form->parameters['sensor-'.$sensor->id]->attributes['checked'] = "checked";
			}

			$input_color = new HTML_Input_Color("chart-sensor-{$sensor->id}-color");
			$input_color->name = "chart[sensors][{$sensor->id}][color]";
			$input_color->value = $this->parameters['sensors'][$sensor->id]['color'];

			$form->parameters['sensor-'.$sensor->id]->suffix = $input_color->element();

			$form->parameters['sensor-'.$sensor->id]->suffix .= " ({$sensor->place()->name} · {$sensor->value_text()})";
		}

		return $form;
	}

	function form() {
		$form = $this->form_base();

		switch ($this->type) {
			case "min-max":
				$form = $this->form_min_max($form);
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

	function from_form_line($data) {
		$this->from_form_parameters_sensors($data);
	}

	function from_form_parameters_sensors($data) {
		$this->parameters = $this->parameters();

		if (isset($data['sensors']) and is_array($data['sensors'])) {
			$this->parameters['sensors'] = [];

			foreach ($data['sensors'] as $id => $sensor) {
				if (isset($sensor['enabled']) and $sensor['enabled']) {

					$this->parameters['sensors'][$id] = [
						'id' => $id,
						'color' => $sensor['color'],
					];
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
			case '1-day':
				return strtotime('today midnight');
			case '1-week':
				return strtotime('-7 days midnight');
			case '2-weeks':
				return strtotime('-15 days midnight');
			case '1-month':
				return strtotime('-31 days midnight');
			case 'all':
				return 0;
		}
	}

	function sensors_data() {
		$data = [];

		$sensors = Sensor::select(['id' => array_map(function($sensor) { return $sensor['id']; }, $this->parameters['sensors'])]);

		$start = $this->data_start();
		$stop = time();

		foreach ($sensors as $sensor) {
			$data[$sensor->id] = [
				'label' => $sensor->name,
				'type' => $sensor->type,
				'unit' => $sensor->unit(),
				'place' => $sensor->place()->name,
				'color' => $this->parameters['sensors'][$sensor->id]['color'],
				'values' => $sensor->data_between($start, $stop),
			];
		}

		return $data;
	}

	function html() {
		$old_serialize_precision = ini_get('serialize_precision');
		ini_set('serialize_precision', 8);
		$data_json = json_encode($this->sensors_data());
		ini_set('serialize_precision', $old_serialize_precision);

		$html = "";

		switch ($this->type) {
			case "min-max":
				$html .= $this->html_min_max($data_json);
				break;
			case "line":
				$html .= $this->html_line($data_json);
				break;
			default:
				break;
		}

		return $html;
	}

	function html_min_max($data_json) {
		switch($this->size) {
			case 'small':
				$height = 350;
				break;
			case 'medium':
				$height = 450;
				break;
			case 'large':
				$height = 600;
				break;
		}

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_min_max_display('chart-{$this->id}', '{$this->title}', {$data_json});
	</script>
HTML;

		return $html;
	}

	function html_line($data_json) {
		switch($this->size) {
			case 'small':
				$height = 350;
				break;
			case 'medium':
				$height = 450;
				break;
			case 'large':
				$height = 600;
				break;
		}

		$html = <<<HTML
    <svg id='chart-{$this->id}' class="dashboard-element chart size-{$this->size}"></svg>
	<script>
		chart_line_display('chart-{$this->id}', '{$this->title}', {$data_json});
	</script>
HTML;

		return $html;
	}
}
