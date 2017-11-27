<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$sensors = Sensor::select();

$table = new Html_Table();
$table->header = Sensor::grid_row_header();
$table->rows = array_map(function($sensor) { return $sensor->grid_row(); }, $sensors);

?>
<div id="sensors">
  <?= $table->html(); ?>
</div>

