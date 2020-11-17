<?php
if (!defined('VALIDACCESS')) {
	exit();
}

require_once("modules/new_comments.php");
require_once("modules/comments_list.php");
require_once("modules/replies.php");
require_once("modules/replies_list.php");

class Page extends PageBase {

	public $Styles	= [ '/resources/new_comments.css', '/resources/log.css' ];
	public $Scripts	= [ '/resources/new_comments.js', '/resources/log.js' ];

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

		?>
		<div style="border: 2px solid brown; border-radius: 10px; padding: 10px; margin: 10px auto 30px; width: calc(100% - 44px);" >
		Normal text <b>Bold text</b> <strong>Strong text</strong> <em>Emphasized text</em> <small>Small text</small> <i>Italic text</i>
		<u>Underlined text</u> <s>Strikethrough text</s> <b><i>Bold and italic text</i></b> <span>Span</span>
		<span style="color: brown;" >Span with red text</span>
		</div>
		<?php

		$CL = new CommentsList("Test");
		$CL->insert();
	}
}
?>