<?php // vim: ft=html:et:sw=2:sts=2:ts=2
?>
<div id="plant">
  <h1 class="name"><?php echo $plant->name; ?></h1>
  <h2 class="latin-name"><?php echo $plant->latin_name; ?></h2>
  <div class='place'><?= $plant->place()->name ?></div>
  <?php if ($plant->planted) { echo "<div class='planted'>Plantée : ".date("d/m/Y", $plant->planted)."</div>"; } ?>
  <?php if ($plant->comment) { echo "<div class='comment'>".$plant->comment."</div>"; } ?>
  <?= $plant->photo() ?>
</div>

