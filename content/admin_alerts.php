<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$alerts = Alert::select();

$table_alerts = new Html_Table();
$table_alerts->header = Alert::grid_row_header_admin();
$table_alerts->rows = array_map(function($alert) { return $alert->grid_row_admin(); }, $alerts);

$new_alert = new Alert();
$table_alerts->rows[] = $new_alert->grid_row_admin();

?>
<div id="admin-alerts">
  <?= $table_alerts->html(); ?>
</div>

