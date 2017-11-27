<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Plant extends Record {
	public static $table = "plants";

	public $id = 0;
	public $place_id = 0;
	public $name = "";
	public $latin_name = "";
	public $planted = 0;
	public $comment = "";
	public $box_x = 0;
	public $box_y = 0;
	public $box_width = 0;
	public $box_height = 0;
	public $dirt_x = 0;
	public $dirt_y = 0;
	public $dirt_width = 0;
	public $dirt_height = 0;
	public $created = 0;
	public $updated = 0;

	static function grid_row_header_admin() {
		return [
			'name' => 'Name',
			'place' => 'Place',
			'planted' => 'Planted',
			'latin_name' => 'Latin name',
			'last_watered' => 'Watered',
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			$could_be_watered = true;

			$last_watered_class = "";
			if ($last_watered = $this->last_watered()) {
				$seconds = time() - $last_watered;
				$last_update = new DateTime();
				$last_update->setTimestamp($last_watered);

				$lag = $last_update->diff(new DateTime());

				$last_watered_string = "";
				if ($lag->d > 0) {
					$last_watered_string = $lag->format("%dd, %hh ago");

					if ($lag->d >= 10) {
						$last_watered_class = "alert";
					}
				} else if ($lag->h > 0) {
					$last_watered_string = $lag->format("%hh ago");
					$could_be_watered = false;
				} else {
					$last_watered_string .= "now";
					$could_be_watered = false;
				}
			} else {
				$last_watered_string = "never";
			}


			$checkbox_extra = "";
			if (!$could_be_watered) {
				$checkbox_extra = "disabled checked";
			}
			// The following form is a bit special, let me explain it:
			// I want it to be a checkbox that you check to indicate that the plant
			// has just been watered.
			// It has to be submitted in AJAX to not interfere too much with browsing.
			// The problem is calling form.submit() (from the checkbox' onclick) doesn't
			// trigger the form's onsubmit event, so instead I have to add a hidden
			// submit element, and call this element.click().
			$last_watered_string .= <<<HTML
				<form class="ajax mini-form" action="/admin/plant/{$this->id}" method="POST">
					<input type="hidden" name="action" value="water" />
					<input type="hidden" name="plant[id]" value="{$this->id}" />
					<input type="checkbox" onclick="this.disabled='disabled'; this.form.querySelector('.submit').click()" {$checkbox_extra} />
					<input class="submit" type="submit" style="display: none;" />
				</form>
HTML;

			return [
				'name' => "<a href='/admin/plant/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'planted' => $this->planted > 0 ? date('Y-m-d', $this->planted) : "",
				'latin_name' => $this->latin_name,
				'last_watered' => [
					'value' => $last_watered_string,
					'attributes' => [
						'class' => $last_watered_class,
					],
				],
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='/admin/plant/{$this->id}'>{$this->name}</a>",
					'attributes' => ['colspan' => 5],
				],
			];
		}
	}

	static function grid_row_header() {
		return [
			'name' => 'Name',
			'place' => 'Place',
			'planted' => 'Planted',
			'latin_name' => 'Latin name',
			'last_watered' => 'Watered',
		];
	}

	function grid_row() {
		if ($this->id) {
			$could_be_watered = true;

			$last_watered_class = "";
			if ($last_watered = $this->last_watered()) {
				$seconds = time() - $last_watered;
				$last_update = new DateTime();
				$last_update->setTimestamp($last_watered);

				$lag = $last_update->diff(new DateTime());

				$last_watered_string = "";
				if ($lag->d > 0) {
					$last_watered_string = $lag->format("%dd, %hh ago");

					if ($lag->d >= 10) {
						$last_watered_class = "alert";
					}
				} else if ($lag->h > 0) {
					$last_watered_string = $lag->format("%hh ago");
					$could_be_watered = false;
				} else {
					$last_watered_string .= "now";
					$could_be_watered = false;
				}
			} else {
				$last_watered_string = "never";
			}

			return [
				'name' => "<a href='/plant/{$this->id}'>{$this->name}</a>",
				'place' => $this->place()->name,
				'planted' => $this->planted > 0 ? date('Y-m-d', $this->planted) : "",
				'latin_name' => "<a href='https://en.wikipedia.org/wiki/{$this->latin_name}'>{$this->latin_name}</a>",
				'last_watered' => [
					'value' => $last_watered_string,
					'attributes' => [
						'class' => $last_watered_class,
					],
				],
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='/plant/{$this->id}'>{$this->name}</a>",
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

		$form->fields['id'] = new HTML_Input("plant-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "plant[id]";
		$form->fields['id']->value = $this->id;

		$form->fields['place_id'] = new HTML_Select("plant-place-id");
		$form->fields['place_id']->name = "plant[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = array_map(function($place) { return $place->name; }, Place::select());
		$form->fields['place_id']->label = "Place";

		if ($photo = $this->place()->photo_at(time())) {
			$form->fields['place_id']->suffix = "<a class='button modal' href='/admin/plant/{$this->id}/locate'>Locate plant on photo</a>";
		}

		$form->fields['name'] = new HTML_Input("plant-name");
		$form->fields['name']->type = "text";
		$form->fields['name']->name = "plant[name]";
		$form->fields['name']->value = $this->name;
		$form->fields['name']->label = "Name";

		$form->fields['latin_name'] = new HTML_Input("plant-latin-name");
		$form->fields['latin_name']->type = "text";
		$form->fields['latin_name']->name = "plant[latin_name]";
		$form->fields['latin_name']->value = $this->latin_name;
		$form->fields['latin_name']->label = "Latin name";

		$form->fields['planted'] = new HTML_Input("plant-planted");
		$form->fields['planted']->type = "date";
		$form->fields['planted']->name = "plant[planted]";
		$form->fields['planted']->value = date("Y-m-d", $this->planted);
		$form->fields['planted']->label = "Planting date";

		$form->fields['comment'] = new HTML_Textarea("plant-comment");
		$form->fields['comment']->name = "plant[comment]";
		$form->fields['comment']->value = $this->comment;
		$form->fields['comment']->label = "Comment";

		$form->actions['save'] = new HTML_Button("plant-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = "Save";
		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("plant-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = "Delete";
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = "Are you sure you want to delete this plant?";

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

		if (isset($data['latin_name'])) {
			$this->latin_name = $data['latin_name'];
		}

		if (isset($data['planted'])) {
			$parts = explode('-', $data['planted']);
			$this->planted = (int)gmmktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
		}

		if (isset($data['comment'])) {
			$this->comment = $data['comment'];
		}

		if (isset($data['box_x'])) {
			$this->box_x = (int)$data['box_x'];
		}

		if (isset($data['box_y'])) {
			$this->box_y = (int)$data['box_y'];
		}

		if (isset($data['box_width'])) {
			$this->box_width = (int)$data['box_width'];
		}

		if (isset($data['box_height'])) {
			$this->box_height = (int)$data['box_height'];
		}

		if (isset($data['dirt_x'])) {
			$this->dirt_x = (int)$data['dirt_x'];
		}

		if (isset($data['dirt_y'])) {
			$this->dirt_y = (int)$data['dirt_y'];
		}

		if (isset($data['dirt_width'])) {
			$this->dirt_width = (int)$data['dirt_width'];
		}

		if (isset($data['dirt_height'])) {
			$this->dirt_height = (int)$data['dirt_height'];
		}
	}

	function has_photo() {
		if (!$this->box_x) {
			return false;
		}

		if ($photo = $this->place()->photo_at(time())) {
			return true;
		}
	}

	function photo() {
		if (!$this->box_x) {
			return "";
		}

		if ($photo = $this->place()->photo_at(time())) {
			return '<img src="data:image/jpg;base64,'.base64_encode($this->box_image($photo)).'" />';
		}
	}

	function box_image($photo) {
		$original = imagecreatefromjpeg($photo->best_quality());
		$cropped = imagecreatetruecolor($this->box_width, $this->box_height);

		imagecopy($cropped, $original, 0, 0, $this->box_x, $this->box_y, $this->box_width, $this->box_height);

		ob_start();
		imagejpeg($cropped);
		imagedestroy($cropped);
		return ob_get_clean();
	}

	function box_colour($timestamp = null) {
		if ($photo = $this->place()->photo_at($timestamp)) {
			$image = imagecreatefromjpeg($photo->best_quality());
		} else {
			return false;
		}

		$r = [];
		$g = [];
		$b = [];

		$real_x = $this->box_x;
		$real_y = $this->box_y;

		$real_width = $this->box_width;
		$real_height = $this->box_height;

		for ($x = $real_x; $x <= $real_x + $real_width; $x++) {
			for ($y = $real_y; $y <= $real_y + $real_height; $y++) {
				$rgb = imagecolorat($image, $x, $y);

				$r[] = ($rgb >> 16) & 0xFF;
				$g[] = ($rgb >>  8) & 0xFF;
				$b[] =  $rgb        & 0xFF;
			}
		}

		$r = base_convert((int)(array_sum($r)/count($r)), 10, 16);
		$g = base_convert((int)(array_sum($g)/count($g)), 10, 16);
		$b = base_convert((int)(array_sum($b)/count($b)), 10, 16);

		return $r.$g.$b;
	}

	function save() {
		return $this->id > 0 ? $this->update() : $this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'name' => $db->escape($this->name),
			'latin_name' => $db->escape($this->latin_name),
			'planted' => (int)$this->planted,
			'comment' => $db->escape($this->comment),
			'box_x' => (int)$this->box_x,
			'box_y' => (int)$this->box_y,
			'box_width' => (int)$this->box_width,
			'box_height' => (int)$this->box_height,
			'dirt_x' => (int)$this->dirt_x,
			'dirt_y' => (int)$this->dirt_y,
			'dirt_width' => (int)$this->dirt_width,
			'dirt_height' => (int)$this->dirt_height,
			'created' => time(),
			'updated' => time(),
		];

		$query = 'INSERT INTO plants (' . implode(',', array_keys($fields)) . ') '.
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
			'latin_name' => $db->escape($this->latin_name),
			'planted' => (int)$this->planted,
			'comment' => $db->escape($this->comment),
			'box_x' => (int)$this->box_x,
			'box_y' => (int)$this->box_y,
			'box_width' => (int)$this->box_width,
			'box_height' => (int)$this->box_height,
			'dirt_x' => (int)$this->dirt_x,
			'dirt_y' => (int)$this->dirt_y,
			'dirt_width' => (int)$this->dirt_width,
			'dirt_height' => (int)$this->dirt_height,
			'updated' => time(),
		];

		$query = 'UPDATE plants SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		         ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM plants WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function last_watered() {
		$waterings = Watering::select(['plant_id' => $this->id], 'date DESC', 1);

		if (count($waterings)) {
			$watering = array_shift($waterings);
			return $watering->date;
		}

		return 0;
	}
}
