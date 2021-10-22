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
				$last_updated_string = $lag->format("%y")." ".($lag->y > 1 ? __("years") : __("year"));
			} else if ($lag->m > 0) {
				$last_updated_string = $lag->format("%m")." ".($lag->m > 1 ? __("months") : __("month"));
			} else if ($lag->d > 0) {
				$last_updated_string = $lag->format("%dd, %hh, %im, %ss");
			} else if ($lag->h > 0) {
				$last_updated_string = $lag->format("%hh, %im, %ss");
			} else if ($lag->i > 0) {
				$last_updated_string = $lag->format("%im, %ss");
			} else if ($lag->s > 0) {
				$last_updated_string = $lag->format("%ss");
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

