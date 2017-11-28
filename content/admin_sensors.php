<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$sensors = Sensor::select();

$table = new Html_Table();
$table->header = Sensor::grid_row_header_admin();
$table->rows = array_map(function($sensor) { return $sensor->grid_row_admin(); }, $sensors);

$new_sensor = new Sensor();
$table->rows[] = $new_sensor->grid_row_admin();

?>
<div id="admin-sensors">
  <?= $table->html(); ?>
</div>

