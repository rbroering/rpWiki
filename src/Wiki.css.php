<?php
header( 'Content-Type: text/css' );

require_once( 'getdata.php' );

if (!empty( $Wiki['custom']['thru_page']['css'] ) && $Wiki['custom']['thru_page']['css']) {
	if (!empty( $_GET['url'] ) && isset( $Wiki['custom']['thru_page']['allow_css'] )) {
		if (is_bool( $Wiki['custom']['thru_page']['allow_css'] ) && $Wiki['custom']['thru_page']['allow_css'] === true)
			$css_page = $_GET['url'];
		elseIf (is_array( $Wiki['custom']['thru_page']['allow_css'] ))
			if (in_array( urldecode( $_GET['url'] ), $Wiki['custom']['thru_page']['allow_css'] ))
				$css_page = $_GET['url'];
			else
				$css_page = ''; # $Wiki['custom']['thru_page']['css'];
	} else
		$css_page = $Wiki['custom']['thru_page']['css'];

	if (!empty($css_page)) {
		$Page = $dbc->prepare( 'SELECT content FROM pages WHERE url = :page LIMIT 1' );
		$Page->execute([
			':page' => $css_page
		]);
		$Page = $Page->fetch();

		if ($Page)
			echo '/* CSS from page "' . $css_page . '" */' . "\r\n" . $Page['content'];
		else
			echo '/* ' . "\r\n * " . $Wiki['name']['wiki-name'] . ' allows its users to edit public CSS via the page "' . $css_page .
			'", ' . "\r\n * " . 'which does not exist at the moment. Create it to apply styles to the website or ' . "\r\n * " .
			'set $Wiki[\'custom\'][\'thru_page\'][\'css\'] to '.
			'false in the Config.php to disable the ' . "\r\n * " . 'public CSS via page edit function. ' . "\r\n" . ' */';
	} else {
		echo '/* CSS cannot be loaded from "' . $_GET['url'] .
		'" as it is not included in the list of accepted pages for CSS rendering. Contact ' . $Wiki['name']['wiki-name'] .
		' staff or administrators for more information. */';
	}
} else
	echo '/* This wiki is not set up for CSS rendering using ' . __FILE__ . '.php currently. For more information contact ' .
	$Wiki['name']['wiki-name'] . ' staff or administrators. */';
?>