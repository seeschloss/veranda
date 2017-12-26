<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$videos = Video::select_latest_by_place();

$table = new Html_Table();
$table->header = Video::grid_row_header_admin();
$table->rows = array_map(function($video) { return $video->grid_row_admin(); }, $videos);

?>
<div id="admin-videos">
  <?= $table->html(); ?>
</div>

