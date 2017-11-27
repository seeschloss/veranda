<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$photos = Photo::select(['place_id' => $place->id], 'timestamp DESC');

$table = new Html_Table();
$table->header = Photo::grid_row_header_admin();
$table->rows = array_map(function($photo) { return $photo->grid_row_admin(); }, $photos);

?>
<div id="admin-photos">
  <?= $table->html(); ?>
</div>

