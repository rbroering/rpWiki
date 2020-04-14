<?php
	header( 'Content-Type: application/javascript' );

	require_once( 'getdata.php' );
?>
var user = {
	'logged-in': <?php echo (empty( $User )) ? 0 : 1; ?>,
	'name': '<?php echo (!empty( $User )) ? htmlspecialchars( strip_tags( $User ) ) : 'Guest'; ?>',
	'groups': '<?php echo ur( '*' ); ?>'
}

var user_groups = {
<?php
	count( $Wiki['list-groups'] ) - 1;
	$i = 0;
	foreach( $Wiki['list-groups'] as $Group ) {
		$Str = (ur( $Group )) ? "true" : "false";
		$Divider = ($i == count( $Wiki['list-groups'] ) - 1) ? '' : ',';
		echo "\t$Group: " . $Str . "$Divider\r\n";
		$i++;
	}
?>
}