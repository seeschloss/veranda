<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['alert'])) {
  switch ($_POST['action']) {
    case 'insert':
      $alert = new Alert();
      $alert->from_form($_POST['alert']);
      if ($alert->insert()) {
        header("Location: {$GLOBALS['config']['base_path']}/admin/alert/".$alert->id);
        die();
      }
      break;
    case 'update':
      $alert = new Alert();
      $alert->from_form($_POST['alert']);
      $alert->update();
      header("Location: {$GLOBALS['config']['base_path']}/admin/alert/".$alert->id);
      die();
      break;
    case 'delete':
      $alert = new Alert();
      if ($alert->load(['id' => $_POST['alert']['id']])) {
        $alert->delete();
        header("Location: {$GLOBALS['config']['base_path']}/admin");
        die();
      }
      break;
  }
}

?>
<div id="alert">
  <?= $alert->form() ?>
</div>

