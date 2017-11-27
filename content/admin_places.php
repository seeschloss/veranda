<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$places = Place::select();

$table = new Html_Table();
$table->header = Place::grid_row_header_admin();
$table->rows = array_map(function($place) { return $place->grid_row_admin(); }, $places);

$new_place = new Place();
$new_place->name = "Add a new place";
$table->rows[] = $new_place->grid_row_admin();

?>
<div id="admin-places">
  <?= $table->html(); ?>
</div>

