<?php // vim: ft=html:et:sw=2:sts=2:ts=2

$videos = Video::select(['place_id' => $place->id], 'timestamp ASC');
$db = new DB();
$result = $db->query("SELECT COUNT(*) as count, timestamp/86400 as day FROM ".Video::$table." WHERE place_id={$place->id} GROUP BY day ORDER BY day ASC");
$counts = [];
if ($result) while ($row = $result->fetch()) {
  $counts[$row['day'] * 86400] = (int)$row['count'];
}

$calendar = new GregorianCalendar(2017);

foreach ($counts as $day => $count) {
  $date = strtotime("today midnight", $day);

  if (!isset($calendar->events[$date])) {
    $calendar->events[$date] = [];
  }

  $event = "<a href='/admin/place/{$place->id}/videos/{$date}'>".__("%s videos", $count)."</a>";

  $video = new Video();
  $video->load(['place_id' => $place->id, 'timestamp BETWEEN '.strtotime("12:00:00", $day).' AND '.strtotime("13:00:00", $day)]);

  if ($video->id) {
    $event .= "<img src='/video/{$place->id}/{$video->id}' />";
  }

  $calendar->events[$date][] = $event;
}

?>
<div id="place-videos">
  <?= $calendar->html() ?>
</div>

