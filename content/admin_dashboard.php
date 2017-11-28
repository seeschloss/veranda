<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$charts = Chart::select();

$table_charts = new Html_Table();
$table_charts->header = Chart::grid_row_header_admin();
$table_charts->rows = array_map(function($chart) { return $chart->grid_row_admin(); }, $charts);

$new_chart = new Chart();
$table_charts->rows[] = $new_chart->grid_row_admin();

$dashboard_photos = Dashboard_Photo::select();

$table_photos = new Html_Table();
$table_photos->header = Dashboard_Photo::grid_row_header_admin();
$table_photos->rows = array_map(function($dashboard_photo) { return $dashboard_photo->grid_row_admin(); }, $dashboard_photos);

$new_dashboard_photo = new Dashboard_Photo();
$table_photos->rows[] = $new_dashboard_photo->grid_row_admin();

?>
<div id="admin-charts">
  <?= $table_charts->html(); ?>
</div>

<div id="admin-photos">
  <?= $table_photos->html(); ?>
</div>

