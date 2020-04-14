<?php
class Page extends PageBase {
	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
			    return msg( 'pt-logout', 1 );
			break;
			default:
			    return '';
			break;
		}
	}

	public function __construct() {
        global $GlobalImport;
        extract($GlobalImport);

		$Actor->logOut();
		$this->redirect('default:home');
	}

	public function insert() {
		msg('logout', 0);
	}
}