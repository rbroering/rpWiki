<?php
    class Page extends PageBase {

		public $Scripts	= [ '/js/preferences.js.php' ];
		public $Styles	= [ '/css/preferences.css' ];

		public function msg( $str ) {
			switch ($str) {
				case 'pagetitle':
				case 'disptitle':
					return msg( 'pt-preferences', 1 );
				break;
				default:
					return '';
				break;
			}
		}

		public function insert() {
			global $GlobalVariables;
			extract( $GlobalVariables );

			if(!isset( $User )) {
				msg( 'pref-login' );
			} else {
				msg('pref-hint-reload') ?>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-password') ?></h3>
					<form id="password" method="post" action="senddata.php" >
					    <div class="err-notif" style="display: none;" ><span class="err-text" ></span><br /><br /></div>
						<div class="notif" style="display: none;" ><span></span><br /><br /></div>
						<input type="password" class="big-input" id="pwold" name="pwold" maxlength="40" placeholder="<?php msg('placeholder-old-password') ?>" autocomplete="off" /><br />
						<input type="password" class="big-input top10" id="pw" name="pw" maxlength="40" placeholder="<?php msg('placeholder-new-password') ?>" autocomplete="off" /><br />
						<input type="password" class="big-input top10" id="pw2" name="pw2" maxlength="40" placeholder="<?php msg('placeholder-new-password-repeat') ?>" autocomplete="off" /><br />
						<input type="submit" class="ajax-submit big-submit top10" value="<?php msg('placeholder-change-password') ?>" />
					</form>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-language') ?></h3>
					<form id="lang" method="post" action="senddata.php" >
						<div class="notif" style="display: none;" ><span></span><br /><br /></div>
						<select id="langSelect" name="lang" class="big-select font-inherit font-size-unset" >
							<option id="en" value="en"<?php echo ($UserPref['lang'] == 'en') ? ' selected="selected"' : ''; ?> >English</option>
							<option id="de" value="de"<?php echo ($UserPref['lang'] == 'de') ? ' selected="selected"' : ''; ?> >Deutsch</option>
						</select>
					</form>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-skin') ?></h3>
					<?php msg('pref-expl-skin') ?>
					<form id="skin" class="top20" style="width: 100%;" method="post" action="senddata.php" >
						<div class="notif" style="display: none;" ><span></span><br /><br /></div>
						<select id="skinSelect" name="skin" class="big-select font-inherit font-size-unset btm20" >
							<option id="andromeda" value="andromeda"<?php echo ($UserPref['skin'] == 'andromeda') ? ' selected="selected"' : ''; ?> >Andromeda<?php msg('pref-select-recommended') ?></option>
							<option id="project" value="project"<?php echo ($UserPref['skin'] == 'project') ? ' selected="selected"' : ''; ?> >Project</option>
						</select>
						<div class="pref-previewarea" >
							<div class="pref-preview" ><img src="pages/img/skin-Andromeda-new.png" height="180px" /><span class="img-label" >Andromeda</span></div>
							<div class="pref-preview" ><img src="pages/img/skin-Project.png" height="150px" /><span class="img-label" >Project</span></div>
						</div>
					</form>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-colortheme') ?></h3>
					<?php msg('pref-expl-skin') ?>
					<form id="colortheme" class="top20" style="width: 100%;" method="post" action="senddata.php" >
						<div class="notif" style="display: none;" ><span></span><br /><br /></div>
						<select id="colorthemeSelect" name="colortheme" class="big-select font-inherit font-size-unset btm20" >
							<option id="themeadapt" value="adapt"<?php echo ($UserPref['color_theme'] == 'adapt') ? ' selected="selected"' : ''; ?> ><?php msg('pref-option-themeadapt') ?></option>
							<option id="themelight" value="light"<?php echo ($UserPref['color_theme'] == 'light') ? ' selected="selected"' : ''; ?> ><?php msg('pref-option-themelight') ?></option>
							<option id="themedark" value="dark"<?php echo ($UserPref['color_theme'] == 'dark') ? ' selected="selected"' : ''; ?> ><?php msg('pref-option-themedark') ?></option>
						</select>
					</form>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-bgfx') ?></h3>
					<?php msg('pref-expl-bgfx-heavy') ?>
					<form id="bgfx" class="top20" style="width: 100%;" method="post" action="senddata.php" >
						<div class="notif" style="display: none;" ><span></span><br /><br /></div>
						<select id="bgfxSelect" name="bgfx" class="big-select font-inherit font-size-unset btm20" >
							<option id="bgfx_on" value="enable"<?php echo ($UserPref['bgfx_heavy']) ? ' selected="selected"' : ''; ?> ><?php msg( 'pref-option-enable' ) ?></option>
							<option id="bgfx_off" value="disable"<?php echo ($UserPref['bgfx_heavy']) ?: ' selected="selected"'; ?> ><?php msg( 'pref-option-disable' ) ?></option>
						</select>
					</form>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-customcss') ?></h3>
					<?php msg('pref-expl-customcss') ?>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-customjs') ?></h3>
					<?php msg('pref-expl-customjs') ?>

					<h3 class="sectiontitle top30" ><?php msg('pref-section-usericon') ?></h3>
					<?php msg('pref-expl-usericon') ?>
			<?php
			}
		}
	}
?>