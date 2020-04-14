<?php
if (!defined('VALIDACCESS')) {
	exit();
}

class Page extends PageBase {
	public $Styles = [ '/css/site.css' ];

	public function msg( $str ) {
		switch ($str) {
			#case 'pagetitle':
			#return 'Home';
			#break;
			default:
			return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract($GlobalVariables);

		$Startpage = (isset( $Wiki['config']['startpage'] )) ? $Wiki['config']['startpage'] : 'Home';

		$PageContent = $dbc->prepare("SELECT * FROM pages WHERE url = :Startpage AND type = :HomePageType LIMIT 1");
		$PageContent->execute(array(':Startpage' => $Startpage, ':HomePageType' => ''));
		$PageContent = $PageContent->fetch();

		$PageContent = $PageContent['content'];
		echo prcon( $PageContent );
	}
}