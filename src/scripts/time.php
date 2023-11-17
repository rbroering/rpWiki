<?php

if (!function_exists('timestamp')) {

	// TIMESTAMPS
	function timestamp($str, $type = 0, $format = false) {

		if ($str === 'GET') {
			$GLOBALS['timestamp'] = date('YmdHis');
			$GLOBALS['timezone'] = date('OT');
		} else {

			$timestamp = [
				"year"		=> substr($str, 0, 4),
				"month"		=> substr($str, 4, 2),
				"day"		=> substr($str, 6, 2),
				"hour"		=> substr($str, 8, 2),
				"minute"	=> substr($str, 10, 2),
				"second"	=> substr($str, 12, 2)
			];

			if (msg('_time24', 1) == 12) {
				if ($timestamp['hour'] > 12) {
					$hour12 = $timestamp['hour'] - 12;
					if (strlen($hour12) === 1)
						$hour12 = '0' . $hour12;
					$timestamp['hour'] = $hour12;
					$time24 = 'pm';
				} else
					$time24 = 'am';
			}

			$timestamp = array_values( $timestamp );
			if ($type === 1) {
				if ($format)
					return msg('_time-' . $format, 1, $timestamp);
				else {
					$timeMeridiem = '';
					if(isset($time24)) { $timeMeridiem = $time24; }
					return msg('_time', 1, $timestamp) . $timeMeridiem;
				}
			} else {
				msg('_time', 0, $timestamp);
				if (isset($time24)) echo $time24;
			}

		}

	}
}
