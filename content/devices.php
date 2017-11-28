<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$devices = Device::select();

$table = new Html_Table();
$table->header = Device::grid_row_header();
$table->rows = array_map(function($device) { return $device->grid_row(); }, $devices);

?>
<div id="devices">
  <?= $table->html(); ?>
</div>

