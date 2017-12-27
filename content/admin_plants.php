<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$plants = (isset($_REQUEST['action']) and $_REQUEST['action'] == 'filter') ? Plant::filter($_REQUEST) : Plant::select();

$table = new Html_Table();
$table->filters = Plant::filters();
$table->header = Plant::grid_row_header_admin();
$table->rows = array_map(function($plant) { return $plant->grid_row_admin(); }, $plants);

$new_plant = new Plant();
$table->rows[] = $new_plant->grid_row_admin();

?>
<div id="admin-plants">
  <?= $table->html(); ?>
</div>
<script src="<?= $GLOBALS['config']['base_path'] ?>/form.js"></script>

