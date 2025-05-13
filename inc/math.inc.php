<?php

class Math {
	static function percentile($array, $percentile = 50) {
		if (count($array) < 4) {
			return array_sum($array) / count($array);
		}

		sort($array);

		$index = ($percentile/100) * count($array) - 1;

		if (floor($index) == $index) {
			$result = $array[$index];
		} else {
			$result = ($array[floor($index)] + $array[ceil($index)])/2;
		}

		return $result;
	}

	static function mean($array, $trim = 10) {
		if (count($array) < 4 or $trim < 1) {
			return array_sum($array) / count($array);
		}

		if ($trim > 50) {
			$trim = $trim - 50;
		}

		$percentile_min = Math::percentile($array, $trim);
		$percentile_max = Math::percentile($array, 100 - $trim);

		$trimmed_array = [];
		foreach ($array as $n) {
			if ($n >= $percentile_min and $n <= $percentile_max) {
				$trimmed_array[] = $n;
			}
		}

		$result = array_sum($trimmed_array) / count($trimmed_array);

		return $result;
	}

	static function great_circle_distance($lat1, $lon1, $lat2, $lon2) {
		// cf. https://www.movable-type.co.uk/scripts/latlong.html

		$r = 6372.8 * 1000;

		$lat1_rad = deg2rad($lat1);
		$lon1_rad = deg2rad($lon1);
		$lat2_rad = deg2rad($lat2);
		$lon2_rad = deg2rad($lon2);

		$alpha = ($lat2_rad - $lat1_rad)/2;
		$beta = ($lon2_rad - $lon1_rad)/2;
		$a = pow(sin($alpha), 2) + cos($lat1_rad) * cos($lat2_rad) * pow(sin($beta), 2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		$distance = $r * $c;
		return $distance;
	}
}
