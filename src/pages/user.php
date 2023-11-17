<?php

class Page extends PageBase {
	public $Styles	= [ '/css/user.css', '/resources/comments.css', '/resources/log.css' ];
	public $Scripts	= [ '/resources/comments.js', '/resources/log.js' ];
	private $Profile;
	private $Messages;

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
				return 'User profile';
			break;
			case 'notification':
				if (!empty( $this->Messages['hidden'] ))
					return [[
						'class'	=> 'important',
						'msg'	=> $this->Messages['hidden']
					]];
				else
					return false;
			break;
			default:
				return '';
			break;
		}
	}

	public function __construct() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$Wiki['config']['urlparam']['own-profile'] = (isset( $Wiki['config']['urlparam']['own-profile'] )) ? $Wiki['config']['urlparam']['own-profile'] : 'myprofile';

		if ((isset( $_GET[$Wiki['config']['urlparam']['user']] ) && !empty( $_GET[$Wiki['config']['urlparam']['user']] ) ) || isset( $_GET[$Wiki['config']['urlparam']['own-profile']] )) {
			if ( empty( $_GET[$Wiki['config']['urlparam']['user']] ) && isset( $_GET[$Wiki['config']['urlparam']['own-profile']] ))
				$this->Profile['name']	= $User;
			else
				$this->Profile['name']	= $_GET[$Wiki['config']['urlparam']['user']];
			$System['own-profile']	= ($User == $this->Profile['name']) ? true : false;
			$this->Profile['data']	= $dbc->prepare( 'SELECT id FROM user WHERE username = :profilename LIMIT 1' );
			$this->Profile['data']->execute([
				':profilename' => $this->Profile['name']
			]);
			$this->Profile['data']	= $this->Profile['data']->fetch();

			// If user exists
			if ($this->Profile['data']) {
				// If user page has been hidden and user is not allowed to see
				#echo (ur( 'hidden', $this->Profile['name'], 'types' )) ? 'ja' : 'nein';
				if (ur( 'hidden', $this->Profile['name'], true ) && p( 'view-hidden-user', $this->Profile['name'] )) {
					// If user page has been hidden and user is allowed to see
						$_Data['log']['hidden-by'] = $dbc->prepare( "SELECT username FROM log WHERE page = :profilepage AND type = 'hideuser' ORDER BY id DESC LIMIT 1" );
						$_Data['log']['hidden-by']->execute([
							':profilepage' => 'User:' . $this->Profile['name']
						]);
						$_Data['log']['hidden-by'] = $_Data['log']['hidden-by']->fetch();

						$this->Messages['hidden'] = msg( 'up-hiddenby', 1, $_Data['log']['hidden-by']['username'] );
				}
			} else
				$this->Profile['username'] = msg( 'error', 1 );
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$Wiki['config']['urlparam']['own-profile'] = (isset( $Wiki['config']['urlparam']['own-profile'] )) ? $Wiki['config']['urlparam']['own-profile'] : 'myprofile';

		if ((isset( $_GET[$Wiki['config']['urlparam']['user']] ) && !empty( $_GET[$Wiki['config']['urlparam']['user']] ) ) || isset( $_GET[$Wiki['config']['urlparam']['own-profile']] )) {
			if ( empty( $_GET[$Wiki['config']['urlparam']['user']] ) && isset( $_GET[$Wiki['config']['urlparam']['own-profile']] ))
				$this->Profile['name']	= $User;
			else
				$this->Profile['name']	= $_GET[$Wiki['config']['urlparam']['user']];
			$System['own-profile']	= ($User == $this->Profile['name']) ? 1 : 0;
			$this->Profile['data']	= $dbc->prepare( 'SELECT usericon FROM user WHERE username = :profilename LIMIT 1' );
			$this->Profile['data']->execute([
				':profilename' => $this->Profile['name']
			]);
			$this->Profile['data']	= $this->Profile['data']->fetch();

			// If user exists
			if ($this->Profile['data']) {
				// If user page has been hidden and user is not allowed to see
				#echo (ur( 'hidden', $this->Profile['name'], 'types' )) ? 'ja' : 'nein';
				if (ur( 'hidden', $this->Profile['name'], true ) && !p( 'view-hidden-user', $this->Profile['name'] ))
					msg( 'up-hidden' );
				else {
					// Get Data for About Header: Log activites, blogs, comments
					// Log
					unset( $Var );
					if (!$System['own-profile'])
						$System['log']['types'] = ' AND type IN (\'editpage\',\'protect\',\'rename\',\'usercss\',\'userjs\',\'writeblog\',\'editblog\')';
					elseIf ($System['own-profile'])
						$System['log']['types'] = ' AND type IN (\'editpage\',\'protect\',\'rename\',\'usercss\',\'userjs\',\'writeblog\',\'editblog\')';
					$this->Profile['about']['log'] = $dbc->prepare("SELECT * FROM log WHERE username = :username AND type IN ('editpage','protect','rename','usercss','userjs','writeblog','editblog') ORDER BY id DESC");
					$this->Profile['about']['log']->execute(array( ':username' => $this->Profile['name'] ));
					$this->Profile['about']['log'] = $this->Profile['about']['log']->fetchAll();

					// Created pages
					$this->Profile['about']['created-pages'] = $dbc->prepare("SELECT * FROM log WHERE username = :username AND type = 'createpage'");
					$this->Profile['about']['created-pages']->execute(array(':username' => $this->Profile['name']));
					$this->Profile['about']['created-pages'] = $this->Profile['about']['created-pages']->rowCount();

					// Blogs
					$this->Profile['blogs'] = $dbc->prepare("SELECT * FROM pages WHERE url LIKE(:url) AND creator = :username ORDER BY id DESC");
					$this->Profile['blogs']->execute([':username' => $this->Profile['name'], ':url' => 'Blog:%']);
					$this->Profile['about']['blogs'] = $this->Profile['blogs']->rowCount();
					$this->Profile['blogs'] = $this->Profile['blogs']->fetchAll();

					// Comments
					$this->Profile['about']['comments'] = $dbc->prepare("SELECT * FROM comments WHERE writer = :username ORDER BY id DESC");
					$this->Profile['about']['comments']->execute(array(':username' => $this->Profile['name']));
					$this->Profile['about']['comments'] = $this->Profile['about']['comments']->rowCount();

					// User page
					?>
<div id="userpageUsericon" style="background: url('<?php echo get_usericon($this->Profile['name']); ?>');" ></div>
					<div id="userpageHeader" >
						<div id="usernameHeader" >
							<div id="usernameUserrights" class="scrolllist" >
								<h1><?php echo $this->Profile['name']; ?></h1>
								<div id="userrights" >
								<?php
									foreach ($Wiki['list-groups'] as $Val) {
										if (ur( $Val, $this->Profile['name'] ) && (
											!key_exists('show-on-userpage', $Wiki['groups'][$Val]) ||
											(
												key_exists('show-on-userpage', $Wiki['groups'][$Val]) &&
												$Wiki['groups'][$Val]['show-on-userpage']
											)
										))
											echo "\t" . '<div class="userright-' . $Val . '" >'. msg( 'group-' . $Val, 1 ) .'</div>' . "\r\n\t\t\t\t\t\t\t\t";
									}
									if (isset( $Wiki['config']['dbusertag'] ) && $this->Profile['name'] === $Wiki['config']['dbusertag'])
										echo "\t" . '<div class="userright-dbuser" >'. msg('group-dbuser', 1) .'</div>' . "\r\n\t\t\t\t\t\t\t\t";
								?>
</div>
							</div>
						</div>
						<div id="userAbout" >
							<span class="about-left" ><?php msg('ua-edits'); ?></span>
							<span class="about-right" ><?php echo count($this->Profile['about']['log']); ?></span>
							<br /><span class="about-left" ><?php msg('ua-createdpages'); ?></span>
							<span class="about-right" ><?php echo $this->Profile['about']['created-pages']; ?></span>
							<br /><span class="about-left" ><?php msg('ua-blogs'); ?></span>
							<span class="about-right" ><?php echo $this->Profile['about']['blogs']; ?></span>
							<br /><br /><span class="about-left" ><?php msg('ua-comments'); ?></span>
							<span class="about-right" ><?php echo $this->Profile['about']['comments']; ?></span>
							<br /><span class="about-left" ><?php msg('ua-latestaction'); ?></span>
							<span class="about-right" ><?php if (count($this->Profile['about']['log']) > 0) timestamp($this->Profile['about']['log'][0]['timestamp']); else msg('ua-noaction'); ?></span>
						</div><?php
							$System['block-user'] = '';
							if (!$System['own-profile']) {
								// Block user link
								if (p( 'blockuser' ) && !ur( 'blocked' ))
									$System['block-user'] = '<a href="'. fl( 'rights', ['?' => $this->Profile['name'], 'block'], 1 ) .'" class="userBlockLink" >'. msg( 'block-user-link', 1 ) .'</a>';
								elseIf (p( 'blockuser' ) && ur( 'blocked', $this->Profile['name'] ))
									$System['block-user'] = '<a href="'. fl( 'rights', ['?' => $this->Profile['name'], 'block'], 1 ) .'" class="userBlockLink userUnblockLink" >'. msg( 'unblock-user-link', 1 ) .'</a>';
							} else
								$System['block-user'] = '';

							// Edit group rights link
							if (p( 'editusergroups' )) {
								if (ur( 'allrights' ) && !ur( 'allrights', $User )) {} else
									echo '<div id="userRightsEditLink" ><a href="'. fl( 'rights', ['?' => $this->Profile['name']], 1 ) .'" >'. msg( 'edit-groups-link', 1 ) .'</a>'. $System['block-user'] .'</div>';
							}

						?>

					</div>
					<br clear="both" />
					<hr style="background: #D3D3D3;" />
					<div id="userpageNav" >
						<ul class="nolist li-left scrolllist" style="margin-left: -40px;" ><?php
								if (ur('nomsg', $this->Profile['name'], 1)) $System['show-msgs'] = 1; else $System['show-msgs'] = 0; ?>

							<li style="border-left: 1px solid #D3D3D3;" for="Profile" ><?php msg('up-t-profile', 0); ?></li><?php if (!ur('nomsg', $this->Profile['name'], 1)) { ?>
							<li for="MsgWall" ><?php msg('up-t-msgwall', 0); ?></li><?php } ?>
							<li for="Blogs" ><?php msg('up-t-blogs', 0); ?></li>
							<li for="Contribs" ><?php msg('up-t-contribs', 0); ?></li>
						</ul><?php
							if ($User === $this->Profile['name']) {
								?>

						<div class="edituserpage" >
							<div id="edituserpagesh" class="showHide showHide1 userpagesh" onclick="shToggle('profileEditLinks')" ></div>
						</div>
					</div>
					<div id="profileEditLinks" style="display: none;" >
						<a href="<?php echo fl('preferences'); ?>" ><?php msg('up-t-pref', 0); ?></a><br />
						<a href="<?php echo fl('editor', array('?' => 'User:' . $this->Profile['name']), 1); ?>" ><?php msg('up-t-edit', 0); ?></a> (<a href="<?php echo fl('page', ['?' => 'User:' . $this->Profile['name'], 'versions']); ?>" ><?php msg('up-t-history'); ?></a>)<br />
						<a href="<?php echo fl('editor', array('css')); ?>" ><?php msg('up-t-css', 0); ?></a><br />
						<a href="<?php echo fl('editor', array('js')); ?>" ><?php msg('up-t-js', 0); ?></a>
					</div><?php } else { ?>

					</div><?php } ?>

					<hr style="background: #D3D3D3;" />
					<div id="userpageContent" >
						<style type="text/css" ><?php
							if (isset($_GET['p'])) {
								if ($_GET['p'] == 'msg' && !ur('nomsg', $this->Profile['name'], 1))
									echo '#userpage_MsgWall:before { display: block; } #userpage_Profile, #userpage_Blogs, #userpage_Contribs { display: none; } ';
								if ($_GET['p'] == 'blogs')
									echo '#userpage_Blogs { display: block; } #userpage_Profile, #userpage_MsgWall, #userpage_Contribs { display: none; } ';
								if ($_GET['p'] == 'contribs')
									echo '#userpage_Contribs:before { display: block; } #userpage_Profile, #userpage_MsgWall, #userpage_Blogs { display: none; } ';
							}
						?></style>
						<div id="userpage_Profile" >
							<?php
							/* Userpage Content by User */
							$this->Profile['page'] = $dbc->prepare( "SELECT content FROM pages WHERE url = :profilepage LIMIT 1" );
							$this->Profile['page']->execute([
								':profilepage' => 'User:' . $this->Profile['name']
							]);
							$this->Profile['page'] = $this->Profile['page']->fetch();

							if ($this->Profile['page']) {
								if ($this->Profile['page']['content'] != '<!-- This is your userpage. -->' && !empty($this->Profile['page']['content']))
									echo prcon( $this->Profile['page']['content'] );
								else
									msg('up-unedited');
							} else
								msg('up-unedited');
						?>

						</div><?php if(!ur('nomsg', $this->Profile['name'], 1)) { ?>

						<div id="userpage_MsgWall" style="<?php if(!isset($_GET['p']) or $_GET['p'] != 'msg') { echo 'display: none;'; } ?>" >
						<?php
							$this->extension( 'comments', ['page' => 'User:' . $this->Profile['name'], 'show' => ['title' => false]] );
							if ($this->Extension['count'] === 0) {
								if ($User === $this->Profile['name'])
									msg( 'up-own-nomsgs' );
								else
									msg( 'up-nomsgs', $this->Profile['name'] );
							}
						?>

						</div><?php } ?>
						<div id="userpage_Blogs" style="<?php if(!isset($_GET['p']) or $_GET['p'] != 'blogs') echo 'display: none;'; ?>" >
							<?php
								if (count($this->Profile['blogs']) > 0) {
									if ($System['own-profile']) {
										echo '<a href="'. fl('editor', ['?' => $Wiki['namespace']['blog']['autoPrefix'] . ':']) .'" title="'. msg('up-writeblog', 1) .'" >'. msg('up-writeblog', 1) .'</a><br /><hr style="background: #D3D3D3;" /><div id="userpageBlogList" >';
										foreach ($this->Profile['blogs'] as $Val)
											echo '<a href="'. fl('page', ['?' => $Val['url']]) .'" ><div class="blog-list blog-list-userpage list" ><b>'. $Val['disptitle'] .'</b><br />'. replace( shortStr($Val['content'], 200), 's' ) .'</div></a>';
										echo '</div>';
									} else {
										echo '<div id="userpageBlogList" >';
										foreach ($this->Profile['blogs'] as $Val)
											echo '<a href="'. fl('page', ['?' => $Val['url']]) .'" ><div class="blog-list blog-list-userpage list" ><b>'. $Val['disptitle'] .'</b><br />'. replace( shortStr($Val['content'], 200), 's' ) .'</div></a>';
										echo '</div>';
									}
								} else {
									if ($System['own-profile'])
										msg('up-own-noblog', 0, [fl('editor', ['?' => $Wiki['namespace']['blog']['autoPrefix'] . ':'])]);
									else
										msg('up-noblog', 0, $this->Profile['name']);
								}
							?>

						</div>
						<div id="userpage_Contribs" style="<?php if(!isset($_GET['p']) || $_GET['p'] != 'contribs') { echo 'display: none;'; } ?>" >
							<?php
								if (count($this->Profile['about']['log']) > 0)
									$this->extension( 'log', ['users' => $this->Profile['name']] );
								else {
									if ($User != $this->Profile['name'])
										msg('up-nocontribs', 0, $this->Profile['name']);
									else
										msg('up-own-nocontribs', 0);
								}
							?>

						</div>
					</div><?php
				}
			} else
				msg( 'user_not_existing' ); // If user does not exist
		}
		// Hidden
		
	}
}