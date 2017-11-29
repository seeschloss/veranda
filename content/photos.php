<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$photos = Photo::select_latest_by_place(['places.public' => 1]);

$table = new Html_Table();
$table->header = Photo::grid_row_header();
$table->rows = array_map(function($photo) { return $photo->grid_row(); }, $photos);

?>
<div id="photos">
  <?= $table->html(); ?>
</div>

