<?php

class Page extends PageBase {
	private $Action;
	private $Buttons;
	private $Namespace;
	private $NamespaceSettings;
	private $Page;
	private $Version;
	private $Compare;

	public $Styles	= [ '/css/page.css', '/resources/comments.css', '/resources/log.css' ];
	public $Scripts	= [ '/resources/comments.js', '/resources/log.js' ];

	public function msg( $str ) {
		global $GlobalVariables;
		extract ($GlobalVariables);

		switch ($str) {
			case 'pagetitle':
				return ($this->Page) ? $this->Page['pagetitle'] : msg( 'page_nonexistent_title', 1 );
			break;
			case 'disptitle':
				return ($this->Page) ? $this->Page['disptitle'] : msg( 'page_nonexistent_title', 1 );
			break;
			case 'title-buttons':
				return $this->Buttons;
			break;
			case 'subtitle':
				if (!$this->Page) return false;

				$Date = $dbc->prepare("SELECT * FROM log WHERE page = :page AND type = 'createpage' ORDER BY timestamp LIMIT 1");
				$Date->execute([
					':page' => $this->Page['rid']
				]);
				$Date = $Date->fetch();

				$Subtitles = array_filter([
					($this->Namespace['Blog']) ? msg( 'blog-writtenby', 1, [al($this->Page['creator'], 'user', ['?' => $this->Page['creator']]), timestamp($Date['timestamp'], 1, 'dmy')]) : '',
					($this->Version && $this->Page['user-can-view']) ? msg( 'page_title_version', 1, $this->Version['rid'] ) : ''
				]);

				/* DOES NOT WORK */
				$m = '';
				foreach ($Subtitles as $i => $Subtitle) {
					$m .= $Subtitle;
					$m .= ($i === count( $Subtitles ) - 1 || count( $Subtitles ) === 1) ? '' : ' &bull; ';
				}
				/* ALTERNATIVELY */
				/*for ($i = 0; $i < count( $Subtitles ); $i++) {
					$m .= $Subtitles[$i];

					if ($i < count($Subtitles) - 1) {
						$m .= ' • ';
					}
				}*/
				return $m;
			break;
		}
	}

	public function __construct() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$this->Namespace = [
			'Blog'		=> false,
			'Help'		=> false,
			'System'	=> false,
			'User'		=> false
		];

		// FIND PAGE IN DATABASE TABLE pages
		if (!empty( $_GET[$Wiki['config']['urlparam']['pagename']] )) {
			$this->Page = $dbc->prepare( 'SELECT * FROM pages WHERE url = :url LIMIT 1' );
			$this->Page->execute([
				':url' => $_GET[$Wiki['config']['urlparam']['pagename']]
			]);
			$this->Page = $this->Page->fetch();
		}

		// GENERATE TITLE AND HEADING IF EMPTY
		if ($this->Page) {
			if (empty( $this->Page['pagetitle'] ))
				$this->Page['pagetitle'] = $this->Page['url'];
			if (empty( $this->Page['disptitle'] ))
				$this->Page['disptitle'] = $this->Page['pagetitle'];
		}

		// SET ACTION ON PAGE
		$this->Action = 'view';
		if (isset( $_GET[$Wiki['config']['urlparam']['versionindex']] ) && p( 'view-versions' ))
			$this->Action = 'version-index';
		if (!empty( $_GET[$Wiki['config']['urlparam']['pageversion']] ) && p( 'view-versions' ))
			$this->Action = 'version-view';
		if (!empty( $_GET[$Wiki['config']['urlparam']['pageversion']] ) && !empty( $_GET[$Wiki['config']['urlparam']['compare']] ) && p( 'compare-versions' ))
			$this->Action = 'version-compare';

		// GET VERSION LOG ENTRY IF REQUESTED IN URL
		$this->Version = false;
		if ($this->Action == 'version-view' || $this->Action == 'version-compare') {
			$this->Version = $dbc->prepare( 'SELECT * FROM log WHERE page = :page AND rid = :id ORDER BY timestamp DESC LIMIT 1' );
			$this->Version->execute([
				':page'	=> $this->Page['rid'],
				':id'	=> $_GET[$Wiki['config']['urlparam']['pageversion']]
			]);
			$this->Version = $this->Version->fetch();
			if (empty( $this->Version ))
				$this->Action = 'view';
		}
		if ($this->Action == 'version-compare') {
			$this->Compare = $dbc->prepare( 'SELECT * FROM log WHERE page = :page AND rid = :id ORDER BY timestamp DESC LIMIT 1' );
			$this->Compare->execute([
				':page'	=> $this->Page['rid'],
				':id'	=> $_GET[$Wiki['config']['urlparam']['compare']]
			]);
			$this->Compare = $this->Compare->fetch();
			if (empty( $this->Compare ) || $this->Compare['timestamp'] == $this->Version['timestamp'])
				$this->Action = 'version-view';
			else {
				if ($this->Version['timestamp'] > $this->Compare['timestamp']) {
					$switch			= $this->Version;
					$this->Version	= $this->Compare;
					$this->Compare	= $switch;
				}
			}
		}

		$this->NamespaceSettings = false;
		if ($this->Page) {
			// GET THE NAMESPACE OF THE PAGE
			foreach ($Wiki['namespace'] as $Namespace => $Features) {
				$Prefixes = array();
				if (is_string( $Features['prefix'] ))
					array_push( $Prefixes, $Features['prefix'] );
				else
					$Prefixes = $Features['prefix'];

				foreach ($Prefixes as $Prefix) {
					if (strtolower( substr( $this->Page['url'], 0, strlen( $Prefix ) + 1 ) ) == strtolower( $Prefix ) . ':') {
						foreach (array_keys( $this->Namespace ) as $DefinedNamespace) {
							if ($Namespace == strtolower( $DefinedNamespace )) {
								$this->Namespace[$DefinedNamespace] = true;
								$this->NamespaceSettings = (!empty( $Features['page'] )) ? $Features['page'] : false;
								$this->Page['url-noprefix'] = substr( $this->Page['url'], strlen( $Prefix ) + 1, strlen( $this->Page['url'] ) - strlen( $Prefix ) - 1 );
							}
						}
					}
				}
			}

			// DOES USER HAVE PERMISSION TO VIEW PAGE?
			$this->Page['user-can-view'] = true;
			if (!empty( $this->Page['hidden'] )) {
				$AllowedGroups = explode( ',', $this->Page['hidden'] );
				if (!empty( $AllowedGroups )) {
					$this->Page['user-can-view'] = false;

					foreach ($AllowedGroups as $Group) {
						if (ur($Group)) {
							$this->Page['user-can-view'] = true;
							break;
						}
					}
				}
			}

			// DOES USER HAVE PERMISSION TO EDIT GIVEN NAMESPACE?
			$this->Page['user-can-edit'] = (p( 'p-edit' )) ? true : false;
			foreach (array_keys( $Wiki['namespace'] ) as $Namespace) {
				foreach (array_keys( $this->Namespace ) as $DefinedNamespace) {
					if (strtolower( $Namespace ) == strtolower( $DefinedNamespace )) {
						if ($this->Namespace[$DefinedNamespace]) {
							// SPECIAL CASES
							$Own = null;
							if ($this->Namespace['User'])
								$Own = $this->Page['url-noprefix'];

							if ((!$this->Page && !p( 'create-ns-' . strtolower( $Namespace ) )) || ($this->Page && !p( 'edit-ns-' . strtolower( $Namespace ), $Own )))
								$this->Page['user-can-edit'] = false;
						}
					}
				}
			}

			// SET WHICH BUTTONS ARE DISPLAYED NEXT TO HEADING
			switch ($this->Action) {
				default:
				case 'view':
					if ($this->Page['user-can-edit'])
						$this->Buttons['edit'] = [
							'label'	=>  msg( 'page_edit_text', 1 ),
							'title' => msg( 'page_edit_ph', 1 ),
							'link'	=> fl( 'editor', ['?' => $this->Page['url']] ),
							'class'	=> 'editorlink'
						];
					if (p( 'view-versions' ))
						$this->Buttons['versionindex'] = [
							'label'	=> msg( 'page_versions_text', 1 ),
							'title' => msg( 'page_versions_ph', 1 ),
							'link'	=> fl( 'page', ['?' => $this->Page['url'], $Wiki['config']['urlparam']['versionindex']] ),
							'class'	=> 'versionindex'
						];
					if (p( 'p-protect' ))
						$this->Buttons['protect'] = [
							'label'	=> msg( 'protect', 1 ),
							'title' => msg( 'protect', 1 ),
							'link'	=> fl( 'editor', ['?' => $this->Page['url'], $Wiki['config']['urlparam']['action'] => 'protect'] ),
							'class'	=> 'protect'
						];
					if (p( 'p-hide' ))
						$this->Buttons['hide'] = [
							'label'	=> msg( 'hide', 1 ),
							'title' => msg( 'hide', 1 ),
							'link'	=> fl( 'editor', ['?' => $this->Page['url'], $Wiki['config']['urlparam']['action'] => 'hide'] ),
							'class'	=> 'hide'
						];
					if (p( 'p-rename' ))
						$this->Buttons['rename'] = [
							'label'	=> msg( 'rename', 1 ),
							'title' => msg( 'rename', 1 ),
							'link'	=> fl( 'editor', ['?' => $this->Page['url'], $Wiki['config']['urlparam']['action'] => 'rename'] ),
							'class'	=> 'rename'
						];
				break;
				case 'version-index':
				case 'version-view':
				case 'version-compare':
					if ($this->Page['user-can-view'] || $this->Action == 'version-index')
						$this->Buttons['back'] = [
							'label'	=> msg( 'page_back_text', 1 ),
							'title' => msg( 'page_back_ph', 1 ),
							'link'	=> fl( 'page', ['?' => $this->Page['url']] ),
							'class'	=> 'editorlink'
						];
					else
						$this->Buttons['versionindex'] = [
							'label'	=> msg( 'page_versions_text', 1 ),
							'title' => msg( 'page_versions_ph', 1 ),
							'link'	=> fl( 'page', ['?' => $this->Page['url'], $Wiki['config']['urlparam']['versionindex']] ),
							'class'	=> 'versionindex'
						];
				break;
			}
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (($this->Page && $this->Page['user-can-view']) || ($this->Page && $this->Action == 'version-index')) {
			switch ($this->Action) {
				default:
				case 'view':
	?>
	<div class="pagecontenttext" style="width: 100%; display: inline-block;" >
		<?php
		if (!$this->Namespace['System'])
			echo prcon( $this->Page['content'] );
		else
			echo '<div style="font-family: monospace;" >' . str_replace( ["\r\n ", "  ", "\r\n"], ["\r\n&nbsp;", "&nbsp;&nbsp;", "<br />\r\n"], $this->Page['content'] ) . '</div>';
		?>
	</div>
<?php
					if ($this->Page['allowcomments'] && (!$this->NamespaceSettings || !key_exists( 'comments', $this->NamespaceSettings ) || $this->NamespaceSettings['comments']))
						$this->extension( 'comments', ['page' => $this->Page['url']] );
				break;
				case 'version-index':
					$this->extension( 'log', [
						'pages' => $this->Page['rid'],
						'types'	=> [
							'createpage',
							'editpage',
							'rename',
							'hide',
							'unhide',
							'protect',
							'unprotect'
						],
						'count-versions' => true,
						'show-version-link' => $this->Page['user-can-view'],
						'show-version-edit-link' => ($this->Page['user-can-edit'] && $this->Page['user-can-view'])
					]);
				break;
				case 'version-view':
					if ($this->Version)
						echo prcon( $this->Version['new'] );
				break;
				/*
				case 'version-compare':
				break;
				*/
			}
		} elseIf ($this->Page && !$this->Page['user-can-view']) {
			$HiddenBy = $dbc->prepare('SELECT username FROM log WHERE page = :page AND type = :type ORDER BY id DESC');
			$HiddenBy->execute([
				':page' => $this->Page['rid'],
				':type' => 'hide'
			]);
			$HiddenBy = $HiddenBy->fetch()['username'];

			msg('page-hiddenby', al($HiddenBy, 'user', ['?' => $HiddenBy]));
		} else {
			?>
<div id="page_DetailsBox" >
						<?php
						if (!empty( $_GET[$Wiki['config']['urlparam']['pagename']] ))
							$Params = [
								'?'			=> $_GET[$Wiki['config']['urlparam']['pagename']],
								'action'	=> 'create'
							];
						else
							$Params = [
								'action'	=> 'create'
							];
						msg( 'page_not-found_box', fl( 'editor', $Params ) );
					?>

					</div><?php
		}
	}
}
?>