<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$day_start = strtotime("today midnight", $day);
$day_stop = strtotime("tomorrow midnight", $day) - 1;

$photos = Photo::select([
  'place_id' => $place->id,
  'timestamp BETWEEN '.(int)$day_start.' AND '.(int)$day_stop,
], 'timestamp ASC');

$table = new Html_Table();
$table->header = Photo::grid_row_header_admin();
$table->rows = array_map(function($photo) { return $photo->grid_row_admin(); }, $photos);

?>
<div id="admin-photos">
  <?= $table->html(); ?>
</div>
