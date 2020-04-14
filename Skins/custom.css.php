<?php
header( 'Content-Type: text/css' );

require_once( '../getdata.php' );

if (!empty( $Wiki['custom']['logo']['url'] ) && file_exists( '../' . $Wiki['custom']['logo']['url'] )) {

	$ImgHeight		= (isset( $Wiki['custom']['logo']['height'] )) ? $Wiki['custom']['logo']['height'] : 30;
	$ImgDimensions	= getimagesize( '../' . $Wiki['custom']['logo']['url'] );
	$ImgRatio		= $ImgDimensions[0] / $ImgDimensions[1];
	$ImgWidth		= (isset( $Wiki['custom']['logo']['width'] )) ? $Wiki['custom']['logo']['width'] : $ImgHeight * $ImgRatio;

	/* Reduced size */
	$ImgR			= (!empty( $Wiki['custom']['logo-small']['url'] ) && file_exists( '../' . $Wiki['custom']['logo-small']['url'] )) ? $Wiki['custom']['logo-small']['url'] : $Wiki['custom']['logo']['url'];
	$ImgDimensionsR	= getimagesize( '../' . $ImgR );
	$ImgRatioR		= $ImgDimensionsR[0] / $ImgDimensionsR[1];
	$ImgWidthR		= (isset( $Wiki['custom']['logo-small']['width'] )) ? $Wiki['custom']['logo-small']['width'] : 35;
	$ImgHeightR		= (isset( $Wiki['custom']['logo-small']['height'] )) ? $Wiki['custom']['logo-small']['height'] : round( $ImgWidthR / $ImgRatioR );

?>/* GENERATED CSS */
header#header #logo {
	height: <?php
		echo $ImgHeight;
	?>px;
	width: <?php
		echo $ImgWidth;
	?>px;
	margin: <?php
		$HeaderHeight = (!empty( $Skin['custom']['logo']['url'] )) ? $Skin['custom']['logo']['url'] : 50;
		echo ($HeaderHeight - $ImgHeight) / 2;
	?>px 15px;
	background: url('../<?php echo $Wiki['custom']['logo']['url'] ?>') no-repeat 0% 0% / 100% 100% !important;
}

@media (max-width: 720px) {
	#header .toggleNav .toggle {
		margin: 15px 20px !important;
	}
	#userIconHeader {
		height: 38px !important;
		width: 38px !important;
		margin: 6px 20px 6px 24px !important;
	}
	#userLinks .loggedin .symbol.open {
		margin: 18px 0px 15px -18px !important;
	}

	header#header #logo {
		height: <?php
		echo $ImgHeightR;
	?>px;
		width: <?php
		echo $ImgWidthR;
	?>px;
		margin: <?php
		echo ($HeaderHeight - $ImgHeightR) / 2;
	?>px 0;<?php
		if ($ImgR != $Wiki['custom']['logo']['url']) {
	?>

		background: url('../<?php echo $ImgR; ?>') no-repeat 0% 0% / 100% 100% !important;<?php
		}
	?>

		position: absolute;
		left: calc(50% - <?php
			echo $ImgWidthR / 2;
		?>px);
	}
}<?php

}