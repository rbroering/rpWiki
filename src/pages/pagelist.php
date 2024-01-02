<?php

class Page extends PageBase {
	public $Styles	= [];
	public $Scripts	= [];

	private $ShownCols = [];
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

		$this->ShownCols = $Actor->hasPermission('pagelist-view-details') ?
			['id', 'rid', 'url', 'pagetitle', 'disptitle', 'type', 'creator', 'hidden', 'properties'] :
			['url', 'pagetitle', 'disptitle', 'type', 'creator', 'hidden'];

		$cols = implode(',', $this->ShownCols);

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

		$pages = $dbc->prepare( "SELECT $cols FROM pages ORDER BY $sortkey $sortorder LIMIT $limit" );
		$pages->execute();
		$this->PageList = array_filter($pages->fetchAll(PDO::FETCH_NAMED), function($page) use ($Actor) {
			if (empty($page['hidden'])) return true;

			$options = json_decode($page['properties'] ?? '{}', true);
			$options = $options['hide_options'] ?? [];

			if (!in_array('hide_in_log', $options)) return true;

			$match = array_intersect(explode(',', $page['hidden']), $Actor->listGroups());

			return !empty($match);
		});
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (!$Actor->hasPermission('pagelist-view')) {
			msg('pagelist-nopermission-view');
			return false;
		}

		$HTML = new HTMLTags();
		$HTML->setPrintMode(false);
		$HTML->setAutoNl(true);
		$HTML->setAutoIndent(true);
		echo $HTML->divId(tag_attributes: [
			'style' => 'max-width: 100%; overflow-x: scroll;'
		], tag_inner: 
			$HTML->tableFrom2dArray([
				$this->ShownCols,
				...array_map(function($page) {
					return array_map(fn($key, $val) => match($key) {
						default => $val,
						'url' => al($val, 'page', ['?' => $val]),
						'creator' => al($val, 'user', ['?' => $val]),
					}, array_keys($page), $page);
				}, $this->PageList),
			], table_indexes: true)
		);
	}
}
?>
