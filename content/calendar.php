<?php // vim: ft=html:et:sw=2:sts=2:ts=2
?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/suncalc.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/dashboard.js"></script>
<script src="<?= $GLOBALS['config']['base_path'] ?>/chart.js"></script>
<link rel="stylesheet" href="<?= $GLOBALS['config']['base_path'] ?>/css/chart.css" />
<div id="calendar">
<?php

if (isset($_REQUEST['type']) and $_REQUEST['type'] == 'republican') {
  $calendar = new FlorealCalendar();
} else {
  $calendar = new GregorianCalendar();
}

$plant_notes = Plant_Note::select();
foreach ($plant_notes as $plant_note) {
  $date = strtotime("today midnight", $plant_note->timestamp);

  if (!isset($calendar->events[$date])) {
    $calendar->events[$date] = [];
  }

  $plant = new Plant();
  $plant->load(['id' => $plant_note->plant_id]);
  $calendar->events[$date][] = __("Note: %s « %s »", "<a href='/plant/{$plant->id}'>{$plant->name}</a>", $plant_note->note);
}

$waterings = Watering::select();

foreach ($waterings as $watering) {
  $date = strtotime("today midnight", $watering->date);

  if (!isset($calendar->events[$date])) {
    $calendar->events[$date] = [];
  }

  $plant = new Plant();
  $plant->load(['id' => $watering->plant_id]);
  $calendar->events[$date][] = __("Watered: %s", $plant->name);
}

print $calendar->html();

?>
</div>

