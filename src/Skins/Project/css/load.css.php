<?php
    header( 'Content-Type: text/css' );

	require_once '../../../getdata.php';

	// ------
	$nav = $dbc->query("SELECT * FROM pages WHERE url = 'Sys:CSS:NavigationPanel/project' LIMIT 1");
	$nav = $nav->fetch();

	echo (!empty( $nav )) ? $nav['content'] : '';
?>