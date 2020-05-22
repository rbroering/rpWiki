<?php

if (!function_exists( 'prcon' )) {
	// PROCESSING CONTENT
	function prcon ( $str, $type = 1 ) {
		if ($type == 'p' || $type == 1) {
			/* HEADINGS */
			$str = "\r\n" . $str;
			$str = preg_replace_callback(
			#[ '/(?:\r\n)(={2,6})(.+)\1/' ],
			[ '/(?:\r\n[\s]*)(={2,6})(.+)\1/' ],
			function( $match ) {
				return "\r\n" . '<h' . substr_count( $match[1], '=' ) . ' class="sectiontitle" >' . $match[2] . '</h' . substr_count( $match[1], '=' ) . '>';
			},
			$str
			);
			/* https://codereview.stackexchange.com/questions/9255/bulleted-list-from-dashes */
			/* UNORDERED LISTS */
			$str = preg_replace_callback(
				'/(^\s*\* (.*)$\r*\n*)+/m',
				function( $match ) {
					return '<ul>' . preg_replace('/^\s*\* (.*)$/m', '<li>$1</li>', $match[0]) . '</ul>';
				},
				$str
			);
			/* ORDERED LISTS */
			$str = preg_replace_callback(
				'/(^\s*\# (.*)$\r*\n*)+/m',
				function( $match ) {
					return '<ol>' . preg_replace('/^\s*\# (.*)$/m', '<li>$1</li>', $match[0]) . '</ol>';
				},
				$str
			);
			/* LINKS */
			$str = preg_replace_callback(
			[ '/\[\[([^\[\]\r\n]+)(?:\|([^\[\]\r\n]+))?\]\]/U' ],
			function( $match ) {
				global $dbc;

				$Page = $dbc->prepare( "SELECT COUNT(id) FROM pages WHERE url = :name LIMIT 1" );
				$Page->execute([
					':name' => $match[1]
				]);
				$Page = $Page->fetch();

				if (empty( $match[2] ))
					$match[2] = $match[1];

				if ($Page[0] == 1)
					return al( $match[2], 'page', ['?' => $match[1]], 1 );
				else
					return al( $match[2], 'editor', ['?' => $match[1]], ['classes' => ['editlink']] );
			},
			$str
			);
			/* BOLD AND ITALIC */
			$str = preg_replace_callback(
			[ '/\'\'\'(.+)\'\'\'/U', '/\'\'(.+)\'\'/U' ],
			function( $match ) {
				$code = '';
				if (substr( $match[0], 0, 3 ) === "'''" && substr( $match[0], -3, 3 ) === "'''") {
					$code = '<b>' . $match[1];
					if (substr( $match[0], 0, 5 ) != "'''''")
						$code .= '</b>';
				} else
					$code .= '<i>' . $match[1] . '</i>';
				if (substr( $match[0], 0, 5 ) == "'''''" && substr( $match[0], -3, 3 ) == "'''")
					$code .= '</b>';
				return $code;
			},
			$str
			);
			/* TEMPLATES */
			$str = preg_replace_callback(
			[ '/(\{)?\{\{([^{}\|\r\n]+)(?:(?:[\r\n\s]*)\|([\w\W]+))?\}\}(\})?/U' ],
			function( $match ) {
				global $dbc;

				if ($match[1] !== "{" && (!key_exists(5, $match) || $match[5] !== "}")) {
					$Template = $dbc->prepare( "SELECT * FROM pages WHERE url = :name LIMIT 1" );
					$Template->execute([
						':name' => $match[2]
					]);
					$Template = $Template->fetch();

					if (empty($match[3]))
						$match[3] = '{{{1}}}';

					if ($Template) {
						$replacements = explode('|', $match[3]);
						foreach ($replacements as $i => $val) {
							$Template['content'] = str_replace( '{{{' . ($i + 1) . '}}}', $val, $Template['content'] );
						}
						$Template['content'] = preg_replace('/\<noinclude\>([\w\W]*)\<\/noinclude\>/U', '', $Template['content']);
						$Template['content'] = preg_replace('/\<includeonly\>([\w\W]*)\<\/includeonly\>/U', '$1', $Template['content']);
						return str_replace("\r\n", "", prcon( $Template['content'] ));
					} else
						return 'Missing template: ' . $match[2];
				} else
					return $match[1] . '{{' . $match[2] . '}}';
			},
			$str
			);
			$str = preg_replace('/\<noinclude\>([\w\W]*)\<\/noinclude\>/U', '$1', $str);
			$str = preg_replace('/\<includeonly\>([\w\W]*)\<\/includeonly\>/U', '', $str);
			/* <hr /> */
			$str = preg_replace( '/\r\n\s*-{4}\r\n/', "\r\n<hr />\r\n", $str );
			/* <br /> */
			$strparts = preg_split('/(\<(script|style).*\>[\w\W]*\<\/\g2\>)/Ui', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
			array_push($strparts, '');
			foreach ($strparts as $i => $val) {
				$strblock = false;
					
				if (!key_exists(($i + 1), $strparts) || ($strparts[$i + 1] != 'style' && $strparts[$i + 1] != 'script' && ($val == 'style' || $val == 'script'))) {
					unset($strparts[$i]);
					$strblock = true;
				}

				if (!$strblock) {
					if (!key_exists(($i + 1), $strparts) || ($strparts[$i + 1] != 'style' && $strparts[$i + 1] != 'script'))
						$strparts[$i] = str_replace("\r\n\r\n", '<br /><br />', $val);
				}
			}
			#var_dump($strparts);
			$str = implode('', $strparts);
			return $str;
		}
		if ($type == 's') {
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
		if ($type == 'msg') {
			/* <br /> */
			$str = str_replace("\r\n\r\n", '<br /><br />', $str);
			/* https://codereview.stackexchange.com/questions/9255/bulleted-list-from-dashes */
			/* UNORDERED LISTS */
			$str = preg_replace_callback(
				'/(^\s*\* (.*)$\r*\n*)+/m',
				function( $match ) {
					return '<ul>' . preg_replace('/^\s*\* (.*)$/m', '<li>$1</li>', $match[0]) . '</ul>';
				},
				$str
			);
			/* ORDERED LISTS */
			$str = preg_replace_callback(
				'/(^\s*\# (.*)$\r*\n*)+/m',
				function( $match ) {
					return '<ol>' . preg_replace('/^\s*\# (.*)$/m', '<li>$1</li>', $match[0]) . '</ol>';
				},
				$str
			);
			/* LINKS */
			$str = preg_replace_callback(
			[ '/\[\[([^\[\]\r\n]+)(?:\|([^\[\]\r\n]+))?\]\]/U' ],
			function( $match ) {
				global $dbc;

				$Page = $dbc->prepare( "SELECT COUNT(id) FROM pages WHERE url = :name LIMIT 1" );
				$Page->execute([
					':name' => $match[1]
				]);
				$Page = $Page->fetch();

				if (empty( $match[2] ))
					$match[2] = $match[1];

				if ($Page[0] == 1)
					return al( $match[2], 'page', ['?' => $match[1]], 1 );
				else
					return al( $match[2], 'editor', ['?' => $match[1]], ['classes' => ['editlink']] );
			},
			$str
			);
			/* BOLD AND ITALIC */
			$str = preg_replace_callback(
			[ '/\'\'\'(.+)\'\'\'/U', '/\'\'(.+)\'\'/U' ],
			function( $match ) {
				$code = '';
				if (substr( $match[0], 0, 3 ) === "'''" && substr( $match[0], -3, 3 ) === "'''") {
					$code = '<b>' . $match[1];
					if (substr( $match[0], 0, 5 ) != "'''''")
						$code .= '</b>';
				} else
					$code .= '<i>' . $match[1] . '</i>';
				if (substr( $match[0], 0, 5 ) == "'''''" && substr( $match[0], -3, 3 ) == "'''")
					$code .= '</b>';
				return $code;
			},
			$str
			);

			return $str;
		}

		return $str;
	}
}