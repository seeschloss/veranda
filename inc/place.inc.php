<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Place extends Record {
	public static $table = "places";

	public $id = 0;
	public $name = "";
	public $type = "";
	public $public = true;
	public $comment = "";
	public $mask = null;
	public $created = 0;
	public $updated = 0;

	static function grid_row_header_admin() {
		return [
			'name' => __('Name'),
			'type' => __('Type'),
			'condition' => __('Condition'),
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			$conditions = [];

			$sensors = Sensor::select(['place_id' => $this->id]);
			foreach ($sensors as $sensor) {
				$conditions[] = $sensor->value_text();
			}

			return [
				'name' => "<a href='{$GLOBALS['config']['base_path']}/admin/place/{$this->id}'>{$this->name}</a>",
				'type' => _a('place-types', $this->type),
				'condition' => join(' ', $conditions),
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/place/{$this->id}'>".__('Add a new place')."</a>",
					'attributes' => [
						'colspan' => 3,
					],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => __('Name'),
			'type' => __('Type'),
			'condition' => __('Condition'),
		];
	}

	function grid_row() {
		$conditions = [];

		$sensors = Sensor::select(['place_id' => $this->id]);
		foreach ($sensors as $sensor) {
			$conditions[] = $sensor->value_text();
		}

		return [
			'name' => "<a href='{$GLOBALS['config']['base_path']}/place/{$this->id}'>{$this->name}</a>",
			'type' => _a('place-types', $this->type),
			'condition' => join(' ', $conditions),
		];
	}

	function details() {
		$html = "";

		if ($photo = $this->photo_at(time())) {
			$dashboard_photo = new Dashboard_Photo();
			$dashboard_photo->id = "photo-".$this->id;
			$dashboard_photo->place_id = $this->id;
			$dashboard_photo->size = "large";

			$html .= $dashboard_photo->html();
		}

		$events = Place_Event::select(['place_id' => $this->id], 'timestamp DESC');

		if (count($events)) {
			$dl = new Html_DL();
			$dl->elements['title'] = array(
				'title' => __('Events'),
				'value' => '',
				'attributes' => [
					'class' => 'title',
				],
			);
			$dl->elements += array_map(function($event) {
				return [
					'title' => gmdate('Y-m-d H:i:s', $event->timestamp),
					'value' => '<b>'.$event->title.'</b>'.$event->details,
				];
			}, $events);
			$html .= $dl->html();
		}

		$sensors = Sensor::select(['place_id' => $this->id]);

		$chart = new Chart();
		$chart->id = "place-{$this->id}-line";
		$chart->title = $this->name;
		$chart->size = "large";
		$chart->period = "1-week";
		$chart->type = "line";
		$chart->parameters = [
			'events' => array_map(function($event) {
				return [
					'timestamp' => $event->timestamp,
					'title' => $event->title,
					'details' => $event->details,
				];
			}, $events),
			'sensors' => array_map(function($sensor) {
				return [
					'value' => [
						'id' => $sensor->id,
						'color' => '#'.substr(md5($sensor->name), 0, 6),
					],
				];
			}, array_filter($sensors, function($sensor) { return $sensor->type != 'rx-power'; })),
		];
		$html .= $chart->html();

		foreach ($sensors as $sensor) {
			$html .= $sensor->chart();
		}

		return $html;
	}

	function form() {
		$form = new HTML_Form();
		$form->attributes['class'] = "dl-form";

		$form->fields['id'] = new HTML_Input("place-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "place[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['name'] = new HTML_Input("place-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "place[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = __("Name");

		$form->fields['type'] = new HTML_Select("place-type");
		$form->fields['type']->name = "place[type]";
		$form->fields['type']->value = $this->type;
		$form->fields['type']->options = _a('place-types');
		$form->fields['type']->label = __("Type");

		$form->fields['public'] = new HTML_Input_Checkbox("place-public");
		$form->fields['public']->name = "place[public]";
		$form->fields['public']->value = $this->public;
		$form->fields['public']->label = __("Public");

		$form->fields['comment'] = new HTML_Textarea("place-comment");
		$form->fields['comment']->name = "place[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = __("Comment");

		$form->fields['mask'] = new HTML_Input("place-mask");
		$form->fields['mask']->type = "file";
		$form->fields['mask']->name = "place[mask]";
		$form->fields['mask']->label = __("Photo mask");

		if (!empty($this->mask)) {
			$file = new File();
			$file->load(['id' => $this->mask]);

			$form->fields['mask']->suffix = '<a href="'.$file->url().'">'.$file->name.'</a>';
		}

		$form->actions['save'] = new HTML_Button("place-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("place-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to delete this place?");

		$html = $form->html();

		if ($this->id) {
			$event = new Place_Event();
			$event->place_id = $this->id;

			$html .= $event->form();
		}

		return $html;
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

		if (isset($data['public'])) {
			$this->public = $data['public'];
		}

		if (isset($data['type'])) {
			$this->type = $data['type'];
		}

		if (isset($data['comment'])) {
			$this->comment = $data['comment'];
		}

		if (!empty($_FILES['place']['tmp_name']['mask'])) {
			$contents = file_get_contents($_FILES['place']['tmp_name']['mask']);
			$name = $_FILES['place']['name']['mask'];

			$file = new File();
			$file->name = $name;
			$file->save($contents);

			$this->mask = $file->id;
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
			'public' => (int)$this->public,
			'comment' => $db->escape($this->comment),
			'mask' => $db->escape($this->mask),
			'created' => time(),
			'updated' => time(),
		];

		$query = 'INSERT INTO places (' . implode(',', array_keys($fields)) . ') '.
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
			'public' => (int)$this->public,
			'comment' => $db->escape($this->comment),
			'mask' => $db->escape($this->mask),
			'updated' => time(),
		];

		$query = 'UPDATE places SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM places WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function photo() {
		return $this->photo_at(time());
	}

	function photo_at($time) {
		$photos = Photo::select(['place_id' => $this->id, 'timestamp <= '.(int)$time], 'timestamp DESC', '1');

		if (count($photos)) {
			return array_shift($photos);
		}

		return null;
	}

	function period($timestamp) {
		return Time::period($timestamp);
	}
}
