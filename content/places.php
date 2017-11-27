<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$places = Place::select(['public' => 1]);

$table = new Html_Table();
$table->header = Place::grid_row_header();
$table->rows = array_map(function($place) { return $place->grid_row(); }, $places);

?>
<div id="places">
  <?= $table->html(); ?>
</div>

