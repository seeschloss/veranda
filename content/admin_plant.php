<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['plant'])) {
  switch ($_POST['action']) {
    case 'water':
      $plant = new Plant();
      if ($plant->load(['id' => $_POST['plant']['id']])) {
        $watering = new Watering();
        $watering->plant_id = $plant->id;
        $watering->date = time();
        $watering->insert();
      }
      break;
    case 'insert':
      $plant = new Plant();
      $plant->from_form($_POST['plant']);
      if ($plant->insert()) {
        header("Location: {$GLOBALS['config']['base_path']}/admin/plant/".$plant->id);
      }
      break;
    case 'update':
      $plant = new Plant();
      $plant->from_form($_POST['plant']);
      $plant->update();
      break;
    case 'delete':
      $plant = new Plant();
      if ($plant->load(['id' => $_POST['plant']['id']])) {
        $plant->delete();
        header("Location: {$GLOBALS['config']['base_path']}/admin");
      }
      break;
  }
}

?>
<div id="plant">
  <?= $plant->form() ?>
</div>
<div id="plant-photo">
  <?= $plant->photo() ?>
</div>
<script src="<?= $GLOBALS['config']['base_path'] ?>/admin.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/modal.js"></script>
