<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['dashboard_photo'])) {
  switch ($_POST['action']) {
    case 'insert':
      $dashboard_photo = new Dashboard_Photo();
      $dashboard_photo->from_form($_POST['dashboard_photo']);
      if ($dashboard_photo->insert()) {
        header("Location: /admin/dashboard-photo/".$dashboard_photo->id);
        die();
      }
      break;
    case 'update':
      $dashboard_photo = new Dashboard_Photo();
      $dashboard_photo->from_form($_POST['dashboard_photo']);
      $dashboard_photo->update();
      header("Location: /admin/dashboard-photo/".$dashboard_photo->id);
      die();
      break;
    case 'delete':
      $dashboard_photo = new Dashboard_Photo();
      if ($dashboard_photo->load(['id' => $_POST['dashboard_photo']['id']])) {
        $dashboard_photo->delete();
        header("Location: /admin");
        die();
      }
      break;
  }
}

?>
<div id="dashboard-photo">
  <?= $dashboard_photo->form() ?>
</div>

