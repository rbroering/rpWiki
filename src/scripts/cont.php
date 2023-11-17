<?php

$prcon_template_environment_total = 0;

if (!function_exists( 'prcon' )) {
	// PROCESSING CONTENT
	function prcon ( $str, $type = 1, $template_environment = [] ) {
		if ($type == 'p' || $type == 1) {
			/* HEADINGS */
			$str = "\r\n" . $str;
			$str = preg_replace_callback(
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
				[
					'/({{2,3})(?:([^\n]+?)\n*)(\|.+?)?(}{2,3})/msu'
				],
				function($match) use ($template_environment) {
					global $dbc, $prcon_template_environment_total;
					
					$is_parameter = strlen($match[1]) + strlen($match[4]) === 2 * 3;
					$is_template = !$is_parameter;

					$full = $match[0];
					$name = rtrim($match[2]);
					$inside = $match[3];

					foreach([
						'container_depth', // Limit how deep a transclusion may get
						'container_templates', // Array of templates containing the current template
						'templates_total', // Count how many templates have been in use yet for the current page load
					] as $key) {
						if (!array_key_exists($key, $template_environment)) {
							switch ($key) {
								default:
									$template_environment[$key] = [];
								break;
								case 'container_depth':
								case 'templates_total':
									$template_environment[$key] = 0;
								break;
							}
						}
					}
					$template_environment['templates_total'] = $prcon_template_environment_total;

					if ($is_template) {
						// Template includes itself or is included through one another template or more
						if (in_array($name, $template_environment['container_templates'])) {
							return '<span class="template-exception template-exception-loop" >'.
								msg('prcon-templates-error-loop', 1,
									implode(msg('prcon-templates-error-loop-separator', 1), array_map(
										fn ($str) => "<span>$str</span>",
										$template_environment['container_templates']))).
								'</span><br>';
						}

						// Template container depth is too big
						if ($template_environment['container_depth'] > 10) {
							return '<span class="template-exception template-exception-depth" >'.
								msg('prcon-templates-error-depth', 1,
									'<span>'. $template_environment['container_depth'] - 1 .'</span>').
								'</span><br>';
						}

						// Total number of used templates is too big
						if ($template_environment['templates_total'] > 100) {
							return '<span class="template-exception template-exception-total" >'.
								msg('prcon-templates-error-total', 1,
									'<span>'. $template_environment['templates_total'] - 1 .'</span>').
								'</span><br>';
						}

						// Fetch template page content
						$content = $dbc->prepare( "SELECT content FROM pages WHERE url = :name LIMIT 1" );
						$content->execute([
							':name' => $name
						]);
						$content = $content->fetch();

						if ($content) {
							$content = $content['content'];

							$replacements = explode('|', $inside);

							foreach ($replacements as $i => $val) {
								$content = str_replace('{{{' . ($i) . '}}}', $val, $content);
							}

							$content = preg_replace('/\<noinclude\>([\w\W]*)\<\/noinclude\>/U', '', $content);
							$content = preg_replace('/\<includeonly\>([\w\W]*)\<\/includeonly\>/U', '$1', $content);

							$container_templates = array_merge($template_environment['container_templates'], [$name]);
							$new_template_environment = [
								'container_depth' => count($container_templates) + 1,
								'container_templates' => $container_templates,
								'templates_total' => $template_environment['templates_total'] + 1,
							];
							$prcon_template_environment_total++;

							return str_replace("\r\n", "", prcon($content, 1, $new_template_environment));
						} else {
							return '<span class="template-exception template-exception-missing" >'.
								msg('prcon-templates-error-missing', 1, "<span>$name</span>").
								'</span><br>';
						}
					} else {
						if ($inside) {
							return $inside;
						} else {
							return $full;
						}
					}
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
