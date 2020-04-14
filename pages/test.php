<?php
if (!defined('VALIDACCESS')) {
	exit();
}
/*function file_exists_ci($file) {
    if (file_exists($file))
		return $file;
/*
	$browse = glob(dirname($file) . '/*', GLOB_NOSORT);
	var_dump($browse);
    /*foreach ($browse as $compare)
        if (strtolower($file) === strtolower($compare))
            return $compare;

    return false;*
}*/

class Page extends PageBase {

	public $Styles	= [ '/css/site.css', '/resources/comments.css', '/resources/log.css' ];
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

		$str = 'w42-h60';
		$str = 'h60';
		$arr = [];
		preg_match('/(?:(w)([0-9]+))?(?:-?(h)([0-9]+))?/', $str, $arr);
		var_dump(array_filter($arr));

		#echo bin2hex(random_bytes(32));#password_hash('Yuko', PASSWORD_DEFAULT);

		#echo $_SESSION['user'] . '<br />' . $_SESSION['user_id'];
	}
}
?>