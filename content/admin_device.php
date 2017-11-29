<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['device'])) {
  switch ($_POST['action']) {
    case 'insert':
      $device = new Device();
      $device->from_form($_POST['device']);
      var_dump($device);
      if ($device->insert()) {
        header("Location: {$GLOBALS['config']['base_path']}/admin/device/".$device->id);
      }
      break;
    case 'update':
      $device = new Device();
      $device->from_form($_POST['device']);
      $device->update();
      break;
    case 'delete':
      $device = new Device();
      if ($device->load(['id' => $_POST['device']['id']])) {
        $device->delete();
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
<div id="device">
  <?= $device->form() ?>
  <?= $device->details() ?>
</div>

