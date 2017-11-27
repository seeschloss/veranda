<?php // vim: ft=html:et:sw=2:sts=2:ts=2

// place_id = 1 : boîte
$plants = Plant::select(['place_id = 1']);

usort($plants, function($a, $b) {
  return $a->box_y - $b->box_y;
});

?>
<div id="home">
    <svg id='daily-outside' width="450" height="350"></svg>
    <svg id='daily-inside' width="450" height="350"></svg>
    <div id='plants'>
      <a href="<?php print $GLOBALS['config']['latest-video']['url']; ?>" type="video/webm" /><img id='photo-boite' src="<?php print $GLOBALS['config']['latest-picture']['url']; ?>" /></a>
      <ul>
      <?php
        foreach ($plants as $plant) {
          print "<li data-id='{$plant->id}'
            data-box-x='{$plant->box_x}'
            data-box-y='{$plant->box_y}'
            data-box-width='{$plant->box_width}'
            data-box-height='{$plant->box_height}'
            ><a href='/plant/{$plant->id}'>{$plant->name}</a></li>";
        }
      ?>
      </ul>
    </div>
    <svg id='hourly' height="550"></svg>
    <script src="suncalc.js"></script>
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="home.js"></script>
    <script>
    link_plants();
    show_home_charts(
      "<?php print $GLOBALS['config']['local-temperatures']['url']; ?>",
      "<?php print $GLOBALS['config']['lille-temperatures']['url']; ?>"
    );
    </script>
</div>
