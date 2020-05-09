<?php

class WikiPage {
	private $Page		= [ 'exists' => false, 'unset' => true ];
	private $MsgError	= '';
	public $Namespace	= [];

	final public function __construct() {
		// Defaults
		$this->Page = [
			'exists'	=> false,
			'title'		=> '',
			'heading'	=> '',
			'content'	=> '',
			'comments'	=> true
		];
	}

	final public function set_page( $id_name ) {
		if (!empty( $id_name )) {
			global $GlobalImport;
			extract( $GlobalImport );

			$this->Page['unset'] = false;

			$Data = $dbc->prepare( 'SELECT * FROM pages WHERE LOWER(url) = LOWER(:id_name) LIMIT 1' );
			$Data->execute([
				':id_name' => $id_name
			]);
			$Data = $Data->fetch();

			if (!empty( $Data )) {
				$this->Page = [
					'exists'	=> true,
					'id_name'	=> $id_name,
					'id_rand'	=> $Data['rid'],
					'title'		=> $Data['pagetitle'],
					'heading'	=> $Data['disptitle'],
					'content'	=> $Data['content'],
					'creator'	=> $Data['creator'],
					'protect'	=> $Data['protect'],
					'comments'	=> $Data['allowcomments'],
					'hidden'	=> $Data['hidden'],
					'properties'=> $Data['properties']
				];

				$this->Page['protecttext']	= $this->Page['protect'];
				$this->Page['hiddentext']	= $this->Page['hidden'];
				if (empty($this->Page['title'])) $this->Page['title'] = $this->Page['id_name'];
				if (empty($this->Page['heading'])) $this->Page['heading'] = $this->Page['title'];

				if (!empty( $this->Page['hidden'] )) {
					if (is_string( $this->Page['hidden'] ))
						$this->Page['hidden'] = protectlayer($this->Page['hidden']);
					if (is_string( $this->Page['hidden'] ))
						$this->Page['hidden'] = explode(',', $this->Page['hidden']);
				} else
					$this->Page['hidden'] = [];

				if (!empty( $this->Page['protect'] )) {
					if (is_string( $this->Page['protect'] ))
						$this->Page['protect'] = protectlayer($this->Page['protect']);
					if (is_string( $this->Page['protect'] ))
						$this->Page['protect'] = explode(',', $this->Page['protect']);
				} else
					$this->Page['protect'] = [];

				// CHECK FOR NAMESPACES SET IN Config.php
				$this->Page['id_noprefix'] = $this->Page['id_name'];

				if (empty( $this->Namespace ))
					$this->Namespace = $Wiki['namespace'];

				foreach ($Wiki['namespace'] as $Namespace => $Features) {
					$Prefixes = array();

					if (is_string( $Features['prefix'] ))
						array_push( $Prefixes, $Features['prefix'] );
					else
						$Prefixes = $Features['prefix'];

					foreach ($Prefixes as $Prefix)
						if (strtolower( substr( $this->Page['id_name'], 0, strlen( $Prefix ) + 1 ) ) == strtolower( $Prefix ) . ':')
							foreach (array_keys( $this->Namespace ) as $DefinedNamespace)
								if ($Namespace == strtolower( $DefinedNamespace )) {
									$this->Namespace[$DefinedNamespace] = true;
									$this->Page['namespace']	= $Namespace;
									$this->Page['id_noprefix']	= substr( $this->Page['id_name'], strlen( $Prefix ) + 1, strlen( $this->Page['id_name'] ) - strlen( $Prefix ) - 1 );
								}
				}
			} else
				$this->Page = [
					'exists'	=> false,
					'id_name'	=> $id_name
				];

			return true;
		} else
			return false;
	}

	final public function exists() {
		return $this->Page['exists'];
	}

	final public function get_namespace() {
		if (!empty( $this->Page['namespace'] ))
			return $this->Page['namespace'];
		else
			return false;
	}

	public function user_can_read() {
		if (!empty( $this->Page['protect'] )) {
			$InGroup = 0;
			foreach ($this->Page['hidden'] as $Group)
				if (ur( $Group ))
					$InGroup++;

			if ($InGroup === 0) {
				foreach ($this->Page['hidden'] as $i => $Group)
					$this->Page['hidden'][$i] = msg('group-' . $Group, 1);

				$this->MsgError = msg( 'editor-permission-protection', 1, [ rtrim(implode( ', ', $this->Page['protect'] ), ', ') ] );
				return false;
			}
		}
	}

	public function user_can_edit( $text = false ) {
		global $GlobalImport;
		extract( $GlobalImport );

		if ($text)
			if (!empty($this->MsgError))
				return $this->MsgError;
			else
				return true;

		$this->MsgError = '';

		// Check whether user is allowed to edit pages at all
		if (p( 'p-edit' )) {
			if (p('suppress-protection'))
				return true;

			// Check whether user is allowed to edit pages in that namespace
			if ($this->get_namespace()) {
				$Namespace	= $Wiki['namespace'][$this->get_namespace()];
				$InGroup	= 0;
				if (!empty( $Namespace['groups'] ) && (is_array( $Namespace['groups'] ) || is_string( $Namespace['groups'] ))) {
					if (is_string( $Namespace['groups'] ))
						$Namespace['groups'] = explode( ',', $Namespace['groups'] );

					foreach ($Namespace['groups'] as $Group)
						if (ur( $Group ))
							$InGroup++;
					if ($InGroup === 0) {
						$this->MsgError = msg( 'editor-permission-ns', 1 );
						return false;
					}
				}
			}

			// Check if page is protected and user is in allowed groups
			if ($this->exists() && !empty( $this->Page['protect'] )) {

				$InGroup = 0;
				foreach ($this->Page['protect'] as $Group)
					if (ur( $Group ))
						$InGroup++;

				if ($InGroup === 0) {
					foreach ($this->Page['protect'] as $i => $Group)
						$this->Page['protect'][$i] = msg('group-' . $Group, 1);

					$this->MsgError = msg( 'editor-permission-protection', 1, [ rtrim(implode( ', ', $this->Page['protect'] ), ', ') ] );
					return false;
				}
			}

			return true;
		} else {
			$this->MsgError = msg( 'editor-permission', 1 );
			return false;
		}

		if ($text && !empty($this->MsgError))
			return $this->MsgError;
	}

	final public function data($index) {
		return $this->Page[$index];
	}

	public function link() {
		return al($this->data('heading'), 'page', ['?' => $this->Page['id_name']]);
	}
}

class Page extends PageBase {
	protected $Editor;
	private $Action;
	private $ActionCurrent;
	private $Page;
	private $WP;
	private $Version;
	public $ShowMessage;
	private $Storage;

	public $Styles = [
		'/css/editor.css',
		'libraries/codemirror/lib/codemirror.css',
		'/resources/log.css'
	];
	public $Scripts = [
		'libraries/require.js/require.js',
		'/js/editor.js'
	];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-editor', 1 );
				break;
			case 'notification':
				if (p( 'p-edit' ))
					if (!isset( $this->Editor['prevent'] ) || !$this->Editor['prevent']) {
						$Messages = array();

						if (!$this->Page['exists'])
							array_push( $Messages, [
								'class'	=> 'important',
								'msg'	=> msg( 'editor-createpage', 1 )
							]);
						if (isset( $_POST['pagecontent'] ) && empty( $_POST['pagecontent'] ) && !$this->Namespace['CSS'] && !$this->Namespace['JS'])
							array_push( $Messages, [
								'class'	=> 'important',
								'msg'	=> msg( 'editor-unfilled', 1 )
							]);
						if (!$this->Version['current'])
							array_push( $Messages, [
								'class'	=> 'important',
								'msg'	=> msg( 'editor-oldversion-edit', 1 )
							]);

						return $Messages;
					} else
						return false;
				break;
			default:
				return '';
				break;
		}
	}

	public function __construct() {
		$this->WP = new WikiPage();

		if (!empty($_GET['url']))
			$this->WP->set_page($_GET['url']);

		// SET PARAMETERS
		$this->Param = [
			'action'	=> 'action',
			'css'		=> 'css',
			'js'		=> 'js',
			'url'		=> 'url',
			'version'	=> 'ver'
		];

		// SET VARIABLES
		$this->Action	= [
			'create'	=> false,
			'edit'		=> false,
			'hide'		=> false,
			'protect'	=> false,
			'rename'	=> false
		];
		$this->Editor	= [
			'AutoNote'	=> ''
		];
		$this->Elements = [
			'comments'	=> true,
			'note'		=> true,
			'titles'	=> true,
			'url'		=> true
		];
		$this->Namespace = [
			'w'			=> false,
			'Blog'		=> false,
			'CSS'		=> false,
			'Help'		=> false,
			'JS'		=> false,
			'System'	=> false,
			'User'		=> false
		];
		$this->Page		= [
			'comments'	=> true,
			'content'	=> '',
			'disptitle'	=> '',
			'exists'	=> '',
			'id'		=> '',
			'pagetitle'	=> '',
			'url'		=> '',
			'version'	=> ''
		];
		$this->Version = [
			'current'	=> true,
			'exists'	=> false,
			'new'		=> '',
			'old'		=> '',
			'rid'		=> '',
			'timestamp'	=> ''
		];

		// IMPORT GlobalVariables
		global $GlobalVariables;
		extract( $GlobalVariables );

		// GET NAMESPACE
		if (isset( $_GET[$this->Param['css']] )) {
			$this->Namespace['CSS']	= true;
			$this->Editor['source']	= 'user';

			if (p( 'edit-strange-css' ) && !empty( $_GET[$this->Param['url']] ))
				$this->Page['url'] = $_GET[$this->Param['url']];
			else
				$this->Page['url'] = $User;
		}
		if (isset( $_GET[$this->Param['js']] )) {
			$this->Namespace['JS']	= true;
			$this->Editor['source']	= 'user';

			if (p( 'edit-strange-js' ) && !empty( $_GET[$this->Param['url']] ))
				$this->Page['url'] = $_GET[$this->Param['url']];
			else
				$this->Page['url'] = $User;
		}

		// CHECK FOR PAGE SET IN URL
		if (!empty( $_GET[$this->Param['url']] ))
			if (!isset( $this->Page['url'] ) || empty( $this->Page['url'] ))
				$this->Page['url'] = $_GET[$this->Param['url']];
			else
				$this->Page['url'] = (empty( $_GET[$this->Param['url']] )) ? '' : $_GET[$this->Param['url']];

		if (!empty( $this->Page['url'] )) {
			if (!isset( $this->Editor['source'] )) {
				$Data = $dbc->prepare( "SELECT * FROM pages WHERE url = :url LIMIT 1" );
				$Data->execute([
					':url' => $this->Page['url']
				]);
			} else
				switch ($this->Editor['source']) {
					case 'user':
						$Data = $dbc->prepare( "SELECT * FROM user WHERE username = :username LIMIT 1" );
						$Data->execute([
							':username' => $this->Page['url']
						]);
						break;
				}

			$Data = $Data->fetch();

			$this->Page['exists']	= ($Data) ? true : false;
			$this->Action['edit']	= ($Data) ? true : false;
			$this->Action['create']	= ($Data) ? false : true;
		} else {
			$this->Page['exists']	= false;
			$this->Action['create']	= true;
		}

		// CHECK FOR NAMESPACES SET IN Config.php
		$this->Page['url-noprefix'] = $this->Page['url'];

		foreach ($Wiki['namespace'] as $Namespace => $Features) {
			$Prefixes = array();
			if (is_string( $Features['prefix'] ))
				array_push( $Prefixes, $Features['prefix'] );
			else
				$Prefixes = $Features['prefix'];

			$this->CurrentNamespace = '';
			foreach ($Prefixes as $Prefix)
				if (strtolower( substr( $this->Page['url'], 0, strlen( $Prefix ) + 1 ) ) == strtolower( $Prefix ) . ':')
					foreach (array_keys( $this->Namespace ) as $DefinedNamespace)
						if ($Namespace == strtolower( $DefinedNamespace )) {
							$this->Namespace[$DefinedNamespace] = true;
							$this->CurrentNamespace = $DefinedNamespace;
							$this->Page['url-noprefix'] = substr( $this->Page['url'], strlen( $Prefix ) + 1, strlen( $this->Page['url'] ) - strlen( $Prefix ) - 1 );
						}
		}

		// GET INFORMATION ON PAGE
		if ($this->Page['exists'] && !isset( $this->Editor['source'] )) {
			$this->Page['comments']		= ($Data['allowcomments']) ? true : false;
			$this->Page['content']		= $Data['content'];
			$this->Page['disptitle']	= $Data['disptitle'];
			$this->Page['id']			= $Data['rid'];
			$this->Page['pagetitle']	= $Data['pagetitle'];
		}

		if (($this->Namespace['CSS'] || $this->Namespace['JS']) && $this->Page['exists'])
			$this->Page['content'] = ($this->Namespace['CSS']) ? $Data['css'] : $Data['js'];

		if (isset( $this->Editor['source'] ) && !$this->Page['exists']) {
			$this->Editor['prevent']			= true;
			$this->Editor['prevent-message']	= msg( 'editor-user-not-found', 1 );
		}

		// GET PAGE VERSION
		if (!empty( $_GET[$this->Param['version']] )) {
			$Current = $dbc->prepare( "SELECT rid, username, timestamp FROM log WHERE page = :page AND type IN ('createpage', 'editpage') ORDER BY timestamp DESC LIMIT 1" );
			$Current->execute([
				':page' => $this->Page['id']
			]);
			$Current = $Current->fetch();

			$Log = $dbc->prepare( "SELECT timestamp, old, new, username FROM log WHERE page = :page AND rid = :rid AND type IN ('createpage', 'editpage') ORDER BY id DESC LIMIT 1" );
			$Log->execute([
				':page' => $this->Page['id'],
				':rid'	=> $_GET[$this->Param['version']]
			]);
			$Log = $Log->fetch();

			$LogBetween = $dbc->prepare( "SELECT id FROM log WHERE page = :page AND type IN ('createpage', 'editpage') AND timestamp > :lowerLimit AND timestamp < :upperLimit ORDER BY id DESC" );
			$LogBetween->execute([
				':page'			=> $this->Page['id'],
				':lowerLimit'	=> $Log['timestamp'],
				':upperLimit'	=> $Current['timestamp']
			]);
			$LogBetween = $LogBetween->rowCount();

			$this->Version['exists'] = ($Log) ? true : false;
		}

		// GET INFORMATION ON VERSION
		if ($this->Version['exists']) {
			$this->Version['id']		= $_GET[$this->Param['version']];
			$this->Version['timestamp']	= $Log['timestamp'];
			$this->Version['old']		= $Log['old'];
			$this->Version['new']		= $Log['new'];
			$this->Version['user']		= $Log['username'];
		}

		if ($this->Version['exists'])
			if ($this->Version['id'] != $Current['rid'])
				$this->Version['current'] = false;

		// OVERRIDE PAGE INFORMATION WITH VERSION INFORMATION
		if (!$this->Version['current']) {
			$this->Page['content']		= $this->Version['new'];
			$this->Editor['AutoNote']	= 'Rollback from version "'. $Current['rid'] .'" by '.
				$Current['username'] .' to version "'. $this->Version['id'] .'" by '.
				$this->Version['user'] . " with $LogBetween versions in between";
		}

		// GET ACTION
		if (!empty( $_GET[$this->Param['action']] ) && $this->Action['create'] == false) # when a page does not exist, it can not be protected/renamed/etc.
			switch ($_GET[$this->Param['action']]) {
				default:
				case 'edit':
					$this->Action['edit']		= true;
				break;
				case 'hide':
					$this->Action['hide']		= true;
				break;
				case 'protect':
					$this->Action['protect']	= true;
				break;
				case 'rename':
					$this->Action['rename']		= true;
				break;
			}
		elseIf ($this->Action['create'] == false)
			$this->Action['edit'] = true;

		foreach ($this->Action as $Action => $Current)
			if ($Current)
				$this->ActionCurrent = $Action;

		// HIDE ELEMENTS WHEN UNUSABLE
		if ($this->Namespace['CSS'] || $this->Namespace['JS']) {
			$this->Elements['comments'] = false;
			$this->Elements['titles']	= false;
			if ($this->Namespace['CSS'])
				if (!p( 'edit-strange-css' ))
					$this->Elements['url']		= false;
			elseIf ($this->Namespace['JS'])
				if (!p( 'edit-strange-js' ))
					$this->Elements['url']		= false;
		}
		if (!empty( $this->CurrentNamespace ))
			if (key_exists( 'page', $Wiki['namespace'][$this->CurrentNamespace])) {
				$NamespaceSettings = $Wiki['namespace'][strtolower( $this->CurrentNamespace )]['page'];
				if (key_exists( 'comments', $NamespaceSettings ) && is_bool( $NamespaceSettings['comments'] ))
					$this->Elements['comments'] = $NamespaceSettings['comments'];
				if (key_exists( 'customtitle', $NamespaceSettings ) && is_bool( $NamespaceSettings['customtitle'] ))
					$this->Elements['titles'] = $NamespaceSettings['customtitle'];
			}

		// DOES USER HAVE PERMISSION TO EDIT GIVEN NAMESPACE?
		foreach (array_keys( $Wiki['namespace'] ) as $Namespace) {
			foreach (array_keys( $this->Namespace ) as $DefinedNamespace) {
				if (strtolower( $Namespace ) == strtolower( $DefinedNamespace )) {
					if ($this->Namespace[$DefinedNamespace]) {
						// SPECIAL CASES
						$Own = null;
						if ($this->Namespace['User'])
							$Own = $this->Page['url-noprefix'];

						if ((!$this->Page['exists'] && !p( 'create-ns-' . strtolower( $Namespace ) )) || ($this->Page['exists'] && !p( 'edit-ns-' . strtolower( $Namespace ), $Own ))) {
							$this->Editor['prevent']			= true;
							$this->Editor['prevent-message']	= msg( 'editor-permission-ns', 1 );
						}
					}
				}
			}
		}

		// CHANGING VISIBILITY OF EDITOR FORM ELEMENTS
		if ($this->Namespace['System'])
			$this->Elements['comments']	= false;
		if ($this->Namespace['User']) {
			$this->Elements['comments']	= false;
			$this->Elements['titles']	= false;
		}
	}

	private function formEditor() {
		?>
<form method="post" id="editorForm" >
	<input type="hidden" name="send" /><!-- -->

	<div id="editorHeadings" >
		<!-- CHECKBOX ALLOWCOMMENTS -->
		<?php
		if ($this->Elements['comments'])
			$this->__insertCheckbox([
				'allowComments' => [
					'checked'	=> $this->Page['comments'],
					'label'		=> msg( 'editor-lbl-allowcomments', 1 )
				]
			]);
		if ($this->Elements['titles']) {
		?><!--

		TEXTINPUT PAGETITLE --><input type="text" name="pagetitle" class="big-input input-editor" maxlength="30" placeholder="<?php msg('editor-ph-pagetitle') ?>" autocomplete="off" <?php echo 'value="'. $this->Page['pagetitle'] .'" ';; ?>/><!--

		TEXTINPUT DISPTITLE --><input type="text" name="disptitle" class="big-input input-editor" maxlength="80" placeholder="<?php msg('editor-ph-disptitle') ?>" autocomplete="off" <?php echo 'value="'. $this->Page['disptitle'] .'" ';; ?>/><?php
		}
		if ($this->Elements['url']) {
			?><!--

		TEXTINPUT URL --><input type="text" name="pageurl" class="big-input input-editor" maxlength="50" placeholder="<?php msg('editor-ph-id_name'); ?>" autocomplete="off" <?php if (!empty( $this->Page['url'] )) echo 'value="'. $this->Page['url'] .'" '; ?><?php
		if ($this->Action['edit']) echo ' disabled="disabled" '; ?>/><?php
		}
		?>
	</div>
	<br clear="both" />
	<div id="editorTextarea" class="top10" >
		<textarea id="Editor" name="pagecontent" class="big-textarea full-textarea bg1-textarea top10" onkeyup="noticeUndo()" ><?php echo $this->Page['content']; ?></textarea>
	</div>
	<?php if ($this->Elements['note']) { ?>
		<textarea id="editComment" name="editcomment" class="big-textarea full-textarea top10 Arial" style="min-height: 80px; height: 80px; max-height: 180px; resize: vertical;" placeholder="<?php msg('editor-ph-notice') ?>" maxlength="200" ><?php echo $this->Editor['AutoNote']; ?></textarea>
	<?php } ?>
	<input type="submit" class="big-submit submit-login top10" value="<?php msg('editor-btn-submit') ?>" />
</form>
		<?php
	}

	private function actionEdit() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$Input['comments']		= (isset( $_POST['allowComments'] )) ? true : false;
		$Input['content']		= $_POST['pagecontent'];
		$Input['pagetitle']		= (!empty( $_POST['pagetitle'] )) ? $_POST['pagetitle'] : $this->Page['pagetitle'];
		$Input['disptitle']		= (!empty( $_POST['disptitle'] )) ? $_POST['disptitle'] : $this->Page['disptitle'];
		$Input['editcomment']	= (!empty( $_POST['editcomment'] )) ? $_POST['editcomment'] : $this->Editor['AutoNote'];
		if ($this->Action['create']) {
			$Input['url']		= $_POST['pageurl'];
			$this->Page['url']	= $Input['url'];
		} else
			$Input['url']		= $this->Page['url'];

		if ($this->Page['exists']) { # check for changes
			$Test = $dbc->prepare( "SELECT * FROM pages WHERE rid = :id LIMIT 1" );
			$Test->execute( [':id' => $this->Page['id']] );
			$Test = $Test->fetch();

			if (($Input['pagetitle'] != $Test['pagetitle'] || $Input['disptitle'] != $Test['disptitle'] || $Input['content'] != $Test['content'] || $Input['comments'] != $Test['allowcomments']))
				$this->Editor['changes'] = true;
			else
				$this->Editor['changes'] = false;
		} else
				$this->Editor['changes'] = true;

		if ($this->Action['create'])
			$this->Page['id'] = randID( 10, 'pages' );

		timestamp( 'GET' );

		if ($this->Editor['changes']) {
			$Log = $dbc->prepare( 'INSERT INTO log
				(username, page, pageURL, old, new, type, rid, autonote, notice, timestamp, timezone) VALUES
				(:username, :pageid, :pageurl, :oldpage, :newpage, :type, :id, :autonote, :editcomment, :timestamp, :timezone)
			' );
			$Log = $Log->execute([
				':username' 	=> $User,
				':pageid'		=> $this->Page['id'],
				':pageurl'		=> $this->Page['url'],
				':oldpage'		=> $this->Page['url'],
				':newpage'		=> $Input['content'],
				':type'			=> ($this->Action['edit']) ? 'editpage' : 'createpage',
				':id'			=> randID( 10, 'log' ),
				':autonote'		=> $this->Editor['AutoNote'],
				':editcomment'	=> $Input['editcomment'],
				':timestamp'	=> $timestamp,
				':timezone'		=> $timezone
			]);
		} else
			$Log = true;

		if ($Log) {
			if ($this->Action['edit']) {
				if (!$this->Namespace['CSS'] && !$this->Namespace['JS'])
					if ($this->Editor['changes']) {
						$Update = $dbc->prepare( "UPDATE pages SET content = :content, pagetitle = :pagetitle, disptitle = :disptitle, allowcomments = :allowcomments WHERE url = :url" );
						$Update = $Update->execute([
							':content'		=> $Input['content'],
							':pagetitle'	=> $Input['pagetitle'],
							':disptitle'	=> $Input['disptitle'],
							':allowcomments'=> $Input['comments'],
							':url'			=> $this->Page['url']
						]);
					} else
						$Update = true;
				else
					if ($this->Namespace['CSS']) {
						if (!p( 'edit-strange-css' ))
							$Input['url'] = $User;

						$Update = $dbc->prepare( "UPDATE user SET css = :content WHERE username = :username" );
						$Update = $Update->execute([
							':content'		=> $Input['content'],
							':username'		=> $Input['url']
						]);
					} elseIf ($this->Namespace['JS']) {
						if (!p( 'edit-strange-js' ))
							$Input['url'] = $User;

						$Update = $dbc->prepare( "UPDATE user SET js = :content WHERE username = :username" );
						$Update = $Update->execute([
							':content'		=> $Input['content'],
							':username'		=> $Input['url']
						]);
					}
			} elseIf ($this->Action['create']) {
				$Test = $dbc->prepare( "SELECT id FROM pages WHERE url = :url" );
				$Test->execute( [':url' => $Input['url']] );
				$Test = $Test->rowCount();

				if ($Test === 0) {;
					$Update = $dbc->prepare( "INSERT INTO pages
						(content, rid, url, pagetitle, disptitle, type, allowcomments, creator)
						VALUES
						(:content, :rid, :url, :pagetitle, :disptitle, :type, :allowcomments, :user)"
					);
					$Update = $Update->execute([
						':content'		=> $Input['content'],
						':rid'			=> $this->Page['id'],
						':url'			=> $this->Page['url'],
						':pagetitle'	=> $Input['pagetitle'],
						':disptitle'	=> $Input['disptitle'],
						':type'			=> '',
						':allowcomments'=> $Input['comments'],
						':user'			=> $User
					]);
				} else {
					$Update = false;
					msg( 'editor-error-page-double-save' );
				}
			}

			if ($Update)
				if (empty($Input['pagetitle']))
					$Input['pagetitle'] = $this->Page['url'];
				if (empty($Input['disptitle']))
					$Input['disptitle'] = $Input['pagetitle'];
				if ($this->Action['edit']) {
					msg( 'editor-success-edit', al($Input['disptitle'], 'page', ['?' => $this->Page['url']]) );
					#$this->redirect(fl('page', ['?' => $this->Page['url'], 'saved' => 'edit']));
				} elseIf ($this->Action['create']) {
					msg( 'editor-success-create', al($Input['disptitle'], 'page', ['?' => $this->Page['url']]) );
					#$this->redirect(fl('page', ['?' => $this->Page['url'], 'saved' => 'create']));
				}
			else
				msg( 'editor-error-page-inexistent-save' );
		} else {
			msg( 'editor-error' );
			msg( 'editor-error-log' );
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if ($this->WP->user_can_edit()) {
			switch ($this->ActionCurrent) {
			default:
			case 'create':
			case 'edit':
				// DOES USER HAVE PERMISSION TO EDIT?
				if (p( 'p-edit' )) {
					if (!isset( $this->Editor['prevent'] ) || !$this->Editor['prevent']) { // if has not been blocked by previous error that set this var to true, let user edit
						if ((!empty( $_POST['pagecontent'] ) && !empty( $this->Page['url'] )) ||
							(isset( $_POST['pagecontent'] ) && empty( $_POST['pagecontent'] ) && ($this->Namespace['CSS'] || $this->Namespace['JS'])) ||
							(!empty( $_POST['pageurl'] ) && !empty( $_POST['pagecontent'] ) && $this->Action['create'])) // whenever something is saved, do not show the editor form
							$this->actionEdit();
						elseIf (isset( $_POST['pagecontent'] ) && empty( $_POST['pagecontent'] ))
							$this->formEditor();
						else
							$this->formEditor();
					} else
						if (isset( $this->Editor['prevent-message'] ))
							echo $this->Editor['prevent-message'];
						else
							msg( 'editor-error' );
				} else
					// USER NOT ALLOWED TO EDIT
					msg( 'editor-permission' );
			break;
			case 'hide':
				if (p( 'p-hide' )) {
					$HideGroups = array_merge($Wiki['groups'], ['users' => $Wiki['autogroups']['users']]);

					$HideInLog = false;
					$Properties = $this->Storage['hide']['properties'] = json_decode($this->WP->data('properties'), true);
					if (!is_array($Properties)) $Properties = [];
					if (array_key_exists('hide_options', $Properties)) {
						if (in_array('hide_in_log', $Properties['hide_options'])) $HideInLog = true;
					}


					if (isset( $_POST['hide'] )) {
						// Properties
						$this->Storage['hide']['property-change'] = false; // for nochange test

						if (isset($_POST['hideoption_loghide'])) {
							/*if (!array_key_exists('hide_options', $Properties))
								$Properties['hide_options'] = [];
							if (!in_array('hide_in_log', $Properties['hide_options'])) {
								$Properties['hide_options'][] = 'hide_in_log';
								$this->Storage['hide']['property-change'] = true;
							}*/
							if (!$HideInLog) {
								$Properties['hide_options'][] = 'hide_in_log';
								$this->Storage['hide']['property-change'] = true;
							}
						} else {
							/*if (array_key_exists('hide_options', $Properties)) {
								if (($LogHideKey = array_search('hide_in_log', $Properties['hide_options'])) !== false) {
									unset($Properties['hide_options'][$LogHideKey]);
									$this->Storage['hide']['property-change'] = true;
								}

								if (empty($Properties['hide_options'])) unset($Properties['hide_options']);
							}*/
							if ($HideInLog) {
								$LogHideKey = array_search('hide_in_log', $Properties['hide_options']);
								unset( $Properties['hide_options'][$LogHideKey] );
								$this->Storage['hide']['property-change'] = true;
							}
						}

						$this->Storage['hide']['properties'] = json_encode($Properties);

						// Groups
						$this->Storage['hide']['groups'] = array();

						foreach ($HideGroups as $Groupname => $Group)
							if (isset($_POST['hide_' . $Groupname]))
								$this->Storage['hide']['groups'][] = $Groupname;

						$this->Storage['hide']['groupsarray']	= $this->Storage['hide']['groups'];
						$this->Storage['hide']['groups']		= implode(',', $this->Storage['hide']['groups']);

						$this->Storage['hide']['logtype']		= (empty($this->Storage['hide']['groups'])) ? 'unhide' : 'hide';

						if ($this->Storage['hide']['groupsarray'] != $this->WP->data('hidden') || $this->Storage['hide']['property-change']) {
							$Log = $dbc->prepare( 'INSERT INTO log
								(username, page, pageURL, old, new, type, rid, notice, timestamp, timezone) VALUES
								(:username, :pageid, :pageurl, :oldpage, :newpage, :type, :id, :editcomment, :timestamp, :timezone)
							' );
							$Log = $Log->execute([
								':username' 	=> $User,
								':pageid'		=> $this->WP->data('id_rand'),
								':pageurl'		=> $this->WP->data('id_name'),
								':oldpage'		=> $this->WP->data('hiddentext'),
								':newpage'		=> $this->Storage['hide']['groups'],
								':type'			=> $this->Storage['hide']['logtype'],
								':id'			=> randID( 10, 'log' ),
								':editcomment'	=> (!empty($_POST['editcomment'])) ? $_POST['editcomment'] : '',
								':timestamp'	=> $timestamp,
								':timezone'		=> $timezone
							]);

							if ($Log) {
								$Hide = $dbc->prepare('UPDATE pages SET hidden = :hidden, properties = :properties WHERE rid = :rid LIMIT 1');
								$Hide = $Hide->execute([
									':hidden'		=> $this->Storage['hide']['groups'],
									':properties'	=> $this->Storage['hide']['properties'],
									':rid'			=> $this->WP->data('id_rand')
								]);

								if ($Hide) {
									if ($this->Storage['hide']['logtype'] == 'unhide')
										msg('editor-success-unhide', $this->WP->link());
									else
										msg('editor-success-hide', $this->WP->link());
								} else
									msg('error');
							} else
								msg('error');
						} else
							msg('editor-nochange', $this->al_current_url('back', ['msg']));
					} else {
					// if ($this->Page) { open page creator instead
		?>
			<form method="post" id="getURLForm" >
				<input type="hidden" name="send" />
				<input type="hidden" name="hide" />
				<div class="editor_desc_box" ><?php
					msg( 'editor-desc-hide' );
				?></div>
				<div class="adviselabel" ><?php
					$Hidelevel = [];
					foreach ($this->WP->data('hidden') as $i => $val)
						$Hidelevel[$i] = msg('group-' . $val, 1);
					if (empty($Hidelevel)) msg('hide-status-none', $this->WP->link()); else {
						msg('hide-status', [implode(', ', $Hidelevel), $this->WP->link()]);

						$this->extension('log', [
							'limit' => 1,
							'pages' => $this->WP->data('id_rand'),
							'types' => ['hide', 'unhide']
						]);
					}
				?></div>
				<div class="checkbox-list" >
				<?php
					$checkBoxes = array();

					foreach ($HideGroups as $Groupname => $Group)
						$checkBoxes[$Groupname] = [
							'checked'	=> in_array($Groupname, $this->WP->data('hidden')),
							'label'		=> '<span>' . $Group['msg'] . '</span>',
							'name'		=> 'hide_' . $Groupname
						];
					$this->__insertCheckbox( $checkBoxes );
				?>
				</div>
				<div class="top40" >
					<div class="adviselabel" ><?php msg('editor-desc-hideoption-loghide'); ?></div>
					<?php
						$this->__insertCheckbox([[
							'checked'	=> $HideInLog,
							'label'		=> msg( 'editor-btn-option-loghide', 1 ),
							'name'		=> 'hideoption_loghide'
						]]);
					?>
				</div>
				<div>
					<textarea name="editcomment" class="big-textarea top50 Arial" placeholder="<?php msg( 'global-ph-reason' ); ?>" maxlength="200" ></textarea><br />
					<input type="submit" class="big-submit submit-login top20" value="<?php $msgstr = ($this->WP->data('hidden') === 'hidden') ? 'editor-btn-unhide' : 'editor-btn-hide'; msg( $msgstr ); ?>" />
				</div>
			</form>
		<?php
					/* } else
						msg( 'editor-page-nonexistent' ); */ // open page creator instead
					}
				} else
					msg( 'editor-permission-hide' );
			break;
			case 'protect':
				if (isset( $_POST['protect'] )) {
					if (p( 'p-protect-advanced' ) && !isset($_GET[$Wiki['config']['urlparam']['simpleprotect']])) {
						$this->Storage['protect']['groups'] = array();

						foreach ($Wiki['groups'] as $Groupname => $Group)
							if (isset($_POST['protect_' . $Groupname]))
								$this->Storage['protect']['groups'][] = $Groupname;

						$this->Storage['protect']['groupsarray']	= $this->Storage['protect']['groups'];
						$this->Storage['protect']['groups']			= implode(',', $this->Storage['protect']['groups']);

						$this->Storage['protect']['logtype']		= (empty($this->Storage['protect']['groups'])) ? 'unprotect' : 'protect';

						if ($this->Storage['protect']['groupsarray'] != $this->WP->data('protect')) {
						# if ($this->Storage['protect']['groups'] != $this->WP->data('protecttext')) {
							$Log = $dbc->prepare( 'INSERT INTO log
								(username, page, pageURL, old, new, type, rid, notice, timestamp, timezone) VALUES
								(:username, :pageid, :pageurl, :oldpage, :newpage, :type, :id, :editcomment, :timestamp, :timezone)
							' );
							$Log = $Log->execute([
								':username' 	=> $User,
								':pageid'		=> $this->WP->data('id_rand'),
								':pageurl'		=> $this->WP->data('id_name'),
								':oldpage'		=> $this->WP->data('protecttext'),
								':newpage'		=> $this->Storage['protect']['groups'],
								':type'			=> $this->Storage['protect']['logtype'],
								':id'			=> randID( 10, 'log' ),
								':editcomment'	=> (!empty($_POST['editcomment'])) ? $_POST['editcomment'] : '',
								':timestamp'	=> $timestamp,
								':timezone'		=> $timezone
							]);

							if ($Log) {
								$Protect = $dbc->prepare('UPDATE pages SET protect = :protect WHERE rid = :rid LIMIT 1');
								$Protect = $Protect->execute([
									':protect'	=> $this->Storage['protect']['groups'],
									':rid'		=> $this->WP->data('id_rand')
								]);

								if ($Protect) {
									if ($this->Storage['protect']['logtype'] == 'unprotect')
										msg('editor-success-unprotect', $this->WP->link());
									else
										msg('editor-success-protect', $this->WP->link());
								} else
									msg('error');
							} else
								msg('error');
						} else
							msg('editor-nochange', $this->al_current_url('back', ['msg']));
					} elseIf (p( 'p-protect' )
						&& !empty($_POST['protection'])
						&& !empty($Wiki['select-groups']['protection']))
					{
						if (substr($_POST['protection'], 0, 6) == 'option'
							&& is_numeric(substr($_POST['protection'], 6))
							&& intval(substr($_POST['protection'], 6)) > -2
							&& intval(substr($_POST['protection'], 6)) < count($Wiki['select-groups']['protection']))
						{
							$this->Storage['protect']['groups'] = '';
							$this->Storage['protect']['groups'] = '#' . substr($_POST['protection'], 6);

							$this->Storage['protect']['logtype']= ($this->Storage['protect']['groups'] === '#-1') ? 'unprotect' : 'protect';

							if (protectlayer($this->Storage['protect']['groups']) != $this->WP->data('protect')) {
								$Log = $dbc->prepare( 'INSERT INTO log
									(username, page, pageURL, old, new, type, rid, notice, timestamp, timezone) VALUES
									(:username, :pageid, :pageurl, :oldpage, :newpage, :type, :id, :editcomment, :timestamp, :timezone)
								' );
								$Log = $Log->execute([
									':username' 	=> $User,
									':pageid'		=> $this->WP->data('id_rand'),
									':pageurl'		=> $this->WP->data('id_name'),
									':oldpage'		=> $this->WP->data('protecttext'),
									':newpage'		=> ($this->Storage['protect']['groups'] === '#-1') ? '' : $this->Storage['protect']['groups'],
									':type'			=> $this->Storage['protect']['logtype'],
									':id'			=> randID( 10, 'log' ),
									':editcomment'	=> (!empty($_POST['editcomment'])) ? $_POST['editcomment'] : '',
									':timestamp'	=> $timestamp,
									':timezone'		=> $timezone
								]);

								if ($Log) {
									$Protect = $dbc->prepare('UPDATE pages SET protect = :protect WHERE rid = :rid LIMIT 1');
									$Protect = $Protect->execute([
										':protect' => ($this->Storage['protect']['groups'] === '#-1') ? '' : $this->Storage['protect']['groups'],
										':rid' => $this->WP->data('id_rand')
									]);

									if ($Protect) {
										if ($this->Storage['protect']['logtype'] == 'unprotect')
											msg('editor-success-protect', $this->WP->link());
										else
											msg('editor-success-unprotect', $this->WP->link());
									} else
										msg('error');
								} else
									msg('error');
							} else
								msg('editor-nochange', $this->al_current_url('back', ['msg']));
						} else // Options not correct
							msg('error');
					} else
						msg('error');
				} else {
					if (p( 'p-protect-advanced' ) && !isset($_GET[$Wiki['config']['urlparam']['simpleprotect']])) {
						echo al(
							'<button class="btm20" >' . msg('protect-link-simple', 1) . '</button>',
							'editor', ['?' => $this->WP->data('id_name'), $this->Param['action'] => 'protect', 'simpleprotect']);
		?>
			<form method="post" id="getURLForm" >
				<input type="hidden" name="send" />
				<input type="hidden" name="protect" />
				<div class="editor_desc_box" ><?php
					msg( 'editor-desc-protect-advanced' );
				?></div>
				<div class="adviselabel" ><?php
					$Protection = [];
					foreach ($this->WP->data('protect') as $i => $val)
						$Protection[$i] = msg('group-' . $val, 1);
					if (empty($Protection)) msg('protect-status-none', $this->WP->link()); else {
						msg('protect-status', [implode(', ', $Protection), $this->WP->link()]);

						$this->extension('log', [
							'limit' => 1,
							'pages' => $this->WP->data('id_rand'),
							'types' => 'protect'
						]);
					}
				?></div>
				<div class="checkbox-list" >
				<?php
					$checkBoxes = array();

					foreach ($Wiki['groups'] as $Groupname => $Group)
						$checkBoxes[$Groupname] = [
							'checked'	=> in_array($Groupname, $this->WP->data('protect')),
							'label'		=> '<span>' . $Group['msg'] . '</span>',
							'name'		=> 'protect_' . $Groupname
						];
					$this->__insertCheckbox( $checkBoxes );
				?>
				</div>
				<div>
					<textarea name="editcomment" class="big-textarea top50 Arial" placeholder="<?php msg( 'global-ph-reason' ); ?>" maxlength="200" ></textarea><br />
					<input type="submit" class="big-submit submit-login top20" value="<?php msg( 'editor-btn-protect' ); ?>" />
				</div>
			</form>
		<?php
				} elseIf (p( 'p-protect' )) {
					if (p( 'p-protect-advanced'))
						echo al(
							'<button class="btm20" >' . msg('protect-link-advanced', 1) . '</button>',
							'editor', ['?' => $this->WP->data('id_name'), $this->Param['action'] => 'protect']);
		?>
			<form method="post" id="getURLForm" >
				<input type="hidden" name="send" />
				<input type="hidden" name="protect" />
				<div class="editor_desc_box" ><?php
					msg( 'editor-desc-protect' );
				?></div>
				<div class="adviselabel" ><?php
					$Protection = [];
					foreach ($this->WP->data('protect') as $i => $val)
						$Protection[$i] = msg('group-' . $val, 1);
					if (empty($Protection)) msg('protect-status-none', $this->WP->link()); else {
						msg('protect-status', [implode(', ', $Protection), $this->WP->link()]);

						$this->extension('log', [
							'limit' => 1,
							'pages' => $this->WP->data('id_rand'),
							'types' => 'protect'
						]);
					}
				?></div>
				<select id="protectionSelect" name="protection" class="big-select font-inherit font-size-unset wide-select" >
					<option id="option_-1" value="option-1" ><?php msg('editor-protect-none'); ?></option>
		<?php
					foreach ($Wiki['select-groups']['protection'] as $Option => $Groups) {
						$selected = '';
						if ($Groups == $this->WP->data('protect'))
							$selected = 'selected="selected" ';
						echo '<option id="option_' . $Option . '" value="option' . $Option . '" ' . $selected . '>';
						echo listglue($Groups, ['groups']);
						echo '</option>';
					}
		?>
				</select>
				<div>
					<textarea name="editcomment" class="big-textarea top50 Arial" placeholder="<?php msg( 'global-ph-reason' ); ?>" maxlength="200" ></textarea><br />
					<input type="submit" class="big-submit submit-login top20" value="<?php msg( 'editor-btn-protect' ); ?>" />
				</div>
			</form>
		<?php
						
					} else
						msg( 'editor-permission-protect' );
					}
				break;
				case 'rename':
					if (p( 'p-rename' )) {
						if (isset($_POST['rename'])) {
							if (!empty($_POST['pageurl']) && $_POST['pageurl'] != $this->WP->data('id_name')) {
								// Page ready for rename
								$this->Storage['rename']['new'] = $_POST['pageurl'];

								$Log = $dbc->prepare( 'INSERT INTO log
									(username, page, pageURL, old, new, type, rid, notice, timestamp, timezone) VALUES
									(:username, :pageid, :pageurl, :oldpage, :newpage, :type, :id, :editcomment, :timestamp, :timezone)
								' );
								$Log = $Log->execute([
									':username' 	=> $User,
									':pageid'		=> $this->WP->data('id_rand'),
									':pageurl'		=> $this->WP->data('id_name'),
									':oldpage'		=> $this->WP->data('id_name'),
									':newpage'		=> $this->Storage['rename']['new'],
									':type'			=> 'rename',
									':id'			=> randID( 10, 'log' ),
									':editcomment'	=> (!empty($_POST['editcomment'])) ? $_POST['editcomment'] : '',
									':timestamp'	=> $timestamp,
									':timezone'		=> $timezone
								]);

								if ($Log) {
									$Rename = $dbc->prepare('UPDATE pages SET url = :new WHERE rid = :rid LIMIT 1');
									$Rename = $Rename->execute([
										':new' => $this->Storage['rename']['new'],
										':rid' => $this->WP->data('id_rand')
									]);

									if ($Rename) {
										msg('editor-success-rename', al($this->WP->data('heading'), 'page', ['?' => $this->Storage['rename']['new']]));
									} else
										msg('error');
								}
							} elseIf (empty($_POST['pageurl']))
								msg('rename-empty', $this->al_current_url('back', ['msg']));
							else
								msg('editor-nochange', $this->al_current_url('back', ['msg']));
						} else {
						?>
			<form method="post" class="getURLForm" >
				<input type="hidden" name="send" />
				<input type="hidden" name="rename" />
				<div class="editor_desc_box" ><?php
					msg( 'editor-desc-rename' );
				?></div>
				<div class="adviselabel btm30" ><?php
					msg( 'rename-label', $this->WP->link() );
				?></div>
				<div id="rename_old" class="input-label" ><?php msg('rename-old'); ?></div>
				<input type="text" class="big-input" disabled="disabled" value="<?php echo $this->WP->data('id_name'); ?>" />
				<div id="rename_new" class="input-label" ><?php msg('rename-new'); ?></div>
				<input type="text" name="pageurl" class="big-input" maxlength="50" placeholder="<?php msg('editor-ph-id_name'); ?>" autocomplete="off" <?php if (!empty( $this->Page['url'] )) echo 'value="'. $this->Page['url'] .'" '; ?>/><br />
				<textarea name="editcomment" class="big-textarea top50 Arial" placeholder="<?php msg( 'global-ph-reason' ); ?>" maxlength="200" ></textarea><br />
				<input type="submit" class="big-submit submit-login top20" value="<?php msg( 'editor-btn-rename' ); ?>" />
			</form>
						<?php
						}
					} else
						msg( 'editor-permission-rename' );
				break;
			}
		} else
			echo $this->WP->user_can_edit(true);
	}
}