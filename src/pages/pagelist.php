<?php

class Page extends PageBase {
	public $Styles	= [];
	public $Scripts	= [];

	private $PageList = [];

	public function msg( $str ) {
		global $GlobalVariables;
		extract ($GlobalVariables);

		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-pagelist', 1 );
		}
	}

	public function __construct() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$sortkey = match($_GET['sortdir'] ?? $_GET['skey'] ?? null) {
			default => 'url',
			'creation' => 'id',
			'pagetitle' => 'pagetitle,url',
			'disptitle' => 'disptitle,pagetitle,url',
		};

		$sortorder = match($_GET['sortdirection'] ?? $_GET['sdir'] ?? null) {
			default => 'ASC',
			'asc', 'ascending' => 'ASC',
			'desc', 'descending' => 'DESCENDING',
		};

		$cap = 100;
		$limit = $_GET['limit'] ?? 0;
		$limit = is_integer($limit) && 0 < $limit && $limit <= $cap ?: $cap;

		$pages = $dbc->prepare( "SELECT id, rid, url, pagetitle, disptitle, type, creator, hidden, properties FROM pages ORDER BY $sortkey $sortorder LIMIT $limit" );
		$pages->execute();
		$this->PageList = $pages->fetchAll(PDO::FETCH_NAMED);
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (!$Actor->hasPermission('pagelist-view')) {
			msg('pagelist-nopermission-view');
			return false;
		}

		$cols = ['id', 'rid', 'url', 'pagetitle', 'disptitle', 'type', 'creator', 'hidden', 'properties'];

		$HTML = new HTMLTags();
		$HTML->setPrintMode(true);
		$HTML->setAutoNl(true);
		$HTML->setAutoIndent(true);
		$HTML->tableFrom2dArray([
			$cols,
			...array_map(function($page) {
				return array_map(fn($key, $val) => match($key) {
					default => $val,
					'url' => al($val, 'page', ['?' => $val]),
				}, array_keys($page), $page);
			}, $this->PageList),
		], table_indexes: true);
	}
}
?>
