<?php
// $langcodes should only include fully translated JSON files
/* Determining the selected language (by User preference, Get method and Wiki configuration) */
if (!isset( $Wiki['config']['lang']['codes'] ))
	$langcodes = 'en,';
else
	$langcodes = $Wiki['config']['lang']['codes'];

if (isset($_GET[$Wiki['config']['urlparam']['lang']]) &&
!empty($_GET[$Wiki['config']['urlparam']['lang']]) &&
stristr($langcodes, $_GET[$Wiki['config']['urlparam']['lang']])) {
	$lang = $_GET[$Wiki['config']['urlparam']['lang']];
} else {
	if (isset($UserPref['lang']) && stristr($langcodes, $UserPref['lang']))
		$lang = $UserPref['lang'];
	else {
		if (isset($Wiki['config']['lang']['default']) && stristr($langcodes, $Wiki['config']['lang']['default']))
			$lang = $Wiki['config']['lang']['default'];
		else
			$lang = 'en';
	}
}

/* Check for system message file */
$jsonFile = dirname( __FILE__ ) . '/../' . $Wiki['dir']['langs'] . strtolower($lang) . '.json';

if (file_exists( $jsonFile )) {
	$jsonMsg = file_get_contents( $jsonFile );
	$msg = json_decode( $jsonMsg );
} elseIf ($lang !== 'msg') {
	echo 'ERROR';
	return false;
}

/* Function */
if (!function_exists('msg')) {
	
	function msg($str, $type = 0, $replace = 0) {
		global $msg;
		global $lang;
		global $Wiki;

		/* Swapping parameters in misorder */
		if ((is_array( $type ) || is_string( $type )) && (is_int( $replace ) || is_bool( $replace ))) {
			$swap = $replace;
			$replace = $type;
			$type = $swap;
		} elseIf ((is_array( $type ) || is_string( $type )) && $replace === 0) {
			$replace = $type;
			$type = 0;
		}

		/* Converting string/integer/numeric/bool $replace to array */
		if (!is_array( $replace ))
			$replace = [$replace];

		/* Printing message names */
		if ($lang === 'msg') {
			if (!isset( $type ) || $type === 0)
				echo $str;
			else {
				if ($type === 1)
					return $str;
			}
		} else {
			/* Handling missing translations / messages */
			if (empty( $msg->{$str} )) {
				$msgEn = json_decode( file_get_contents( dirname( __DIR__ ) . '/' . $Wiki['dir']['langs'] . 'en.json' ) );
				if (property_exists( $msgEn, $str ))
					$sm = $msgEn->{$str};
				else
					$sm = 'Error: The system message "' . $str . '" is not defined in ' . $lang . '.json' . ($lang == 'en') ? '' : ' or en.json';
			} else
				$sm = $msg->{$str};

			/* Replacements */
			# if (is_array( $replace ) && !empty( $replace )) {
			if (is_array( $replace )) {
				/* Switches */
				$sm = preg_replace_callback( '/\$([0-9,]+|\?)\[(.+)\]/U', function( $match ) use( $replace ) {
				#$sm = preg_replace_callback( '/\$([0-9,]+)\[(.+)\]/U', function( $match ) use( $replace ) {
					$key		= $match[1];
					$switch		= explode( '|', $match[2] );
					$options	= [];

					foreach ($switch as $option) {
						$option = explode( '=', $option );
						#$options[$option[0]] = $option[1];
						foreach (explode(',', $option[0]) as $multioption) {
							if ($multioption === '?')
								$options['?'] = $option[1];
							else
								$options[$multioption] = $option[1];
						}
					}

					if (empty($replace) && !key_exists('?', $options))
						return false;
					elseIf (empty($replace) && key_exists('?', $options))
						return $options['?'];
					elseIf (key_exists($replace[$key], $options)) /* Add same message for different specified values: e.g. $0[1,8=Hello|2,3=Bye] */
						return $options[$replace[$key]];
					elseIf (key_exists('default', $options))
						return $options['default'];
				}, $sm);
				/* Numeric variables */
				$sm = preg_replace_callback('/\$[0-9]{1,}/', function($match) use($replace) {
					$key = substr( $match[0], 1 );
					if (key_exists( $key, $replace ))
						return $replace[$key];
				}, $sm);
				/* Named variables */
				$sm = preg_replace_callback('/\$\$[A-Za-z]+\$/', function($match) use($replace) {
					$key = substr(substr($match[0], 0, strlen($match[0]) - 1), 2);
					if (key_exists($key, $replace))
						return $replace[$key];
				}, $sm);

				/* Functions trim */
				$sm = preg_replace_callback('/\%([a-z]+)\{(.+)\}(?:\[(.+)\])?/U', function($match) use($replace) {
					switch($match[1]) {
						default:
							return $match[0];
							break;
						case 'msg':
							return (isset($match[3])) ? msg($match[2], 1, json_decode($match[3]), true) : msg($match[2], 1);
							break;
						case 'trim':
							return (isset($match[3])) ? trim($match[2], $match[3]) : trim($match[2]);
							break;
						case 'ltrim':
							return (isset($match[3])) ? ltrim($match[2], $match[3]) : ltrim($match[2]);
							break;
						case 'rtrim':
						return (isset($match[3])) ? rtrim($match[2], $match[3]) : rtrim($match[2]);
							break;
					}
				}, $sm);
			}

			if ($type == 1)
				return prcon($sm, 'msg');
			else
				echo prcon($sm, 'msg');
		}
	}

}
