<?php // vim: ft=html:et:sw=2:sts=2:ts=2
?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="/suncalc.js"></script>
<script src="/dashboard.js"></script>
<script src="/chart.js"></script>
<link rel="stylesheet" href="/css/chart.css" />
<div id="dashboard">
<?php

$photos = Dashboard_Photo::select();
foreach ($photos as $photo) {
  echo $photo->html();
}

$charts = Chart::select();
foreach ($charts as $chart) {
  echo $chart->html();
}

?>
</div>

