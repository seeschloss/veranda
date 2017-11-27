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
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			return [
				'name' => "<a href='/admin/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='/admin/device/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 3],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => 'Name',
			'place' => 'Place',
			'type' => 'Type',
		];
	}

	function grid_row() {
		if ($this->id) {
			return [
				'name' => "<a href='/device/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'type' => $this->type,
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='/device/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 3],
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
}
