<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$photos = Photo::select_latest_by_place();

$table = new Html_Table();
$table->header = Photo::grid_row_header_admin();
$table->rows = array_map(function($photo) { return $photo->grid_row_admin(); }, $photos);

$new_photo = new Photo();
$new_photo->name = "Add a new photo";
$table->rows[] = $new_photo->grid_row_admin_new();

?>
<div id="admin-photos">
  <?= $table->html(); ?>
</div>

