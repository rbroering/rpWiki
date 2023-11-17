<?php

class Skin extends SkinBase {
	public $Scripts	= [ '/content.js.php' ];
	public $Styles	= [ '/css/main.css' ];

	public function construct() {
		global $SkinImport;
		extract( $SkinImport );

		?>
<header id="header" class="header" >
	<div id="logo" class="headerlogo" ><a href="index.php" class="fullsize" ></a></div>
		<nav id="nav" class="headernav" >
			<div id="nav" >
				<div id="navigation" >
					<div class="toggle nav panel" onclick="toggleNavigationPanel()" ></div>
					<div id="header_right" >
						<div id="userLinks" >
							<?php
							if ($Actor->isLoggedIn()) {
								$headerMsgShow = '';
								if (empty( $UserData['msgcount'] ) || $UserData['msgcount'] == 0) {
									$headerMsgShow = 'display: none;';
								}
							?>
								<div class="loggedin" ><div id="userIconHeader" class="header-usericon" style="background: url('<?php echo $Actor->getIcon([36, 36]); ?>');" ><a href="<?php echo fl( 'user', ['?' => $UserData['username']] ); ?>" class="fullsize" ></a><a href="<?php echo fl( 'messages' ); ?>" style="<?php echo $headerMsgShow; ?>" ><div id="msgCounter" class="messages-count" ><?php echo $UserData['msgcount']; ?></div></a></div><div id="userNameHeader" class="header-username" ><a href="<?php echo fl( 'user', ['?' => $UserData['username']] ); ?>" ><?php echo $UserData['username']; ?></a></div><div id="logoutLink" class="header-logout" ><a href="<?php echo fl( 'logout' ); ?>" >Log out</a></div></div>
							<?php
							} else {
							?>
								<a href="<?php echo fl( 'login' ); ?>" class="headerlink loginlink alpha" >Log in</a> <div id="registerLink" class="register-link" ><a href="<?php echo fl( 'signup' ); ?>" class="headerlink registerlink alpha" >Register</a></div>
							<?php
							}
							?>
						</div>
						<div id="search" >
							<form action="search" method="get" id="searchForm" >
								<input type="hidden" name="ref" value="h" />
								<input type="text" id="search" class="search searchInput keyword query" onclick="headerSearch(1)" onblur="headerSearch(0)" name="q" placeholder="" autocomplete="off" /><!--onblur="arClass(0, this, \'activesearch\')"-->
								<input type="submit" class="glass" value="" />
							</form>
						</div>
					</div>
				</div>
			</div>
		</nav>
	</div>
</header>
<nav id="NavigationPanel" class="nav navigation panel" style="display: none;" >
	<script type="text/javascript" >document.write(wPanelNavC);</script>
</nav>
<div style="align: center;" class="maincontent" onclick="sh(0, 1, 'NavigationPanel')" >
	<div id="main" >
		<div id="pageheader" >
			<h1 class="pagetitle" >
				<?php echo $Page->msg( 'disptitle' );
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
				?>
			</h1><?php
				if (!empty( $Page->msg( 'subtitle' ) )) {
			?>

			<div class="pagesubtitle" >
				<span class="pagetitle-subtitle" ><?php echo $Page->msg( 'subtitle' ); ?></span>
			</div><?php
				}
			?>

		</div>
		<div id="pagecontent" class="pw<?php echo (empty( $Page->msg( 'disptitle' ) )) ? ' noheader' : ''; ?>" >
			<!-- CONTENT -->
			<?php
			$this->load();
			?>
		</div>
	</div>
</div>
<footer id="footer" class="footer copyright copyright-footer" >
	<?php
		echo $Wiki['custom']['footer']['license'];
	?>
</footer>
		<?php
	}
}
?>