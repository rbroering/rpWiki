<?php

if (!function_exists( 'fl' )) {

	// FLEXIBLE LINKS
	function fl ($page, $params = [], $encode = 0) {
		global $Wiki;
	
		$Rewrites = [
			'register'	=> 'signup',
			'site'		=> 'page'
		];

		if (array_key_exists($page, $Rewrites))
			$page = $Rewrites[$page];

		$link = '';
		$paramchar = '?';

		if ($encode === 1 || $encode === true) {
			foreach( $params as $key => $val ) {
				$params[$key] = urlencode( $val );
			}
		}

		$mainparams = [
			'user' => $Wiki['config']['urlparam']['user'],
			'page' => $Wiki['config']['urlparam']['pagename']
		];

		if (array_key_exists( $page, $mainparams )) {
			if (array_key_exists( $mainparams[$page], $params )) {
				$params['?'] = $params[$mainparams[$page]];
				unset( $params[$mainparams[$page]] );
			}
		}

		if (isset( $params['?'] )) {
			$tab = $params['?'];

			$prefix 		= 'url';
			$MainPrefixes	= [
				'rights'	=> 'user',
				'blog'		=> 'blog',
			];

			if (array_key_exists($page, $MainPrefixes))
				$prefix = $MainPrefixes[$page];

			// Special cases
			$short = '';
			switch ($page) {
				default:
					// Normal page URLs with '?' param
					$link = $page;
					$link .= $paramchar . $prefix . '=' . $tab;
					$paramchar = '&';
				break;
				case 'page':
					$short = 'p';

					if (substr($tab, 0, 5) === 'User:' && !in_array($Wiki['config']['urlparam']['versionindex'], $params)) {
						$short	= 'u';
						$tab	= substr($tab, 5);
					}
				break;
				case 'editor':
					if (array_key_exists('inwikilink', $Wiki['namespace'])) {
						foreach ($Wiki['namespace']['inwikilink']['prefix'] as $inWikiLinkPrefix) {
							$compare = ($encode === 1 || $encode === true) ? urlencode(strtolower($inWikiLinkPrefix) . ":") : strtolower($inWikiLinkPrefix) . ":";
							if (strtolower(substr($tab, 0, strlen($inWikiLinkPrefix) + 1)) == $compare) {
								$link	= substr($tab, strlen($inWikiLinkPrefix) + 1);
							}
						}
					}

					// Normal page URLs with '?' param
					if (empty($link)) {
						$link = $page;
						$link .= $paramchar . $prefix . '=' . $tab;
						$paramchar = '&';
					}
				break;
				case 'user':
					$short = 'u';
				break;
			}

			if (!empty($short)) $link .= $short . '/' . $tab;
		} else {
			$link = $page;
		}
		foreach($params as $i => $feLink) {
			if (empty( $feLink )) {
				$link .= $paramchar . $i;
				$paramchar = '&';
			} elseIf (is_int( $i )) {
				$link .= $paramchar . $feLink;
				$paramchar = '&';
			} elseIf (is_string( $i ) && $i != '?' && $i != '#') {
				$link .= $paramchar . $i . '=' . $feLink;
				$paramchar = '&';
			}
		}

		if (isset( $params['#'] )) {
			$link .= '#' . $params['#'];
		}
		return $link;
	}

}


if (!function_exists( 'al' )) {

	// <a> ELEMENTS WITH FLEXIBLE LINKS
	function al ($text, $page, $params = [], $settings = []) {
		$link		= fl($page, $params, 0);
		$classes	= '';

		// $settings
		if (is_array($settings)) {
			// Classes per $settings
			if (key_exists('classes', $settings) && (is_string($settings['classes']) || is_array($settings['classes']))) {
				if (is_string($settings['classes']))
					$settings['classes'] = explode(' ', $settings['classes']);

				if ($page === 'editor' && substr($link, 0, 6) !== 'editor') {
					$settings['classes'] = array_filter($settings['classes'], function ($val) {
						return $val != 'editlink';
					});
					$settings['classes'][] = 'in-wiki-link';
				}

				$classes = 'class="' . rtrim(implode(' ', $settings['classes'])) . '" ';
			}

			// $text is sys msg per $settings
			if (in_array('msg', $settings))
				$text = msg('link-' . $text, 1);
		}

		// RETURN
		return '<a href="' . $link . '" ' . $classes . '>' . $text . '</a>';
	}

}