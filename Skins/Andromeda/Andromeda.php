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
							<a href="<?php echo fl('user', array('?' => $Wiki['nav']['sidebar']['News'], 'p' => 'blogs#list')) ?>" ><div class="button b-news" ><?php msg('g-nav-l-news') ?></div></a>
							<a href="<?php echo fl('editor') ?>" ><div class="button b-editor" ><?php msg('g-nav-l-editor') ?></div></a>
						</div>
					</div>
				</div>
				<?php
				if (ur( 'test_group' )) {
				?>
				<div class="navToolLists" >
					<h1>Tools</h1>
					<div class="navSelector" >
						<div class="navSelector__label" >Group tools</div>
						<div class="navSelector__list" >
							<a href="<?php echo fl( 'requests' ); ?>" ><div class="navSelector__option" ><?php msg( 'pt-requests' ); ?></div></a>
							<a href="<?php echo fl( 'control' ); ?>" ><div class="navSelector__option" ><?php msg( 'pt-control' ); ?></div></a>
							<a href="<?php echo fl( 'rights' ); ?>" ><div class="navSelector__option" ><?php msg( 'pt-rights' ); ?></div></a>
							<a href="<?php echo fl( 'notif' ); ?>" ><div class="navSelector__option" ><?php msg( 'pt-notif' ); ?></div></a>
							<a href="<?php echo fl( 'page', ['?' => 'Admin'] ); ?>" ><div class="navSelector__option" >More Admin-Tools</div></a>
						</div>
					</div>
					<?php
					if (($Page->info( 'url' ) === 'page' || $Page->info( 'url' ) === 'site' || $Page->info( 'url' ) === 'editor') && !empty( $_GET['url'])) {
					?>
					<div class="navSelector" >
						<div class="navSelector__label" >Page tools</div>
						<div class="navSelector__list" >
							<a href="<?php echo fl( 'editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']]] ); ?>" ><div class="navSelector__option" ><?php msg( 'edit' ); ?></div></a>
							<a href="<?php echo fl( 'page', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], $Wiki['config']['urlparam']['versionindex']] ); ?>" ><div class="navSelector__option" ><?php msg( 'page_versions_text' ); ?></div></a>
							<a href="<?php echo fl( 'editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'protect'] ); ?>" ><div class="navSelector__option" ><?php msg( 'protect' ); ?></div></a>
							<a href="<?php echo fl( 'editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'hide'] ); ?>" ><div class="navSelector__option" ><?php msg( 'hide' ); ?></div></a>
							<a href="<?php echo fl( 'editor', ['?' => $_GET[$Wiki['config']['urlparam']['pagename']], 'action' => 'rename'] ); ?>" ><div class="navSelector__option" ><?php msg( 'rename' ); ?></div></a>
						</div>
					</div>
					<?php
					}
					?>
					<div class="navSelector" >
						<div class="navSelector__label" >Your pages</div>
						<div class="navSelector__list" >
							<div class="navSelector__option" >Empty</div>
						</div>
					</div>
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