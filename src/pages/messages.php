<?php
class Page extends PageBase {
	public $Styles = [ '/resources/comments.css' ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
			return msg( 'pt-mnessages', 1 );
			break;
			default:
			return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		/* MESSAGES */
		$Limit = 30;
		$AllMessages = $dbc->prepare( "SELECT * FROM comments WHERE page = :page AND hidden = false AND writer != :user" );
		$AllMessages->execute([
		':page' => 'User:' . $User,
		':user' => $User
		]);
		$AllMessages = $AllMessages->fetchAll();

		$Messages = $dbc->prepare( "SELECT * FROM comments WHERE page = :page AND hidden = false AND writer != :user AND data NOT IN('user-read-hide') ORDER BY id DESC LIMIT $Limit" );
		$Messages->execute([
		':page' => 'User:' . $User,
		':user' => $User
		]);
		$Messages = $Messages->fetchAll();

		$msgRead = 0;
		if (!empty( $_GET['read'] )) {
			if ($_GET['read'] == 'all') {
				$msgMarkAsRead = $dbc->prepare("UPDATE comments SET data = 'user-read-hide' WHERE page = :page AND hidden = false");
				$msgMarkAsRead->execute([
				':page' => 'User:' . $User
				]);

				$msgRead = 1;
			} else {
				$rid = $_GET['read'];

				$msgMarkAsRead = $dbc->prepare("UPDATE comments SET data = 'user-read-hide' WHERE page = :page AND hidden = false AND rid = :rid");
				$msgMarkAsRead->execute([
					':page' => 'User:' . $User,
					':rid' => $rid
				]);

				$msgRead = 1;
			}
		}

		if (!empty( $User )) {
?>
<style type="text/css" >
	#msgCounter {
		display: none;
	}
	span.read {
		color: green;
	}
	.commentfield:first-of-type {
		margin-top: 20px;
	}
	.markasread {
		margin-bottom: 20px;
		padding: 5px;
		background: #F4F4F4;
		border: 1px solid #D3D3D3;
		float: right;
		transition: .2s;
	}
	.markasread:hover {
		border: 1px solid green;
	}
	.markasreadbtns {
		float: right;
	}
	.markasreadbtns .markasread {
		margin-top: 10px;
		margin-left: 10px;
	}
</style>
<h2 style="margin-bottom: 10px; display: inline-block;" ><?php
if (count( $Messages ) > 0) {
	msg('messages-unread', 0, [count( $Messages ), count( $AllMessages )]);
?>
</h2>
<a href="<?= $this->info('url') ?>?read=all" >
	<div class="markasread" >
		<?php msg( 'messages-markallasread' ); ?>
	</div>
</a>
<hr style="background: #D3D3D3; clear: both;" />
<?php } ?>
<?php
	if (count( $Messages ) > 0) {
		foreach ($Messages as $msgs) {
			$writer = $msgs['writer'];
			$writerD = $dbc->prepare("SELECT * FROM user WHERE username = :writer LIMIT 1");
			$writerD->bindParam(':writer', $writer);
			$writerD->execute();
			$writerD = $writerD->fetch();
			?>
			<a name="c-<?php echo $msgs['rid']; ?>" ></a>
			<div class="commentfield" data-id="<?php echo $msgs['rid']; ?>" >
				<a href="<?php echo fl('user', ['?' => $writer]); ?>" title="<?php echo $writer; ?>" ><div class="commentavatar" style="background: url('<?php echo get_usericon($writerD['username']); ?>');" onmouseover="commentTime(1, 'cT<?php echo $msgs['rid']; ?>')" onmouseout="commentTime(0, 'cT<?php echo $msgs['rid']; ?>')" ></div></a>
				<div id="cT<?php echo $msgs['rid']; ?>" class="commenttime" ><?php echo timestamp( $msgs['timestamp'] ); ?></div>
				<div class="comment usermessage" data-writer="<?php echo $writer; ?>" >
					<div class="commentarrow arrow" ></div>
					<div class="commentcontent" >
						<div id="cc<?php echo $msgs['rid']; ?>" >
							<?php if (!empty($msgs['title'])) { ?><div class="commenttitle title" ><?php echo $msgs['title']; ?></div><?php } ?>
							<div class="commentcontent" <?php
							if (empty($msgs['title'])) {
								echo 'style="margin-top: -45px;" ';
							}
							?>>
								<?php echo $msgs['content']; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="markasreadbtns" >
					<a href="<?php echo fl('user', ['?' => $User, 'p' => 'msg', '#' => 'c-' . $msgs['rid']]); ?>" ><div class="markasread" ><?php msg('messages-openmsg', 0) ?></div></a>
					<a href="<?= $this->info('url') ?>?read=<?php echo $msgs['rid']; ?>" ><div class="markasread" ><?php msg('messages-markasread', 0) ?></div></a>
				</div>
			</div>
			<?php
		}
	} else {
		msg('messages-readall');
	}
} else {
	msg('error-login');
}
	}
}
?>