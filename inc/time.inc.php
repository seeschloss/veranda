<?php

class Time {
	public static function format_last_updated($last_updated) {
		$last_updated_string = "";

		if ($last_updated > 0) {
			$seconds = time() - $last_updated;
			$last_update = new DateTime();
			$last_update->setTimestamp($last_updated);

			$lag = $last_update->diff(new DateTime());

			$last_updated_string = "";
			if ($lag->y > 0) {
				$last_updated_string = $last_update->format("Y-m-d");
			} else if ($lag->m > 0) {
				$last_updated_string = $last_update->format("Y-m-d");
			} else if ($lag->d > 0) {
				$last_updated_string = __("%s ago", $lag->format("%d".__("d (day/days)").", %h".__("h (hour/hours)").", %i".__("m (minute/minutes)").", %s".__("s (second/seconds)")));
			} else if ($lag->h > 0) {
				$last_updated_string = __("%s ago", $lag->format("%h".__("h (hour/hours)").", %i".__("m (minute/minutes)").", %s".__("s (second/seconds)")));
			} else if ($lag->i > 0) {
				$last_updated_string = __("%s ago", $lag->format("%i".__("m (minute/minutes)").", %s".__("s (second/seconds)")));
			} else if ($lag->s > 0) {
				$last_updated_string = __("%s ago", $lag->format("%s".__("s (second/seconds)")));
			}
		} else {
			$last_updated_string = __("never");
		}

		return $last_updated_string;
	}

	public static function period($timestamp) {
		$sunrise = date_sunrise($timestamp,
				SUNFUNCS_RET_TIMESTAMP,
				$GLOBALS['config']['location']['latitude'],
				$GLOBALS['config']['location']['longitude'],
				91);

		if ($timestamp < $sunrise) {
			return 'night';
		}

		$twilight_stop = date_sunrise($timestamp,
				SUNFUNCS_RET_TIMESTAMP,
				$GLOBALS['config']['location']['latitude'],
				$GLOBALS['config']['location']['longitude'],
				80);

		if ($timestamp < $twilight_stop) {
			return 'twilight';
		}

		$twilight_start = date_sunset($timestamp,
				SUNFUNCS_RET_TIMESTAMP,
				$GLOBALS['config']['location']['latitude'],
				$GLOBALS['config']['location']['longitude'],
				80);

		if ($timestamp < $twilight_start) {
			return 'day';
		}

		$sunset = date_sunset($timestamp,
				SUNFUNCS_RET_TIMESTAMP,
				$GLOBALS['config']['location']['latitude'],
				$GLOBALS['config']['location']['longitude'],
				91);

		if ($timestamp < $sunset) {
			return 'twilight';
		}

		return 'night';
	}
}

