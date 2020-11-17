<?php
if (!defined('VALIDACCESS')) {
	exit();
}

class Page extends PageBase {

	public $Styles	= [ '/resources/comments.css', '/resources/log.css' ];
	public $Scripts	= [ '/resources/comments.js', '/resources/log.js' ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return 'Test';
			break;
		}
	}

	public function __construct() {

	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$this->extension("comments", "Test");
	}
}
?>