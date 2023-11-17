<?php
header( 'Content-Type: application/javascript' );

require_once( 'getdata.php' );

if (!empty( $Wiki['custom']['thru_page']['js'] ) && $Wiki['custom']['thru_page']['js']) {
	$Page = $dbc->prepare( 'SELECT content FROM pages WHERE url = :page LIMIT 1' );
	$Page->execute([
		':page' => $Wiki['custom']['thru_page']['js']
	]);
	$Page = $Page->fetch();

	if ($Page)
		echo $Page['content'];
	else
		echo '/* ' . "\r\n * " . $Wiki['name']['wiki-name'] . ' allows its users to edit public JS via the page "' . $Wiki['custom']['thru_page']['js'] .
		'", ' . "\r\n * " . 'which does not exist at the moment. Create it to apply styles to the website or ' . "\r\n * " . 'set $Wiki[\'custom\'][\'thru_page\'][\'js\'] to '.
		'false in the Config.php to disable the ' . "\r\n * " . 'public JS via page edit function. ' . "\r\n" . ' */';
}
?>