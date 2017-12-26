<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Plant_Note extends Record {
	public static $table = "plant_notes";
	public static $relations = ["plant_id" => "Plant"];

	public $id = 0;
	public $plant_id = 0;
	public $note = "";
	public $timestamp = 0;

	function insert() {
		$db = new DB();

		if (!$this->timestamp) {
			$this->timestamp = time();
		}

		$fields = [
			'plant_id' => (int)$this->plant_id,
			'note' => $db->escape($this->note),
			'timestamp' => $this->timestamp,
		];

		$query = 'INSERT INTO plant_notes (' . implode(',', array_keys($fields)) . ') '.
		                          'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function form() {
		$form = new HTML_Form();
		$form->attributes['class'] = "dl-form";

		$form->fields['plant_id'] = new HTML_Input("plant_note-plant_id");
		$form->fields['plant_id']->type = "hidden";
		$form->fields['plant_id']->name = "plant_note[plant_id]";
		$form->fields['plant_id']->value = $this->plant_id;

		$form->fields['note'] = new HTML_Textarea("plant_note-note");
		$form->fields['note']->name = "plant_note[note]";
		$form->fields['note']->value = $this->note;
		$form->fields['note']->label = __("Note");

		$form->actions['save'] = new HTML_Button("plant_note-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Record");
		$form->actions['save']->value = "insert-note";


		return $form->html();
	}

	function from_form($data) {
		if (isset($data['plant_id'])) {
			$this->plant_id = $data['plant_id'];
		} else {
			return false;
		}

		if (isset($data['note'])) {
			$this->note = $data['note'];
		}

		return true;
	}
}

