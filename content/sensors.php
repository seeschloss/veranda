<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$sensors = (isset($_REQUEST['action']) and $_REQUEST['action'] == 'filter') ? Sensor::filter($_REQUEST, ['places.public' => 1]) : Sensor::select(['places.public' => 1]);

$table = new Html_Table();
$table->filters = Sensor::filters();
$table->header = Sensor::grid_row_header();
$table->rows = array_map(function($sensor) { return $sensor->grid_row(); }, $sensors);

?>
<div id="sensors">
  <?= $table->html(); ?>
</div>

