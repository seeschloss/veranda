<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['chart'])) {
  switch ($_POST['action']) {
    case 'insert':
      $chart = new Chart();
      $chart->from_form($_POST['chart']);
      if ($chart->insert()) {
        header("Location: /admin/chart/".$chart->id);
        die();
      }
      break;
    case 'update':
      $chart = new Chart();
      $chart->from_form($_POST['chart']);
      $chart->update();
      header("Location: /admin/chart/".$chart->id);
      die();
      break;
    case 'delete':
      $chart = new Chart();
      if ($chart->load(['id' => $_POST['chart']['id']])) {
        $chart->delete();
        header("Location: /admin");
        die();
      }
      break;
  }
}

?>
<div id="chart">
  <?= $chart->form() ?>
</div>

