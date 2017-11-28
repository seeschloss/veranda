<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$devices = Device::select();

$table = new Html_Table();
$table->header = Device::grid_row_header_admin();
$table->rows = array_map(function($device) { return $device->grid_row_admin(); }, $devices);

$new_device = new Device();
$table->rows[] = $new_device->grid_row_admin();

?>
<div id="admin-devices">
  <?= $table->html(); ?>
</div>

