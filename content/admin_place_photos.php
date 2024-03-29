<?php // vim: ft=html:et:sw=2:sts=2:ts=2

if (!isset($year)) {
  $year = date('Y');
}

$calendar = new GregorianCalendar($year);

$photos = Photo::select([
    'place_id' => $place->id,
    'timestamp' => '> '.mktime(0, 0, 0, 1, 0, $year),
    'timestamp' => '< '.mktime(0, 0, 0, 1, 1, $year + 1),
], 'timestamp ASC');
$db = new DB();
$result = $db->query("SELECT COUNT(*) as count, timestamp/86400 as day FROM ".Photo::$table." WHERE place_id={$place->id} GROUP BY day ORDER BY day ASC");
$counts = [];
if ($result) while ($row = $result->fetch()) {
  $counts[$row['day'] * 86400] = (int)$row['count'];
}

foreach ($counts as $day => $count) {
  $date = strtotime("today midnight", $day);

  if (!isset($calendar->events[$date])) {
    $calendar->events[$date] = [];
  }

  $event = "<a href='/admin/place/{$place->id}/photos/{$date}'>".__("%s photos", $count)."</a>";

  $photo = new Photo();
  $photo->load(['place_id' => $place->id, 'timestamp BETWEEN '.strtotime("12:00:00", $day).' AND '.strtotime("13:00:00", $day)]);

  if ($photo->id) {
    $event .= "<img src='/photo/{$place->id}/{$photo->id}' />";
  }

  $calendar->events[$date][] = $event;
}

?>
<div id="place-photos">
  <?= $calendar->html() ?>
</div>

