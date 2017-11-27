<?php

require __DIR__.'/../inc/common.inc.php';

require __DIR__.'/../inc/update.inc.php';

$timestamp = null;
if ($argc > 1) {
	$timestamp = strtotime($argv[1]);
}

$plants = Plant::select();

echo "timestamp\tid\tcolour\tname\n";

for ($t = strtotime("2017-10-05 12:00:00"); $t < time(); $t = strtotime("+1 day", $t)) {

	foreach ($plants as $plant) {
		echo $t."\t".$plant->id."\t".$plant->box_colour($t)."\t".$plant->name."\n";
	}

}
