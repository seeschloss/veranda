<?php // vim: set ft=php noexpandtab sw=4 sts=4 ts=4

class Dashboard_Photo extends Record {
	public static $table = "dashboard_photos";
	public static $relations = ['place_id' => 'Place'];

	public $id = 0;
	public $place_id = 0;
	public $size = "";
	public $updated = 0;
	public $inserted = 0;

	static function grid_row_header_admin() {
		return [
			'name' => __('Title'),
			'size' => __('Size'),
		];
	}

	function grid_row_admin() {
		if ($this->id) {
			return [
				'place' => "<a href='{$GLOBALS['config']['base_path']}/admin/dashboard-photo/{$this->id}'>{$this->place()->name}</a>",
				'size' => _a('dashboard-sizes', $this->size),
			];
		} else {
			return [
				'name' => [
					'value' => "<a href='{$GLOBALS['config']['base_path']}/admin/dashboard-photo/{$this->id}'>".__('Add a new photo')."</a>",
					'attributes' => ['colspan' => 2],
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
		$form->attributes['class'] = 'dl-form';

		$form->fields['id'] = new HTML_Input("dashboard-photo-id");
		$form->fields['id']->type = "hidden";
		$form->fields['id']->name = "dashboard_photo[id]";
		$form->fields['id']->value = $this->id;

		$photos = Photo::select_latest_by_place();
		$places_with_photos = [];
		foreach ($photos as $photo) {
			$place = new Place();
			if ($place->load(['id' => $photo->place_id])) {
				$places_with_photos[$place->id] = $place->name;
			}
		}

		$form->fields['place_id'] = new HTML_Select("sensor-place-id");
		$form->fields['place_id']->name = "dashboard_photo[place_id]";
		$form->fields['place_id']->value = $this->place_id;
		$form->fields['place_id']->options = $places_with_photos;
		$form->fields['place_id']->label = __("Place");

		$form->fields['size'] = new HTML_Select("dashboard-photo-size");
		$form->fields['size']->name = "dashboard_photo[size]";
		$form->fields['size']->value = $this->size;
		$form->fields['size']->options = _a('dashboard-sizes');
		$form->fields['size']->label = __("Size");

		$form->actions['save'] = new HTML_Button("dashboard-photo-save");
		$form->actions['save']->name = "action";
		$form->actions['save']->label = __("Save");

		if ($this->id > 0) {
			$form->actions['save']->value = "update";
		} else {
			$form->actions['save']->value = "insert";
		}

		$form->actions['delete'] = new HTML_Button_Confirm("dashboard-photo-delete");
		$form->actions['delete']->name = "action";
		$form->actions['delete']->label = __("Delete");
		$form->actions['delete']->value = "delete";
		$form->actions['delete']->confirmation = __("Are you sure you want to remove this photo from the dashboard?");

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
			$this->place_id = (int)$data['place_id'];
		}

		if (isset($data['size'])) {
			$this->size = $data['size'];
		}
	}

	function save() {
		return $this->id > 0 ? $this->update() : $this->insert();
	}

	function insert() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'size' => $db->escape($this->size),
			'updated' => time(),
			'inserted' => time(),
		];

		$query = 'INSERT INTO dashboard_photos (' . implode(',', array_keys($fields)) . ') '.
		                               'VALUES (' . implode(',', array_values($fields)) . ')';

		$db->query($query);

		$this->id = $db->insert_id();

		return $this->id;
	}

	function update() {
		$db = new DB();

		$fields = [
			'place_id' => (int)$this->place_id,
			'size' => $db->escape($this->size),
			'updated' => time(),
		];

		$query = 'UPDATE dashboard_photos SET ' . implode(', ', array_map(function($k, $v) { return $k . '=' . $v; }, array_keys($fields), $fields)) .
		                              ' WHERE id = '.(int)$this->id;

		$db->query($query);

		return $this->id;
	}

	function delete() {
		$db = new DB();

		$query = 'DELETE FROM dashboard_photos WHERE id = '.(int)$this->id;

		$db->query($query);

		return true;
	}

	function photo() {
		$photos = Photo::select(['place_id' => $this->place_id], 'timestamp DESC', 1);

		return array_shift($photos);
	}

	function html() {
		switch($this->size) {
			case 'small':
				$basis = "20%";
				break;
			case 'medium':
				$basis = "33%";
				break;
			case 'large':
				$basis = "50%";
				break;
		}

		$photo = $this->photo();

		$plants_list = "<ul>";
        foreach (Plant::select(['place_id' => $this->place_id, 'box_x > 0'], '(box_y + box_height/2) ASC') as $plant) {
          $plants_list .= "<li data-id='{$plant->id}'
            data-box-x='{$plant->box_x}'
            data-box-y='{$plant->box_y}'
            data-box-width='{$plant->box_width}'
            data-box-height='{$plant->box_height}'
            ><a href='{$GLOBALS['config']['base_path']}/plant/{$plant->id}'>{$plant->name}</a></li>";
        }
		$plants_list .= "</ul>";

		if ($this->place->public and $videos = Video::select(['place_id' => $this->place_id, "quality != 'hd'"], "start DESC, id DESC", 1) and count($videos)) {
			$video = array_shift($videos);
			$link = "<a href='{$GLOBALS['config']['base_path']}/video/{$video->place_id}/{$video->id}'><img src='{$GLOBALS['config']['base_path']}/photo/{$photo->place_id}/{$photo->id}' /></a>";
		} else {
			$link = "<img src='{$GLOBALS['config']['base_path']}/photo/{$photo->place_id}/{$photo->id}' />";
		}

		return <<<HTML
			<div id="dashboard-photo-{$this->id}" style="flex-basis: {$basis};" class="dashboard-element dashboard-photo">
				<table>
					<tr>
						<th colspan="2" class="photo-place">{$this->place()->name}</th>
					</tr>
					<tr class="photo-with-legend">
						<td>{$link}</td>
						<td>{$plants_list}</td>
					</tr>
				</table>
				<script>
					link_plants("dashboard-photo-{$this->id}");
				</script>
			</div>
HTML;
	}
}
