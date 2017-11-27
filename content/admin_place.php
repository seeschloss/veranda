<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['place'])) {
  switch ($_POST['action']) {
    case 'insert':
      $place = new Place();
      $place->from_form($_POST['place']);
      if ($place->insert()) {
        header("Location: /admin/place/".$place->id);
        die();
      }
      break;
    case 'update':
      $place = new Place();
      $place->from_form($_POST['place']);
      $place->update();
      header("Location: /admin/place/".$place->id);
      die();
      break;
    case 'delete':
      $place = new Place();
      if ($place->load(['id' => $_POST['place']['id']])) {
        $place->delete();
        header("Location: /admin");
        die();
      }
      break;
  }
}

?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="/suncalc.js"></script>
<script src="/dashboard.js"></script>
<script src="/chart.js"></script>
<link rel="stylesheet" href="/css/chart.css" />
<div id="place">
  <?= $place->form() ?>
  <?= $place->details() ?>
</div>

