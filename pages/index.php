<?php
if (!defined('VALIDACCESS')) {
	exit(-1);
}

class Page extends PageBase {
	public $Styles = [ '/css/site.css' ];

	public function msg( $str ) {
		switch ($str) {
			default:
				return "";
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract($GlobalVariables);

		if (!isset($Wiki['config']['startpage'])) {
			?>
			No valid landing page was specified in the configuration yet.
			Please set $Wiki['config']['startpage'] in your Config.php to
			specify the wiki page that will be shown here.
			<?php

			return false;
		}

		$Startpage = $Wiki['config']['startpage'];
		$PageContent = $dbc->prepare("SELECT * FROM pages WHERE url = :Startpage AND type = :HomePageType LIMIT 1");
		$PageContent->execute(array(':Startpage' => $Startpage, ':HomePageType' => ''));
		$PageContent = $PageContent->fetch();
		
		if (!$PageContent) {
			?>
			The page that was set in $Wiki['config']['startpage'] in your 
			Config.php to be your landing page does not exist in the wiki. 
			Please create it or set a different landing page.
			<?php

			return false;
		}

		$PageContent = $PageContent['content'];
		echo prcon( $PageContent );
	}
}