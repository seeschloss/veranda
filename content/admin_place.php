<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['place'])) {
  switch ($_POST['action']) {
    case 'insert':
      $place = new Place();
      $place->from_form($_POST['place']);
      if ($place->insert()) {
        header("Location: {$GLOBALS['config']['base_path']}/admin/place/".$place->id);
        die();
      }
      break;
    case 'update':
      $place = new Place();
      $place->from_form($_POST['place']);
      $place->update();
      header("Location: {$GLOBALS['config']['base_path']}/admin/place/".$place->id);
      die();
      break;
    case 'delete':
      $place = new Place();
      if ($place->load(['id' => $_POST['place']['id']])) {
        $place->delete();
        header("Location: {$GLOBALS['config']['base_path']}/admin");
        die();
      }
      break;
  }
}

if (isset($_POST['action']) and $_POST['action'] == 'insert-event' and isset($_POST['place_event'])) {
  $event = new Place_Event();
  if ($event->from_form($_POST['place_event'])) {
    $event->insert();
  }
}

?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/suncalc.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/dashboard.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/chart.js"></script>
<link rel="stylesheet" href="<?= $GLOBALS['config']['base_path'] ?>/css/chart.css" />
<div id="place">
  <?= $place->form() ?>
  <?= $place->details() ?>
</div>

