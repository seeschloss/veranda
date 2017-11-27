<?php // vim: ft=html:et:sw=2:sts=2:ts=2
if (isset($_POST['action']) and isset($_POST['photo'])) {
  switch ($_POST['action']) {
    case 'insert':
      $photo = new Photo();
      $photo->from_form($_POST['photo'], $_FILES['photo']);
      if ($photo->insert()) {
        header("Location: /admin/photo/".$photo->id);
        die();
      }
      break;
    case 'delete':
      $photo = new Photo();
      if ($photo->load(['id' => $_POST['photo']['id']])) {
        $photo->delete();
        header("Location: /admin");
        die();
      }
      break;
  }
}

?>
<div id="photo">
  <?= $photo->form() ?>
</div>

