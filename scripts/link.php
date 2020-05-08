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

			$praefix 		= 'url';
			$MainPraefixes	= [
				'rights'	=> 'user',
				'blog'		=> 'blog',
			];

			if (array_key_exists($page, $MainPraefixes))
				$praefix = $MainPraefixes[$page];

			if ($page === 'user' || $page === 'page') {
				// Special cases
				$short = '';
				switch ($page) {
					default:
					case 'page':
						$short = 'p';
						if (substr($tab, 0, 5) === 'User:' && !in_array($Wiki['config']['urlparam']['versionindex'], $params)) {
							$short = 'u';
							$tab = substr($tab, 5);
						}
					break;
					case 'user':
						$short = 'u';
					break;
				}
				$link .= $short . '/' . $tab;
			} else {
				// Normal page URLs with '?' param
				$link = $page;
				$link .= $paramchar . $praefix . '=' . $tab;
				$paramchar = '&';
			}
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
		$classes = '';

		// $settings
		if (is_array($settings)) {
			// Classes per $settings
			if (key_exists('classes', $settings) && (is_string($settings['classes']) || is_array($settings['classes']))) {
				if (is_string($settings['classes']))
					$settings['classes'] = [$settings['classes']];

				$classes = 'class="' . rtrim(implode(' ', $settings['classes'])) . '" ';
			}

			// $text is sys msg per $settings
			if (in_array('msg', $settings))
				$text = msg('link-' . $text, 1);
		}

		// RETURN
		return '<a href="' . fl( $page, $params, 0 ) . '" ' . $classes . '>' . $text . '</a>';
	}

}