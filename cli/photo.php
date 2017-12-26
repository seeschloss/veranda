#!/usr/bin/php
<?php

require __DIR__.'/../inc/common.inc.php';

$photos = Photo::select(['place_id' => 1, 'period' => 'night', 'timestamp >= '.(int)strtotime("2017-12-01")]);

foreach ($photos as $photo) {
	debug::dump(date("Y-m-d H:i:s", $photo->timestamp), $photo->period, $photo->brightness());
}
