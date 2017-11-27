<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Watering extends Record {
	public static $table = "waterings";

	public $id = 0;
	public $plant_id = 0;
	public $date = 0;

	function insert() {
		if (!$this->date) {
			$this->date = time();
		}

		$db = new DB();

		$fields = [
			'plant_id' => (int)$this->plant_id,
			'date' => (int)$this->date,
		];

		$query = 'INSERT INTO waterings (' . implode(',', array_keys($fields)) . ') '.
		                     'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}
}
