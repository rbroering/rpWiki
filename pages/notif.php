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

		if(isset($User) && p('globalnotif')) {

		$globalNotif = $dbc->query("SELECT * FROM pages WHERE url = 'Sys:GlobalNotif' LIMIT 1");
		$globalNotif = $globalNotif->fetch();
		if(!isset($_POST['send']) && !isset($_GET['close'])) {
			?>
<form method="post" >
	<input type="hidden" name="send" /><!-- -->
	<textarea class="big-textarea bg1-textarea message" name="notifContent" placeholder="Content" ><?php echo $globalNotif['content']; ?></textarea>
	<hr style="margin: 10px 0px; background: #EFEFEF;" />
	<?php
		$this->__insertRadio( 'type', [
			'tHeader'	=> [
				'checked'	=> false,
				'value'		=> 'notifHeader',
				'label'		=> 'Header notification'
			],
			'tMain'		=> [
				'checked'	=> true,
				'value'		=> 'notifMain',
				'label'		=> 'Page notification'
			]
		]);
	?>
	<!--<input id="tHeader" type="radio" name="type" value="notifHeader" /><label for="tHeader" >Header notification</label><br />
	<input id="tMain" type="radio" name="type" value="notifMain" checked="checked" /><label for="tMain" >Page notification</label>-->
	<hr style="margin: 10px 0px; background: #EFEFEF;" />
	<?php
		$this->__insertCheckbox([
			'importantNotif' => [
				'checked'	=> false,
				'label'		=> 'Important'
			]
		]);
	?><br />
	<input type="submit" class="big-submit top10" value="Send" />
	<hr style="margin: 20px 0px; background: #D3D3D3;" />
	<a href="notif?close" >Close current global notification</a>
</form>
			<?php
		} elseIf(isset($_POST['send']) && !isset($_GET['close'])) {
			$notifType = $_POST['type'];
			if($notifType != null) {
				$notifContent = str_replace("'", '&apos;', $_POST['notifContent']);
				if($notifContent != null) {
					if($notifType == 'notifHeader' || $notifType == 'notifMain') {
						if(isset($_POST['importantNotif'])) {
							$important = 'important';
						} else {
							$important = '';
						}
						if($notifType == 'notifHeader') {
							$notif = 'header';
						} elseIf($notifType == 'notifMain') {
							$notif = 'main';
						}
						$send = $dbc->prepare("UPDATE pages SET content = :content, data1 = :data1, data2 = :data2 WHERE url = :url");
						$send = $send->execute(array(
						':content' => $notifContent,
						':data1' => $notif,
						':data2' => $important,
						':url' => 'Sys:GlobalNotif'
						));
						if($send) {
							echo 'The global notification has been sent.';
						} else {
							echo 'An error occured...';
						}
					} else {
						'Invalid value. <a href="?" >Back</a>';
					}
				} else {
					echo 'Please type in a message!';
				}
			} else {
				echo 'Please choose an option. <a href="?" >Back</a>';
			}
		} elseIf(isset($_GET['close']) && !isset($_POST['send'])) {
			$send = $dbc->query("UPDATE pages SET data1 = '', data2 = '' WHERE url = 'Sys:GlobalNotif'");
			if($send == true) {
				echo 'The global notification has been closed.';
			} else {
				echo 'An error occured...';
			}
		}

		} else {
			echo 'You are not allowed to write a global notification.';
		}
	}
}