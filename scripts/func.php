<?php
if (!defined('VALIDACCESS')) {
	exit();
}

if (!function_exists('url')) {

	// URL: REPLACE SPECIAL CHARS
	function url($str) {
	$str = urlencode($str);
		return $str;
	}

}

if (!function_exists('param')) {

	// CHECK GET-PARAMETERS
	function param($param, $requiresValue = 1) {
		global $Wiki;

		if (isset( $Wiki['config']['urlparam'][$param] )) {
			if (!$requiresValue)
				return (isset( $_GET[$Wiki['config']['urlparam'][$param]] )) ? 1 : 0;
			elseif ($requiresValue)
				return (isset( $_GET[$Wiki['config']['urlparam'][$param]] ) && !empty( $_GET[$Wiki['config']['urlparam'][$param]] )) ? 1 : 0;
		} else {
			return 0;
		}
	}
}

if (!function_exists('shortStr')) {

	// SHORT
	function shortStr($shortStr = '', $strLength = 200) {
		if (strlen( $shortStr ) > $strLength) {
				$shortStr = substr( $shortStr, 0, $strLength ) . '…';
				$shortedStr = strrchr( $shortStr, ' ' );
				$shortStr = str_replace( $shortedStr, ' …', $shortStr );
		}
		return $shortStr;
	}

}

if (!function_exists( 'remIfExists' )) {

	// SHORT
	function remIfExists ($Haystack, $Needle, $Offset = -1) {
		if ($Offset == 'start')
			$Offset = 0;
		if ($Offset == 'end') # default
			$Offset = -1;

			#$Position = $Offset;
		if ($Offset == 0) {
			$Position = -1;
		} elseIf ($Offset == -1) {
			$Position = 0;
		}

		$Haystack = (substr( $Haystack, $Offset, strlen( $Needle ) ) == $Needle ) ? substr( $Haystack, $Position, strlen( $Haystack ) - strlen( $Needle ) ) : $Haystack;
		return $Haystack;
	}

}

if (!function_exists( 'randstr' )) {

	// RANDOM STRs
	function randstr($length, $charsRestriction = 0) {
		$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-';
		$randnr = '';
		for ($i = 0; $i != $length; ++$i) {
			$randnr .= $chars[mt_rand( 0, strlen( $chars ) - 1 )];
		}
		return $randnr;
	}

}

// Needed for function randID
if (!function_exists( 'checkTables' )) {
	function checkTables($string, $table = "log") {
		global $dbc;

		//$find = $dbc->prepare("SELECT `rid` FROM `:table` WHERE `rid` = :rid");
		$find = $dbc->prepare("SELECT `rid` FROM `$table` WHERE `rid` = :rid");
		$find->execute([
			//':table'	=> $table,
			':rid'		=> $string
		]);
		return $find->rowCount() > 0;
	}
}

if (!function_exists( 'randID' )) {

	// RANDOM STRs
	function randID($length = 10, $table = "log") {
		$rand = randStr( $length );

		switch ($table) {
			case "log":
			case "pages":
			case "comments":
			case "users":
			case "requests":
			case "files":
			break;
			default:
				$table = "log";
			break;
		}

		while (checkTables($rand, $table)) {
			$rand = randStr($length);
		}

		return $rand;
	}

}

if (!function_exists( 'listglue' )) {

	// Make messageable list
	function listglue ($array, $options = []) {
		$new = '';

		if (is_string($array))
			$array = explode(',', $array);

		foreach ($array as $i => $val) {
			if (in_array('groups', $options))
				$array[$i] = msg('group-' . $val, 1);


			$new .= $val;
			if (end( $array ) != $val)
				$new .= ', ';
		}

		return implode (', ', $array);
	}

}

if (!function_exists( 'protectlayer' )) {

	// Make messageable list
	function protectlayer ($protection) {
		global $Wiki;

			if (substr($protection, 0, 1) === '#' && !empty($Wiki['select-groups']['protection'])) {
			if (is_numeric(substr($protection, 1))
				&& intval(substr($protection, 1)) < count($Wiki['select-groups']['protection'])
				&& intval(substr($protection, 1)) > -2)
			{
				if (intval(substr($protection, 1)) === -1)
					return [];
				else
					return $Wiki['select-groups']['protection'][intval(substr($protection, 1))];
			} else
				return array_merge($Wiki['select-groups']['protection'][0], ['___FALLBACK___']); // Fallback is highest level of protection.
		} else
			return $protection;
	}

}

if (!function_exists( 'get_usericon' )) {

	// Get usericon URL
	function get_usericon($_User) {
		global $Wiki;
		global $dbc;

		$get = $dbc->prepare("SELECT usericon FROM user WHERE username = :username LIMIT 1");
		$get->execute([':username' => $_User]);
		$get = $get->fetch();

		return ($get && !empty($get['usericon'])) ? $Wiki['dir']['media'] . $get['usericon'] . "/$_User.png" : 'custom/usericon.png';
	}

}

if (!function_exists('replace')) {

	// REPLACE
	function replace($str, $type) {
		if($type == 'p' || $type == 1) {
			// Page
			$rLetters = array('&#92;', '&apos;', '  ', "\r\n\r\n");
			$redLetters = array('\\', "'", ' ', '<br /><br />');
			$rTags = array(
			'|^(.*)<style(.*)</style>(.*)$|', '|^(.*)<script(.*)</script>(.*)$|'
			, '|^(.*)contenteditable="(.*)"(.*)$|'
			);
			$redTags = array(
			"$1$3", "$1$3",
			"$1$3"
			);

			$text1 = array("\\\r\n");
			$replaced1 = array('<br />');

			$str = str_replace($rLetters, $redLetters, $str);
			$str = preg_replace($rTags, $redTags, $str);
			$str = str_replace($text1, $replaced1, $str);

			return $str;
		}
		if($type == 's') {
			// Short
			$str = str_replace('<br />', ' ', $str);
			$str = strip_tags($str);
			$replaceShort = array('&#92;', '&apos;', "\r\n\r\n");
			$replacedShort = array('\\', "'", " ");
			$rShortContent = array('|^(.*<style).*(</style>.*)$|', '|^(.*<script).*(</script>.*)$|');
			$redShortContent = array("$1 type=text/css> $2", "$1 type=text/javascript> $2");
			$str = str_replace($replaceShort, $replacedShort, $str);
			$str = preg_replace($rShortContent, $redShortContent, $str);
			//$str = strip_tags($str);
		}
		return $str;
	}

}
