<?php

class Page extends PageBase {
	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return 'Image upload';
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (p('upload')) {
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
			if(!isset($_POST['submit'])) {
			?>
			<div id="imagepreview" >
				<div id="preview" class="usericon" ><span class="caption inside" >…</span><span class="caption" ><?php msg('usericon-new'); ?></span></div>
			</div>
			<form method="post" id="uploadform" enctype="multipart/form-data" >
				<?php if(p( 'set-usericon' )) { ?><input type="text" name="file_name" class="big-input fi btm20" maxlength="30" placeholder="<?php msg('placeholder-username'); msg('usericon-ph-username'); ?>" autocomplete="off" <?php if(isset($_GET['user'])) { echo 'value="' . $userP . '" '; } ?>/><br /><?php } ?>
				<label class="file-upload" ><input type="file" id="fileInput" name="<?php echo 'file'; ?>" id="<?php echo 'file'; ?>" accept="image/*" onchange="readURL(this);" /><?php msg('usericon-selectfile'); ?></label><input type="submit" name="submit" class="big-submit left10" value="<?php msg('usericon-submit-image'); ?>" />
			</form>
			<?php
			} elseIf (isset( $_POST['submit'] ) && isset( $_FILES['file'] )) {
				timestamp( 'GET' );
				$File['extension']	= strtolower( $_FILES['file']['name'] );
				$File['name']		= (!empty( $_POST['file_name'] )) ? preg_replace( '/(.+)\.(jpg|jpeg|png|bmp|gif|tiff)/i', '$1', $_POST['file_name'] ) . $File['extension'] : $_FILES['file']['name'];

				echo $File['name'];
				/*$request = $dbc->prepare("INSERT INTO images_new (rid, filename, file, filepath, timestamp, timezone, user) VALUES (:rid, :filename, :file, :filepath, :timestamp, :timezone, :user)");
				$request = $request->execute([
					':rid'		 => randId( 10, 'files' ),
					':filename'  => $FileName . '.' . $FileExtension,
					':file'		 => file_get_contents( $_FILES[$File]['tmp_name'] ),
					':filepath'	 => $FilePath,
					':timestamp' => $timestamp,
					':timezone'	 => $timezone,
					':user'		 => $User
				]);
				// --------------------- FILE UPLOAD -----------------------------
				$allowUpload	= 1;
				$FileName		= pathinfo( $_FILES[$File]['name'], PATHINFO_FILENAME );
				$FileExtension	= strtolower( pathinfo( $_FILES[$File]['name'], PATHINFO_EXTENSION ) );
				$FileRandDir1	= randstr( 10, 'dir' );
				$FileRandDir2	= randstr( 10, 'dir' );
				$FileRandDir3	= randstr( 10, 'dir' );
				$FilePathDir	= $Wiki['dir']['usericons'] . $FileRandDir1 . '/' . $FileRandDir2 . '/' . $FileRandDir3;
				$FilePath		= $FilePathDir . '/' . $FileName . '.' . $FileExtension;
				/*if (!file_exists( $FilePath )) {
					mkdir( $FilePathDir, 0777, true );
				}*

				// CHECK IF IMAGE
				if ($_FILES[$File]['size'] != 0 && getimagesize( $_FILES[$File]['tmp_name'] )) {
					$allowUpload = 1;
				} else {
					msg('usericon-notimage');
					$allowUpload = 0;
				}

				// CHECK FILE SIZE
				if($_FILES[$File]['size'] > $Wiki['uc']['img']['limit']) {
					msg('usericon-filetoolarge');
					$allowUpload = 0;
				}

				// CHECK EXTENSION
				if($FileExtension != 'png' && $FileExtension != 'jpg' && $FileExtension != 'jpeg' && $FileExtension != 'gif') {
					msg('usericon-filetype');
					$allowUpload = 0;
				}
					$Id = randstr( 10 );

				}*/
			}
		} else
			echo 'You are not allowed to upload images.';
	}
}