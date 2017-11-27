<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$plants = Plant::select();

$table = new Html_Table();
$table->header = Plant::grid_row_header();
$table->rows = array_map(function($plant) { return $plant->grid_row(); }, $plants);

?>
<div id="plants">
  <?= $table->html(); ?>
</div>

