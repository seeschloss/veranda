<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['sensor'])) {
  switch ($_POST['action']) {
    case 'insert':
      $sensor = new Sensor();
      $sensor->from_form($_POST['sensor']);
      var_dump($sensor);
      if ($sensor->insert()) {
        header("Location: {$GLOBALS['config']['base_path']}/admin/sensor/".$sensor->id);
      }
      break;
    case 'update':
      $sensor = new Sensor();
      $sensor->from_form($_POST['sensor']);
      $sensor->update();
      break;
    case 'delete':
      $sensor = new Sensor();
      if ($sensor->load(['id' => $_POST['sensor']['id']])) {
        $sensor->delete();
        header("Location: {$GLOBALS['config']['base_path']}/admin");
      }
      break;
  }
}

?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/suncalc.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/dashboard.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/chart.js"></script>
<link rel="stylesheet" href="<?= $GLOBALS['config']['base_path'] ?>/css/chart.css" />
<div id="sensor">
  <?= $sensor->form() ?>
  <?= $sensor->chart() ?>
</div>

