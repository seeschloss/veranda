<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Place_Event extends Record {
	public static $table = "place_events";
	public static $relations = ["place_id" => "Place"];

	public $id = 0;
	public $place_id = 0;
	public $note = "";
	public $timestamp = 0;

	function insert() {
		$db = new DB();

		if (!$this->timestamp) {
			$this->timestamp = time();
		}

		$fields = [
			'place_id' => (int)$this->place_id,
			'title' => $db->escape($this->title),
			'details' => $db->escape($this->details),
			'timestamp' => $this->timestamp,
		];

		$query = 'INSERT INTO place_events (' . implode(',', array_keys($fields)) . ') '.
		                          'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function form() {
		$form = new HTML_Form();
		$form->title = __('Event');
		$form->attributes['class'] = "dl-form";

		$form->fields['id'] = new HTML_Input("place-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "place_event[place_id]";
		$form->fields['id']->value = $this->place_id;

		$form->fields['date'] = new HTML_Input_Datetime("event-date");
		$form->fields['date']->name = "place_event[date]";
		$form->fields['date']->label = __("Date");
		$form->fields['date']->value = time();

		$form->fields['title'] = new HTML_Input("event-title");
		$form->fields['title']->type = "text";
		$form->fields['title']->name = "place_event[title]";
		$form->fields['title']->label = __("Title");

		$form->fields['details'] = new HTML_Textarea("event-details");
		$form->fields['details']->type = "text";
		$form->fields['details']->name = "place_event[details]";
		$form->fields['details']->label = __("Details");

		$form->actions['save'] = new HTML_Button("place_event-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Record");
		$form->actions['save']->value = "insert-event";


		return $form->html();
	}

	function from_form($data) {
		if (isset($data['place_id'])) {
			$this->place_id = $data['place_id'];
		} else {
			return false;
		}

		if (isset($data['title'])) {
			$this->title = $data['title'];
		}

		if (isset($data['details'])) {
			$this->details = $data['details'];
		}

		if (isset($data['date'])) {
			$this->timestamp = strtotime($data['date']);
		}

		return true;
	}
}


