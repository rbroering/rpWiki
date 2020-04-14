<?php
	error_reporting(0);

    header('Content-Type: text/css');

	include '../../../settings.php';

    session_start();

    $pageconnection = mysql_connect($MysqlURL, $MysqlUser, $MysqlPw) or die ('');
    mysql_select_db($DbName) or die ('');

	$LogoHeight = 30;
	$LogoWidth = 340;
	$LogoMargin = "5px 10px";
	$HeaderRightMinus = 20;

	if(isset($CSSName)) { $gCSS = mysql_query("SELECT * FROM pages WHERE url = '$CSSName' ORDER BY id DESC LIMIT 1"); }
?>


/* SITE CSS */
<?php
	if(isset($gCSS) && mysql_num_rows($gCSS) > 0) {
			$sitecss = str_replace(array('&apos;', 'url(\''), array("'", 'url(\'../../../../css/'), mysql_fetch_object($gCSS)->content);
			echo str_replace('url(\'../../../../css/http://', 'url(\'http://', $sitecss);
		}

	if(isset($Logo) && $Logo != null) {
?>
/* GENERATED CSS */
header#header #logo {
	<?php if(isset($LogoHeight)) { ?>height: <?php echo $LogoHeight; ?>px !important;<?php } ?>
	<?php if(isset($LogoWidth)) { ?>width: <?php echo $LogoWidth; ?>px !important;<?php } ?>
	<?php if(isset($LogoMargin)) { ?>margin: <?php echo $LogoMargin; ?> !important;<?php } ?>
    background: url('../../../../../<?php echo $ImgPath . $Logo; ?>') no-repeat 0% 0% / 100% 100% !important;
}
<?php
	if(isset($LogoWidth) && $LogoWidth != null) {
?>
header#header nav#nav {
    width: calc(100% - <?php echo $LogoWidth; ?>px - <?php echo $HeaderRightMinus; ?>px);
}
<?php
	}
	}
?>