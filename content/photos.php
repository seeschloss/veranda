<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$photos = Photo::select_latest_by_place();

$table = new Html_Table();
$table->header = Photo::grid_row_header();

foreach ($photos as $photo) {
  if ($photo->place()->public) {
    $table->rows[] = $photo->grid_row();
  }
}

?>
<div id="photos">
  <?= $table->html(); ?>
</div>

