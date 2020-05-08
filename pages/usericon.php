<?php

class Page extends PageBase {
	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-usericon', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		?>
<style type="text/css" >
			#imagepreview {
				display: inline-block;
			}
			.usericon {
				height: 200px;
				width: 200px;
				margin: 0 10px 50px;
				background-repeat: no-repeat;
				background-size: 100%;
				background-position: center;
				border-radius: 20px;
				float: left;
			}
			.usericon#preview {
				background-color: #EFEFEF;
				box-shadow: inset 0 0 10px #CCCCCC;
			}
			.caption {
				margin-top: 200px;
				padding-top: 5px;
				font-size: 14px;
				color: #8A8A8A;
				text-align: center;
				display: block;
			}
			.caption.inside {
				height: 200px;
				width: 200px;
				margin-top: 20px;
				padding: 0;
				font-size: 80px;
				font-family: 'Segoe UI', serif;
				position: absolute;
			}

			@media (max-width: 720px) {
				.usericon, .caption.inside {
					height: 150px;
					width: 150px;
				}
				.caption {
					margin-top: 150px;
				}
				.caption.inside {
					margin: 0;
				}
			}
		</style>
		<script type="text/javascript" >
			function readURL(input) {
				if (input.files && input.files[0]) {
					var reader = new FileReader();

					reader.onload = function (e) {
						$('#preview')
						.css('background-image', ' url(\'' + e.target.result + '\')')
						.css('box-shadow', 'none');
						$('#preview .caption.inside').text('');
					};

					reader.readAsDataURL(input.files[0]);
				}
			}
		</script>
		<?php
$File = 'usericon';

/* RIGHTS */
if ($User) {
	$userP = $User;
	if (p('set-usericon')) {
		if (!empty($_GET[$Wiki['config']['urlparam']['user']]))
			$userP = $_GET[$Wiki['config']['urlparam']['user']];
		elseif (!empty($_POST['username']))
			$userP = $_POST['username'];
	}

	$userPD = $dbc->prepare("SELECT username, rid FROM user WHERE username = :username LIMIT 1");
	$userPD->execute(array(
	':username' => $userP
	));
	$userPD = $userPD->fetch();
}

if ($User && p('set-usericon', $userPD['username'])) {
	if (!isset($_POST['submit'])) {

	?>
	<div id="imagepreview" >
		<div class="usericon" style="background-image: url('<?php echo get_usericon($userPD['username']); ?>')" ><span class="caption" ><?php
		if (p( 'set-usericon' ) && isset( $_GET[$Wiki['config']['urlparam']['user']] ) && !empty( $_GET[$Wiki['config']['urlparam']['user']] ))
			msg( 'usericon-current-user', 0, $_GET[$Wiki['config']['urlparam']['user']] );
		else
			msg( 'usericon-current' );
		?></span></div>
		<div id="preview" class="usericon" ><span class="caption inside" >…</span><span class="caption" ><?php msg('usericon-new'); ?></span></div>
	</div>
	<form method="post" id="uploadform" enctype="multipart/form-data" >
		<?php if (p( 'set-usericon' )) { ?><input type="text" name="username" class="big-input fi btm20" maxlength="30" placeholder="<?php msg('placeholder-username'); msg('usericon-ph-username'); ?>" autocomplete="off" <?php if (isset($_GET['user'])) { echo 'value="' . $userP . '" '; } ?>/><br /><?php } ?>
		<label class="file-upload" ><input type="file" id="fileInput" name="<?php echo $File; ?>" id="<?php echo $File; ?>" accept="image/*" onchange="readURL(this);" /><?php msg('usericon-selectfile'); ?></label><input type="submit" name="submit" class="big-submit left10" value="<?php msg('usericon-submit-image'); ?>" />
	</form>
	<?php
	} elseIf (isset( $_POST['submit'] ) && isset( $_FILES[$File] )) {

	// --------------------- FILE UPLOAD -----------------------------
	$allowUpload	= true;

	$upload_media_data = [
		'name'		=> pathinfo($_FILES[$File]['name'], PATHINFO_FILENAME),
		'extension'	=> pathinfo($_FILES[$File]['name'], PATHINFO_EXTENSION)
	];

	// CHECK IF OPEN REQUEST STATUS
	if (!p('set-usericon') && $userPD['username'] === $User) {
		$Requests = $dbc->prepare("SELECT * FROM requests WHERE type = :type AND username = :username ORDER BY timestamp DESC LIMIT 1");
		$Requests->execute([
			':type'		=> 'usericon',
			':username' => $User
		]);
		$Requests = $Requests->fetch();

		if ($Requests && $Requests['status'] == 'open') {
			msg('usericon-foundopenrequest');
			$allowUpload = false;
		}
	}
	// CHECK IF USER EXITS
	if ($allowUpload && !$userPD) {
		msg( 'user_not_existing' );
		$allowUpload = false;
	}
	// CHECK IF FILE IS IMAGE
	if ($allowUpload && (!$_FILES[$File]['size'] || !getimagesize( $_FILES[$File]['tmp_name'] ))) {
		msg('usericon-notimage');
		$allowUpload = false;
	}
	// CHECK FILE SIZE
	if ($allowUpload && $_FILES[$File]['size'] > $Wiki['uc']['img']['limit']) {
		msg('usericon-filetoolarge');
		$allowUpload = false;
	}
	// CHECK EXTENSION
	if ($allowUpload && !preg_match($Wiki['media-config']['usericon']['extensions-regex'], $upload_media_data['extension'])) {
		msg('usericon-filetype');
		$allowUpload = false;
	}

	if ($allowUpload) {
		timestamp('GET');

		if (p('set-usericon')) {
			$TargetUser = new User();
			$TargetUser->setUserByName($userPD['username']);

			// Delete 3rd oldest usericon for target user
			$MediaDelete = $dbc->prepare(
			"DELETE FROM media WHERE id = (SELECT id FROM (SELECT id FROM media WHERE user = :user AND type = :type ORDER BY timestamp DESC LIMIT 2,1) AS t)"
			);
			$MediaDelete = $MediaDelete->execute([
				':user' => $TargetUser->getRandId(),
				':type'		=> 'usericon'
			]);
		}

		try {
			$upload = [
				'access'		=> ((!p('set-usericon') && $userPD['username'] === USERNAME)) ? json_encode(['permission' => ['set-usericon' => []]]) : '',
				'rand_id'		=> randID(20, 'media'),
				'filename'		=> $upload_media_data['name'],
				'filecontent'	=> file_get_contents($_FILES[$File]['tmp_name'])
			];

			$media = $dbc->prepare("
				INSERT INTO media
				(rid, access, url, type, name, file, data, timestamp, timezone, user)
				VALUES
				(:rid, :access, :url, :type, :name, :file, :data, :timestamp, :timezone, :user)
			");

			$media->execute([
			':rid'			=> $upload['rand_id'],
			':access'		=> $upload['access'],
			':url'			=> $upload['rand_id'],
			':type'			=> 'usericon',
			':name'			=> $upload['filename'],
			':file'			=> $upload['filecontent'],
			':data'			=> json_encode($upload_media_data),
			':timestamp'	=> $timestamp,
			':timezone'		=> $timezone,
			':user'			=> $userPD['rid']
			]);
		} catch(Exception $e) {
			msg('error'); msg('usericon-error');
			$allowUpload = false;
		}

		if ($allowUpload) {
			if (p('set-usericon')) {
				$request = $dbc->prepare("UPDATE user SET usericon = :usericon WHERE username = :username");

				$request = $request->execute([
				':usericon' => $upload['rand_id'],
				':username' => $userPD['username']
				]);
			} else {
				$request = $dbc->prepare("
					INSERT INTO requests
					(rid, username, timestamp, timezone, type, content, filelocation, status)
					VALUES
					(:rid, :username, :timestamp, :timezone, :type, :content, :file, :status)
				");

				$request = $request->execute([
				':rid'		 => randstr(10),
				':username'  => $userPD['username'],
				':timestamp' => $timestamp,
				':timezone'	 => $timezone,
				':type'		 => 'usericon',
				':content'	 => $upload['access'],
				':file'		 => $upload['rand_id'],
				':status'	 => 'open'
				]);
			}

			if ($request) {
				msg('usericon-success');

				if (!p('set-usericon'))
					msg('usericon-success-wait-for-review');
			} else {
				msg('error'); msg('usericon-error');
			}
		}
	}
	// --------------------- FILE UPLOAD -----------------------------
	}
} elseIf (!$User) {
	msg('login-required');
} else
	msg('usericon-permission');

	}
}
?>