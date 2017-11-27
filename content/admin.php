<?php // vim: ft=html:et:sw=2:sts=2:ts=2

if (isset($_POST['update-watering'])) {
  if ($_POST['update-watering'] == 'all') {
    $plants = Plant::select();

    $time = time();

    foreach ($plants as $plant) {
      $watering = new Watering();
      $watering->plant_id = $plant->id;
      $watering->date = $time;
      $watering->insert();
    }
  }
}

$plants = Plant::select();

?>
<div id="admin">
  <img id='photo-boite' src="<?php print $GLOBALS['config']['latest-picture']['url']; ?>" />
  <div id="plant-form">
    <form action="/plant" method="POST">
      <input name="id" type="hidden" />
      <dl>
        <dt>Name</dt>
        <dd><input name="name" type="text" placeholder="Plant name" /></dd>

        <dt>Latin name</dt>
        <dd><input name="latin_name" type="text" placeholder="Latin name" /></dd>

        <dt>Planting date</dt>
        <dd><input name="planted" type="date" placeholder="Planting date" /></dd>

        <dt>Comment</dt>
        <dd><textarea name="comment"></textarea></dd>

        <dt><button type="submit" name="water"  value="water">Mark as watered</button></dt>
        <dt><button type="submit" name="save"   value="update">Save</button></dt>
        <dt><button type="submit" name="delete" value="delete">Delete</button></dt>
      </dl>
      <button type="button" name="area" value="area" id="area-button">Area</button>
    </form>
  </div>
  <form action"/admin" method="POST"><button id="update-watering" name="update-watering" value="all">Mark all watered</button></form>
  <button id="new-plant">Add</button>
  <script src="https://d3js.org/d3.v4.min.js"></script>
  <script>
  let plants = <?php echo json_encode(array_values($plants)) ?>;
  </script>
  <script src="admin.js"></script>
</div>

