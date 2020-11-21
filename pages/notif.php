<?php

class Page extends PageBase {
	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-notif', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if ($Actor->hasPermission('globalnotif')) {

		$globalNotif = $dbc->query("SELECT * FROM pages WHERE url = 'Sys:GlobalNotif' LIMIT 1");
		$globalNotif = $globalNotif->fetch();
		if (!isset($_POST['send']) && !isset($_GET['close'])) {
			?>
<form method="post" >
	<input type="hidden" name="send" /><!-- -->
	<textarea class="big-textarea bg1-textarea message" name="notifContent" placeholder="<?= msg('notif-ph-content') ?>" ><?php echo $globalNotif['content']; ?></textarea>
	<hr style="margin: 10px 0px; background: #EFEFEF;" />
	<?php
		$this->__insertRadio( 'type', [
			'tHeader'	=> [
				'checked'	=> false,
				'value'		=> 'notifHeader',
				'label'		=> msg('notif-formlabel-type-header', 1)
			],
			'tMain'		=> [
				'checked'	=> true,
				'value'		=> 'notifMain',
				'label'		=> msg('notif-formlabel-type-page', 1)
			]
		]);
	?>
	<hr style="margin: 10px 0px; background: #EFEFEF;" />
	<?php
		$this->__insertCheckbox([
			'importantNotif' => [
				'checked'	=> false,
				'label'		=> msg('notif-formlabel-mark-as-important', 1)
			]
		]);
	?><br />
	<input type="submit" class="big-submit top10" value="<?= msg('notif-formlabel-submit') ?>" />
	<hr style="margin: 20px 0px; background: #D3D3D3;" />
	<a href="notif?close" ><?= msg('notif-close-link') ?></a>
</form>
			<?php
		} elseif (isset($_POST['send']) && !isset($_GET['close'])) {
			$notifType = $_POST['type'];
			if ($notifType != null) {
				$notifContent = str_replace("'", '&apos;', $_POST['notifContent']);
				if ($notifContent != null) {
					if ($notifType == 'notifHeader' || $notifType == 'notifMain') {
						$important = isset($_POST['importantNotif']) ? 'important' : $important = '';

						$notif = ($notifType == 'notifHeader') ? 'header' : $notif = 'main';

						$send = $dbc->prepare("UPDATE pages SET content = :content, data1 = :data1, data2 = :data2 WHERE url = :url");
						$send = $send->execute(array(
							':content'	=> $notifContent,
							':data1'	=> $notif,
							':data2'	=> $important,
							':url'		=> 'Sys:GlobalNotif'
						));

						msg($send ? 'notif-submit-success' : 'notif-submit-error');
					}
				} else {
					msg('notif-submit-error-no-message');
				}
			} else {
				msg('notif-submit-error-no-option-selected');
			}
		} elseif (isset($_GET['close']) && !isset($_POST['send'])) {
			$send = $dbc->query("UPDATE pages SET data1 = '', data2 = '' WHERE url = 'Sys:GlobalNotif'");

			msg($send ? 'notif-close-success' : 'notif-close-error');
		}

		} else {
			msg('notif-error-permission');
		}
	}
}
