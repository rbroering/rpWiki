<?php

class Skin extends SkinBase {
	public $Scripts	= [ '/main.js' ];
	public $Styles	= [ '/index.css.php' ];

	public function construct() {
		global $SkinImport;
		extract($SkinImport);

		$msgc = count($Actor->getUnreadMessages());

		$GlobalNotif = $dbc->prepare( 'SELECT content, data1, data2 FROM pages WHERE url = :url LIMIT 1' );
		$GlobalNotif->execute([
			':url' => 'Sys:GlobalNotif'
		]);
		$GlobalNotif = $GlobalNotif->fetch();
?>
<header id="header" class="header" >
			<div id="h_btn_nav" class="header-button toggleNav" ><div class="toggle" ></div></div>
			<div class="separator s_tn-l" ></div>
			<div id="logo" class="headerlogo" ><a href="<?php echo fl('home'); ?>" class="fullsize" ></a></div>
			<div class="separator s_s-ul" ></div>
			<div id="header_colordots" ></div>
			<div id="header_right" >
				<!--<div class="separator s_s-ul" ></div>-->
				<div id="userLinks" ><?php
						if ($Actor->isLoggedIn()) { ?>

					<div id="h_btn_user" class="loggedin header-button" >
						<div id="userIconHeader" class="header-usericon" style="background: url('<?php echo $Actor->getIcon([42, 42]); ?>');" >
							<div class="symbol open" <?php if ($msgc > 0) {
							echo 'style="display: none;" ';
						} ?>></div>
							<div id="msgCounter" class="icon messages-count" <?php if ($msgc == 0) {
							echo 'style="display: none;" ';
						} ?>><a href="<?php echo fl('messages') ?>" class="fullsize" ><?php if ($msgc > 99) {
						echo '>';
					} else {
						echo $msgc;
					} ?></a></div>
							<a href="<?php echo fl('user', ['?' => USERNAME]) ?>" class="fullsize" ></a>
						</div>
					</div><?php
						} else {
					?>

					<div class="loggedout" >
						<a href="<?php
							echo fl(
								'login',
								[
									$Wiki['config']['urlparam']['redirect_c'] => urlencode(
										fl(
											$Page->info('url'),
											array_diff_key($_GET, ['page' => 1])
										)
									)
								],
								true)
							?>" class="headerlink loginlink" >
							<?php msg('g-h-l-login') ?>
						</a>
						<div class="separator small s_ll-rl" ></div>
						<a href="<?php echo fl('signup') ?>" class="headerlink registerlink" ><?php msg('g-h-l-register') ?></a>
					</div><?php
						}
					?>

				</div>
			</div>
			<?php
			if ($GlobalNotif && !empty( $GlobalNotif['content'] ) && $GlobalNotif['data1'] == 'header') {
			?>
			<header id="globalNotifHeader" class="header global notification<?php if (!empty( $GlobalNotif['data2'] )) {
				echo ' ' . $GlobalNotif['data2'];
			} ?>" >
				<div><?php echo prcon( $GlobalNotif['content'] ); ?></div>
				<div id="notifClose" class="closenotification" onclick="closeNotif(0);" ></div>
			</header>
			<?php
			}
			?>
		</header>
		<nav id="NavigationPanel" class="nav navigation panel" >
			<div id="NavigationContent" >
				<div id="search" >
					<form action="<?php echo fl('search') ?>" method="get" id="searchForm" >
						<input type="hidden" name="ref" value="h" />
						<input type="text" id="search" class="search searchInput keyword query" onblur="headerSearch(0)" name="q" placeholder="<?php msg('g-nav-ph-search') ?>" autocomplete="off" />
						<input type="submit" id="search-submit" class="searchSubmit" value="" />
					</form>
				</div>
				<div id="navBtns" class="nav-buttons" >
					<div class="center-elements" >
						<div class="button-row" >
							<a href="<?php echo fl('log') ?>" ><div class="button b-log" ><?php msg('g-nav-l-log') ?></div></a>
							<a href="<?php echo fl('userlist') ?>" ><div class="button b-userlist" ><?php msg('g-nav-l-userlist') ?></div></a>
						</div>
						<div class="button-row" >
							<a href="<?= $Wiki['nav']['sidebar']['News'] ?>" ><div class="button b-news" ><?php msg('g-nav-l-news') ?></div></a>
							<a href="<?php echo fl('editor') ?>" ><div class="button b-editor" ><?php msg('g-nav-l-editor') ?></div></a>
						</div>
					</div>
				</div>
				<?php
				$ToolnavGrouptools = [
					[
						"name" => "requests",
						"label" => msg('pt-requests', 1),
						"link" => fl('requests'),
						"permission" => "requests"
					],
					[
						"name" => "permissions",
						"label" => msg('pt-permissions', 1),
						"link" => fl('permissions'),
						"permission" => "control-edit"
					],
					[
						"name" => "rights",
						"label" => msg('pt-rights', 1),
						"link" => fl('rights'),
						"permission" => "editusergroups"
					],
					[
						"name" => "notif",
						"label" => msg('pt-notif', 1),
						"link" => fl('notif'),
						"permission" => "globalnotif"
					]
				];

				$ToolnavPagetools = [];
				if (in_array($Page->info('url'), ['page', 'site', 'editor']) && !empty($_GET[$Wiki['config']['urlparam']['pagename']])) {
					$ToolnavPagetools = [
						[
							"name" => "edit",
							"label" => msg('edit', 1),
							"link" => fl('editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']]]),
							"permission" => "p-edit"
						],
						[
							"name" => "versions",
							"label" => msg('page_versions_text', 1),
							"link" => fl('page', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], $Wiki['config']['urlparam']['versionindex']]),
							"permission" => "editusergroups"//"view-versions"
						],
						[
							"name" => "protect",
							"label" => msg('protect', 1),
							"link" => fl('editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'protect']),
							"permission" => "p-protect"
						],
						[
							"name" => "hide",
							"label" => msg('hide', 1),
							"link" => fl('editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'hide']),
							"permission" => "p-hide"
						],
						[
							"name" => "rename",
							"label" => msg('rename', 1),
							"link" => fl('editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'rename']),
							"permission" => "p-rename"
						]
					];
				}

				$ToolnavElements = [
					'group' => [],
					'page' => []
				];
				foreach($ToolnavGrouptools as $ToolnavElement) {
					if (!$Actor->hasPermission($ToolnavElement['permission'])) continue;

					$ToolnavElements['group'][] = '<a href="'.$ToolnavElement['link'].'" ><div class="navSelector__option" >'.$ToolnavElement['label'].'</div></a>';
				}
				foreach($ToolnavPagetools as $ToolnavElement) {
					if (!$Actor->hasPermission($ToolnavElement['permission'])) continue;

					$ToolnavElements['page'][] = '<a href="'.$ToolnavElement['link'].'" ><div class="navSelector__option" >'.$ToolnavElement['label'].'</div></a>';
				}

				if (count($ToolnavElements['group']) || count($ToolnavElements['page']) || $Actor->isInGroup('test_group')) {
				?>
				<div class="navToolLists" >
					<h1><?= msg('toolnav-tools-section', 1) ?></h1>
					<?php if (count($ToolnavElements['group'])) { ?>
					<div class="navSelector" >
						<div class="navSelector__label" ><?= msg('toolnav-group-tools', 1) ?></div>
						<div class="navSelector__list" >
							<?php foreach($ToolnavElements['group'] as $ToolElement) echo $ToolElement; ?>
						</div>
					</div>
					<?php
					} if (count($ToolnavElements['page'])) {
					?>
					<div class="navSelector" >
						<div class="navSelector__label" ><?= msg('toolnav-page-tools', 1) ?></div>
						<div class="navSelector__list" >
							<?php foreach($ToolnavElements['page'] as $ToolElement) echo $ToolElement; ?>
						</div>
					</div>
					<?php
					}
					if ($Actor->isInGroup('test_group')) {
					?>
					<div class="navSelector" >
						<div class="navSelector__label" ><?= msg('toolnav-pinned-pages', 1) ?></div>
						<div class="navSelector__list" >
							<div class="navSelector__option" >Empty</div>
						</div>
					</div>
					<?php
					}
					?>
				</div>
				<?php
				}
				?>
			</div>
		</nav>
<?php
		if (!empty( $User )) {
		?>
		<div id="UserPanel" class="nav navigation panel" >
			<div class="userLinks" >
				<ul>
					<?php $i = 0; if ($i > 0) { ?>
					<li><a href="<?php echo fl('messages') ?>" title="Your messages" class="fullsize" ><?php msg('g-unav-l-msgs') ?> (<?php echo $UserData['msgcount']; ?>)</a></li>
					<?php } ?><li><a href="<?php echo fl('user', ['?' => USERNAME]) ?>" title="Your profile" class="fullsize" ><?php msg('g-unav-l-profile') ?></a></li>
					<li><a href="<?php echo fl('preferences') ?>" title="Preferences" class="fullsize" ><?php msg('g-unav-l-pref') ?></a></li>
					<li><a href="<?php
						echo fl(
							'logout',
							[
								$Wiki['config']['urlparam']['redirect_c'] => fl(
									$Page->info('url'),
									array_diff_key($_GET, ['page' => 1])
								)
							],
							true
						)
					?>" title="Log out" class="fullsize" ><?php msg('g-unav-l-logout') ?></a></li>
				</ul>
			</div>
		</div>
<?php
		}
		?>
		<div class="maincontent" onclick="toggleClass('#NavigationPanel', 'shown', 0)" >
			<?php
				$Notification = array();

				if (!empty( $Page->msg( 'notification' ) )) {
					if (is_string( $Page->msg( 'notification' ) ))
						$Notification = [ 'msg' => $Page->msg( 'notification' ) ];
					else
						$Notification = $Page->msg( 'notification' );
				}

				if ($GlobalNotif && !empty( $GlobalNotif['content'] ) && $GlobalNotif['data1'] == 'main') {
					array_push( $Notification, [ 'msg' => prcon( $GlobalNotif['content'] ), 'class' => $GlobalNotif['data2'] ] );
				}

				foreach ($Notification as $i => $Notification) {
					?>
				<div id="localNotif<?php echo $i; ?>" class="pageNotifMain main notif global notification<?php
					if (in_array( 'class', array_keys( $Notification ) )) {
						switch ($Notification['class']) {
						case 'important':
							echo ' mark1';
						break;
						default:
						break;
						}
					}
				?>" onclick="closeNotif('#localNotif<?php echo $i; ?>');" >
					<?php
						echo $Notification['msg'];
					?>
				</div>
					<?php
				}
			?>
<div id="main" >
				<?php
					if (!empty( $Page->msg( 'disptitle' ) )) {
						?>
<div id="pageheader" >
					<h1 class="pagetitle" ><?php echo $Page->msg( 'disptitle' );
						if (!empty( $Page->msg( 'title-buttons' ) && is_array( $Page->msg( 'title-buttons' ) ) )) {
							foreach ($Page->msg( 'title-buttons' ) as $ButtonId => $Button) {
								if (empty( $Button['label'] ))
									$Button['label'] = '';
								if (empty( $Button['class'] ))
									$Button['class'] = 'class="title-button"';
								else
									$Button['class'] = 'class="title-button ' . $Button['class'] . '"';
								if (empty( $Button['title'] ))
									$Button['title'] = $Button['label'];

								echo '<a id="tb_' . $ButtonId . '" href="' . $Button['link'] . '" title="' . $Button['title'] . '" ' . $Button['class'] . ' ><small>' . $Button['label'] . '</small></a>';
							}
						}
					?></h1><?php
						if (!empty( $Page->msg( 'subtitle' ) )) {
					?>

					<div class="pagesubtitle" >
						<span class="pagetitle-subtitle" ><?php echo $Page->msg( 'subtitle' ); ?></span>
					</div><?php
						}
					?>

				</div>
				<?php
					}
?>
<div id="pagecontent" class="bw<?php echo (empty( $Page->msg( 'disptitle' ) )) ? ' noheader' : ''; ?>" >
					<?php
					$this->load();
					?>

				</div>
			</div>
		</div><?php
		if (!empty( $Wiki['custom']['footer']['license'] )) {
			?>

		<footer id="footer" class="footer copyright copyright-footer" >
			<?php
				echo $Wiki['custom']['footer']['license'];
			?>

		</footer><?php
		}
	}
}
