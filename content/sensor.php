<?php // vim: ft=html:et:sw=2:sts=2:ts=2
?>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="/suncalc.js"></script>
<script src="/dashboard.js"></script>
<script src="/chart.js"></script>
<link rel="stylesheet" href="/css/chart.css" />
<div id="sensor">
  <h1><?= $sensor->name ?></h1>
  <?= $sensor->chart() ?>
</div>

