<?php
    header( 'Content-Type: application/javascript' );

	require_once '../../getdata.php';

	// ------
	$nav = $dbc->query("SELECT * FROM pages WHERE url = 'Sys:C:NavigationPanel/project' LIMIT 1");
	$nav = $nav->fetch();
	$var = $dbc->query("SELECT * FROM pages WHERE url = 'Sys:JS:Variables' LIMIT 1");
	$var = $var->fetch();
?>
/* DATA */
var username = '<?php echo $User; ?>';

/* VARIABLES */
<?php echo (!empty( $var )) ? $var['content'] : ''; ?>

/* CONTENT */
var wPanelNavC = '<?php echo (!empty( $nav )) ? str_replace( "'", "\'", $nav['content'] ) : ''; ?>';