<?php

require_once( 'getdata.php' );

if (empty( $_POST )) {
	$Link = fl( 'preferences' );
	header( "Location: $Link" );
	die();
}

# PASSWORD
if (isset( $_POST['pw'] )) {
	$pwold	= $_POST['pwold'];
	$pw		= $_POST['pw'];
	$pw2	= $_POST['pw2'];

	if (empty( $pwold ) || empty( $pw ) || empty( $pw2 )) {
		echo '<span class="err-notif" >'. msg( 'error-filltext-section', 1 ) .'</span>';
	} else {
		if (password_verify( $pwold, $UserData['password'] )) {
			$Checks = true;

			if (strlen( $pw ) < $Wiki['userdata']['pw']['lenmin'] || strlen( $pw ) > $Wiki['userdata']['pw']['lenmax']) {
				$Checks = false;
				echo '<span class="err-notif" >'. msg( 'signup-pw-length-range', 1, [strlen( $pw ), $Wiki['userdata']['pw']['lenmin'], $Wiki['userdata']['pw']['lenmax']] ) . '</span>';
			}

			$CountChars = [];
			foreach (count_chars( $pw, 1 ) as $i => $Char) {
				if ($Char / strlen( $pw ) > 1 / 5) {
					$CountChars[] = $i;
				}
			}
			if (!empty( $CountChars )) {
				$Checks = false;
				echo '<span class="err-notif" >'. msg( 'signup-pw-chars', 1, rtrim( implode( ', ', $CountChars ), ', ' ) ) .'</span>';
			}

			if ($pw !== $pw2) {
				$Checks = false;
				echo '<span class="err-notif" >'. msg( 'error-passwordmatch', 1 ) . '</span>';
			}

			if (stristr( $pw, $UserData['username'] )) {
				$Checks = false;
				echo '<span class="err-notif" >'. msg( 'signup-password-is-name', 1 ) . '</span>';
			} elseIf (levenshtein( strtolower( $pw ), strtolower( $UserData['username'] ) ) <= 4) {
				$Checks = false;
				echo '<span class="err-notif" >'. msg( 'signup-name-pw-too-similar', 1 ) . '</span>';
			}

			if ($Checks) {
				if (empty( $Wiki['hashfuncs']['user_pw']['algo'] ))
					$Wiki['hashfuncs']['user_pw']['algo'] = PASSWORD_DEFAULT;

				# SUCCESS, SAVE
				$savePW = $dbc->prepare( 'UPDATE user SET password = :password WHERE username = :username AND rid = :rid LIMIT 1' );
				$savePW = $savePW->execute([
					':username' => $UserData['username'],
					':rid'		=> $UserData['rid'],
					':password' => password_hash( $pw, $Wiki['hashfuncs']['user_pw']['algo'] )
				]);
				if (!$savePW) {
					echo '<span class="err-notif" >'. msg( 'pref-pw-error', 1 ) .'</span>';
				} else {
					echo '<span class="success-notif" >'. msg( 'pref-pw-success', 1 ) .'</span>';
				}
			}
		} else {
			# OLD PASSWORD WRONG
			echo '<span class="err-notif" >'. msg( 'pref-pw-wrong', 1 ) .'</span>';
		}
	}
}


# LANGUAGE
if (isset($_POST['lang'])) {
	$lang		= $_POST['lang'];
	$langName	= $_POST['langName'];

	$saveLang	= $dbc->prepare("UPDATE pref SET lang = :lang WHERE username = :username LIMIT 1");
	$saveLang	= $saveLang->execute([
	':username'	=> $User,
	':lang'		=> $lang
	]);

	if (!$saveLang) {
		echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
	} else {
		msg( 'pref-lang-success', 0, $langName );
	}
}


# SKIN
if (isset( $_POST['skin'] ) && stristr( $Wiki['config']['available-skins'], $_POST['skin'] )) {
	$skin		= $_POST['skin'];
	$skinName	= $_POST['skinName'];

	$saveSkin	= $dbc->prepare( "UPDATE pref SET skin = :skin WHERE username = :username LIMIT 1" );
	$saveSkin	= $saveSkin->execute([
	':username'	=> $User,
	':skin'		=> $skin
	]);

	if (!$saveSkin) {
		echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
	} else {
		msg( 'pref-skin-success', 0, $skinName );
	}
} elseIf (isset( $_POST['skin'] ) && !stristr( $Wiki['config']['available-skins'], $_POST['skin'] )) {
	echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
}


# COLOR THEME
if (isset( $_POST['theme'] ) && in_array($_POST['theme'], ['adapt', 'light', 'dark'])) {
	$theme		= $_POST['theme'];

	$saveTheme	= $dbc->prepare( "UPDATE pref SET color_theme = :theme WHERE username = :username LIMIT 1" );
	$saveTheme	= $saveTheme->execute([
	':username'	=> $User,
	':theme'	=> $theme
	]);

	if (!$saveTheme) {
		echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
	} else {
		msg( 'pref-theme-success', 0, $theme );
	}
} elseIf (isset( $_POST['theme'] ) && !in_array($_POST['theme'], ['adapt', 'light', 'dark'])) {
	echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
}


# BGFX HEAVY
if (isset( $_POST['bgfx'] ) && ($_POST['bgfx'] == 'enable' || $_POST['bgfx'] == 'disable')) {
	$bgfx		= ($_POST['bgfx'] == 'enable') ? true : false;

	$saveBgfx	= $dbc->prepare( "UPDATE pref SET bgfx_heavy = :bgfx WHERE username = :username LIMIT 1" );
	$saveBgfx	= $saveBgfx->execute([
	':username'	=> $User,
	':bgfx'		=> $bgfx
	]);

	if (!$saveBgfx) {
		echo '<span class="err-notif" >'. msg( 'error', 1 ) .'</span>';
	} else {
		msg( 'pref-success' );
	}
}

if (!empty( $_POST['data'] )) {

	# CONTROL
	if ( $_POST['data'] == 'control' && !empty( $_POST['permission'] ) && !empty( $_POST['range'] ) ) {
		if (p( 'control-edit-groups' )) {
			$Permission = '';

			switch ($_POST['range']) {
				case 'groups':
					if (!empty( $_POST['groups'] )) {
						$Groups = explode( ',', $_POST['groups'] );

						foreach ($Groups as $Group) {
							if ( in_array( $Group, $Wiki['list-groups'] ) || $Group === 'own' ) {
								$Permission .= $Group . ',';
							}
						}

						if (strlen( $Permission ) > 0) {
							$Permission = (substr( $Permission, -1, 1 ) == ',') ? substr( $Permission, 0, strlen( $Permission ) - 1 ) : $Permission; // remove last comma from string
						} else {
							unset( $Permission );
						}
					} else {
						unset( $Permission );
					}
				break;
				case 'users':
					$Permission = 'users';
				break;
				case 'every':
					$Permission = '*';
				break;
				case 'nouser':
					$Permission = '-';
				break;
				default:
					unset( $Permission );
				break;
			}

			if (!empty( $Permission )) {
				$Update = $dbc->prepare( "UPDATE permissions SET groups = :groups WHERE permission = :permission" );
				$Update = $Update->execute([
					':permission'	=> $_POST['permission'],
					':groups'		=> $Permission
				]);

				if ($Update) {
					echo $Permission;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	} elseIf ($_POST['data'] == 'control' && !empty( $_POST['permission'] ) && isset( $_POST['new'] )) {
		if (p( 'control-edit-groups' )) {
			if (strlen( $_POST['permission'] ) >= 3) {
				$Check = $dbc->prepare( "SELECT * FROM permissions WHERE permission = :permission" );
				$Check->execute([
					':permission' => $_POST['permission']
				]);
				$Check = $Check->rowCount();

				if ($Check === 0) {
					$Update = $dbc->prepare( "INSERT INTO permissions (permission) VALUES (:permission)" );
					$Update = $Update->execute([
						':permission' => $_POST['permission']
					]);

					if ($Update) {
						echo 'success';
					} else {
						return false;
						#echo 'SQL error';
					}
				} else {
						return false;
						#echo 'Permission name already exists';
				}
			} else {
						return false;
						#echo 'String length error (must be at least 3 characters)';
			}
		} else {
						return false;
						#echo 'Permission error';
		}
	}

}

# COMMENTS
if (isset( $_POST['category'] ) && $_POST['category'] == 'e__Comment' && isset( $_POST['action'] ) &&
	isset( $_POST['page'] ) && !empty( $_POST['page'] ) &&
	isset( $_POST['pagetype'] ) && !empty( $_POST['pagetype'] ))
{
	timestamp( 'GET' );
	$values = [
		'user'			=> $User,
		'rid_new'		=> randstr( 10 ),
		'rid_log'		=> randstr( 10 ),
		'page'			=> $_POST['page'],
		'pagetype'		=> $_POST['pagetype'],
		'timestamp'		=> $timestamp,
		'timezone'		=> $timezone
	];

	$logAction = [
		'edit'		=> 'comment-edit',
		'save'		=> 'comment-new',
		'hide'		=> 'comment-hide',
		'unhide'	=> 'comment-unhide',
		'delete'	=> 'comment-delete'
	];

	if (!empty( $_POST['id'] )) {
		$Comment = $dbc->prepare( "SELECT * FROM comments WHERE rid = :cID LIMIT 1" );
		$Comment->execute([
			':cID' => $_POST['id']
		]);
		$Comment = $Comment->fetch();
	} else {
		$Comment = false;
	}

	switch ($_POST['action']) {
		case 'save':
			if (p( 'writecomments' )) {
				if (isset( $_POST['c_Content'] ) && !empty( $_POST['c_Content'] )) {
					$sql = $dbc->prepare(
						'INSERT INTO comments
						(rid, page, pagetype, title, content, timestamp, timezone, writer) VALUES
						(:rid, :page, :pagetype, :title, :content, :timestamp, :timezone, :user)'
					);

					$values['title']	= (!isset( $_POST['c_Title'] )) ? '' : $_POST['c_Title'];
					$values['content']	= $_POST['c_Content'];

					$test = $sql->execute([
						':user'			=> $User,
						':rid'			=> $values['rid_new'],
						':page'			=> $values['page'],
						':pagetype'		=> $values['pagetype'],
						':timestamp'	=> $timestamp,
						':timezone'		=> $timezone,
						':title'		=> $values['title'],
						':content'		=> $values['content']
					]);

					$logEntry = $dbc->prepare(
					"INSERT INTO log
					(username, page, page2, rid, new, type, timestamp, timezone) VALUES
					(:username, :page, :page2, :randID, :new, :type, :timestamp, :timezone)"
					);
					$logEntry->execute([
						':username' 	=> $User,
						':page'		 	=> $values['page'],
						':page2'		=> $values['rid_new'],
						':randID'		=> $values['rid_log'],
						':new' 		 	=> $values['content'],
						':type' 		=> $logAction['save'], #'writecomment',
						':timestamp'	=> $timestamp,
						':timezone' 	=> $timezone
					]);
				} else {
					echo 'error_missingInformation';
				}
			} else {
				echo 'error_Permission';
			}
		break;
		case 'edit':
			if ($Comment && p( 'editcomments', $Comment['writer'] )) {
				$sql = $dbc->prepare( "UPDATE comments SET title = :title, content = :content, edited = :logID WHERE rid = :cID AND page = :page AND pagetype = :pagetype" );

				$values['title']	= (empty( $_POST['c_Title'] )) ? '' : $_POST['c_Title'];
				$values['content']	= (empty( $_POST['c_Content'] )) ? '' : $_POST['c_Content'];

				if (!empty( $values['content'] )) {
					if ($Comment['title'] != $values['title'] || $Comment['content'] != $values['content']) {

					$logEntry = $dbc->prepare(
					"INSERT INTO log
					(username, page, page2, rid, old, new, type, timestamp, timezone) VALUES
					(:username, :page, :page2, :randID, :old, :new, :type, :timestamp, :timezone)"
					);
					$logEntry->execute([
						':username'		=> $User,
						':page'			=> $values['page'],
						':page2'		=> $Comment['rid'],
						':randID'		=> $values['rid_log'],
						':old'			=> $Comment['content'],
						':new' 			=> $values['content'],
						':type' 		=> $logAction['edit'],
						':timestamp'	=> $timestamp,
						':timezone' 	=> $timezone
					]);

					$test = $sql->execute([
						':cID'			=> $Comment['rid'],
						':title'		=> $values['title'],
						':content'		=> $values['content'],
						':logID'		=> $values['rid_log'],
						':page'			=> $values['page'],
						':pagetype'		=> $values['pagetype']
					]);

					} else {
						$test = true; // There are no changes, so there does not need to be a SQL operation. But success is thrown out anyway.
					}
				} else {
					echo 'error_Empty';
				}
			} else {
				echo 'error_Permission';
			}
		break;
		case 'hide':
			if ($Comment && p( 'hidecomments', $Comment['writer'] )) {
				if ($Comment['hidden']) {
					$sql = $dbc->prepare( "UPDATE comments SET hidden = 0 WHERE rid = :cID AND page = :page AND pagetype = :pagetype" );
					$values['log-action'] = $logAction['unhide'];
				} else {
					$sql = $dbc->prepare( "UPDATE comments SET hidden = 1 WHERE rid = :cID AND page = :page AND pagetype = :pagetype" );
					$values['log-action'] = $logAction['hide'];
				}

				$test = $sql->execute([
					':page'		=> $Comment['page'],
					':pagetype'	=> $Comment['pagetype'],
					':cID'		=> $Comment['rid']
				]);

				$logEntry = $dbc->prepare(
				"INSERT INTO log
				(username, page, page2, type, timestamp, timezone) VALUES
				(:username, :page, :page2, :type, :timestamp, :timezone)
				");
				$logEntry->execute([
					':username'	 => $User,
					':page'		 => $values['page'],
					':page2'	 => $Comment['rid'],
					':type'		 => $values['log-action'],
					':timestamp' => $timestamp,
					':timezone'	 => $timezone
				]);
			} else {
				echo 'error_Permission';
			}
		break;
		case 'delete':
			if ($Comment && p( 'deletecomments', $Comment['writer'] )) {
				$sql = $dbc->prepare( "DELETE FROM comments WHERE rid = :cID AND page = :page AND pagetype = :pagetype LIMIT 1" );
				$test = $sql->execute([
					':page'		=> $Comment['page'],
					':pagetype'	=> $Comment['pagetype'],
					':cID'		=> $Comment['rid']
				]);

				$logEntry = $dbc->prepare(
				"INSERT INTO log
				(username, page, page2, type, timestamp, timezone) VALUES
				(:username, :page, :page2, :type, :timestamp, :timezone)
				");
				$logEntry->execute(array(
					':username'	 => $User,
					':page'		 => $values['page'],
					':page2'	 => $Comment['rid'],
					':type'		 => $logAction['delete'],
					':timestamp' => $timestamp,
					':timezone'	 => $timezone
				));
			} else {
				echo 'error_Permission';
			}
		break;
		default:
			return false;
		break;
	}

	if (isset( $test ) && $test) {
		switch ($_POST['action']) {
			case 'save':
?>
<a name="c-<?php echo $values['rid_new']; ?>" ></a>
<div class="commentfield" data-id="<?php echo $values['rid_new']; ?>" style="display: none;" >
	<a href="<?php echo fl( 'user', ['?' => $User] ); ?>" title="<?php echo $User; ?>" >
		<div class="commentavatar" style="background: url('<?php echo $UserData['usericon']; ?>');" onmouseover="commentTime(1, 'cT__<?php echo $values['rid_new']; ?>')" onmouseout="commentTime(0, 'cT__<?php echo $values['rid_new']; ?>')" >
		</div>
	</a>
	<div id="cT__<?php echo $values['rid_new']; ?>" class="commenttime" ><?php timestamp( $timestamp ); ?></div>
	<div class="comment" data-writer="<?php echo $User; ?>" data-writer-group="<?php #echo (p($writer['rights'], 'allrights')) ? 'highlight' : null; ?>" >
		<div class="commentarrow arrow" ></div>
		<div class="commentcontent" >
		  <div id="cc<?php echo $values['rid_new']; ?>" >
			<?php if (!empty( $values['title'] )) { ?><div class="commenttitle title bw" ><?php echo $values['title']; ?></div><?php } ?>
			<div class="commenttext bw<?php if (empty( $values['title'] )) { echo ' notitle'; } ?>" >
				<?php echo $values['content']; ?>
			</div>
		  </div>
			<?php
				if (p( 'editcomments', $User )) {
			?>
				<div id="ce<?php echo $values['rid_new']; ?>" class="commenteditor" >
					<form method="post" class="e__Comments_Edit_Form commenteditform" data-cID="<?php echo $values['rid_new'];  ?>" >
						<input type="text" name="cTitle" class="cInput cTitle cTitle<?php echo $values['rid_new']; ?>" maxlength="150" autocomplete="off" placeholder="<?php msg( 'comm-edit-ph-title' ); ?>" value="<?php echo $values['title']; ?>" /><br />
						<textarea type="text" name="cContent" class="cTextarea cContent cContent<?php echo $values['rid_new']; ?>" placeholder="<?php $nsUser = 0; if ($nsUser) { msg( 'up-comm-ph-content' ); } else { msg( 'comm-ph-content' ); } ?>" ><?php echo $values['content']; ?></textarea>
						<input type="submit" class="e__Comments_Edit_Submit cInput cSubmit cSubmit<?php echo $values['rid_new']; ?>" value="<?php msg( 'comm-edit-send' ); ?>" />
					</form>
					<div class="commenteditlink" >
						<span class="activateCommentEditor deactivateCommentEditor editlink" onclick="deactivateCommentForm('#ceL<?php echo $values['rid_new']; ?>', '#cc<?php echo $values['rid_new']; ?>', 'ce<?php echo $values['rid_new']; ?>');" ><?php msg( 'comm-edit-quit' ); ?></span>
					</div>
				</div>
			<?php
				}
			?>
		</div>
			<?php
				if (!empty( $User )) {
			?>
			<div id="ceL<?php echo $values['rid_new']; ?>" class="commentedit" >
				<div class="commentreply" >
					<span id="crL<?php echo $values['rid_new']; ?>" class="activateReplyEditor replylink" onclick="activateReplyForm('#cr<?php echo $values['rid_new']; ?>', 'crL<?php echo $values['rid_new']; ?>')" ><?php msg( 'comm-reply' ); ?></span>
				</div>
			<?php
				if (p( 'editcomments', $User )) {
			?>
				<div class="commenteditlink" >
					<span class="activateCommentEditor editlink" onclick="activateCommentForm('#ce<?php echo $values['rid_new']; ?>', 'cc<?php echo $values['rid_new']; ?>', 'ceL<?php echo $values['rid_new']; ?>');" ><?php msg( 'comm-edit' ); ?></span>
				</div>
				<div class="commentdeletelink" >
					<?php
						if (p( 'deletecomments', $Comment['writer'] )) {
							?>
								<form method="post" class="e__Comment_Delete_Form" style="display: inline-block;" >
									<input type="hidden" name="cAction" value="delete" />
									<input type="hidden" name="cRId" value="<?php echo $values['rid_new']; ?>" />
									<input type="submit" id="deleteSubmit_<?php echo $values['rid_new']; ?>" />
									<label for="deleteSubmit_<?php echo $values['rid_new']; ?>" class="e__Comment_Delete_Button deletelink editlink" ><?php msg( 'comm-delete' ); ?></label>
								</form>
							<?php
						}
						if (p( 'hidecomments', $Comment['writer'] )) {
							?>
								<form method="post" class="e__Comment_Hide_Form" style="display: inline-block;" >
									<input type="hidden" name="cAction" value="hide" />
									<input type="hidden" name="cRId" value="<?php echo $values['rid_new']; ?>" />
									<input type="submit" id="hideSubmit_<?php echo $values['rid_new']; ?>" />
									<label for="hideSubmit_<?php echo $values['rid_new']; ?>" class="e__Comment_Hide_Button deletelink editlink" >
										<span class="e__Comment_Hide_Label" ><?php msg( 'comm-hide' ); ?></span>
										<span class="e__Comment_Unhide_Label hide" ><?php msg( 'comm-unhide' ); ?></span>
									</label>
								</form>
							<?php
						}
					?>
				</div>
			<?php
				}
			?>
			</div>
			<?php
				}
			?>
	</div>
	<div class="replyfield" >
		<div id="cr<?php echo $values['rid_new']; ?>" class="writereply" >
			<form method="post" >
				<input type="hidden" name="cAction" value="replynew" />
				<input type="hidden" name="cRId" value="<?php echo $values['rid_new']; ?>" />
				<textarea type="text" name="cContent" class="e__Replies_Content rTextarea rContent" placeholder="<?php msg( 'reply-ph-content' ); ?>" onclick="cRClicked();" onblur="cROnblur();" ></textarea><br />
				<input type="submit" class="e__Replies_Submit rInput rSubmit bluesubmit" value="<?php msg( 'reply-send' ); ?>" />
			</form>
		</div>
		<div class="commentreplies" ></div>
	</div>
</div>
<?php
			break;
			default:
				echo 'success';
			break;
		}
	} else {
		# echo 'error';
	}
}
# REPLIES
if (isset( $_POST['category'] ) && $_POST['category'] == 'e__Reply' && isset( $_POST['action'] ) &&
	isset( $_POST['page'] ) && !empty( $_POST['page'] ) &&
	isset( $_POST['pagetype'] ) && !empty( $_POST['pagetype'] ) &&
	isset( $_POST['cID'] ) && !empty( $_POST['cID'] ))
{
	timestamp( 'GET' );
	$values = [
		'user'			=> $User,
		'rid_new'		=> randstr( 10 ),
		'rid_log'		=> randstr( 10 ),
		'page'			=> $_POST['page'],
		'pagetype'		=> $_POST['pagetype'],
		'timestamp'		=> $timestamp,
		'timezone'		=> $timezone
	];

	$logAction = [
		'edit'		=> 'reply-edit',
		'save'		=> 'reply-new',
		'hide'		=> 'reply-hide',
		'unhide'	=> 'reply-unhide',
		'delete'	=> 'reply-delete'
	];

	$Comment = $dbc->prepare( "SELECT * FROM comments WHERE rid = :cID AND page = :page AND pagetype = :pagetype LIMIT 1" );
	$Comment->execute([
		':cID'		=> $_POST['cID'],
		':page'		=> $_POST['page'],
		':pagetype'	=> $_POST['pagetype']
	]);
	$Comment = $Comment->fetch();

	if ($Comment) {
		$Reply = false;

		if (isset( $_POST['rID'] ) && !empty( $_POST['rID'] )) {
			$Reply = $dbc->prepare( "SELECT * FROM comments WHERE rid = :rID AND page = :page AND pagetype = :pagetype AND toRid = :toRid AND type = :type LIMIT 1" );
			$Reply->execute([
				':rID'		=> $_POST['rID'],
				':page'		=> $_POST['page'],
				':pagetype'	=> $_POST['pagetype'],
				':toRid'	=> $Comment['rid'],
				':type'		=> 'reply'
			]);
			$Reply = $Reply->fetch();
		}

		switch ($_POST['action']) {
			case 'save':
				if (p( 'writereplies' )) {
					if (isset( $_POST['c_Content'] ) && !empty( $_POST['c_Content'] )) {
						$sql = $dbc->prepare(
							'INSERT INTO comments
							(rid, page, pagetype,  content, timestamp, timezone, writer, type, toId, toRid) VALUES
							(:rid, :page, :pagetype, :content, :timestamp, :timezone, :user, :type, :toId, :toRid)'
						);

						$values['content']	= $_POST['c_Content'];

						$test = $sql->execute([
							':user'			=> $User,
							':rid'			=> $values['rid_new'],
							':page'			=> $values['page'],
							':pagetype'		=> $values['pagetype'],
							':timestamp'	=> $timestamp,
							':timezone'		=> $timezone,
							':content'		=> $values['content'],
							':type'			=> 'reply',
							':toId'			=> $Comment['id'],
							':toRid'		=> $Comment['rid']
						]);

						$logEntry = $dbc->prepare(
						"INSERT INTO log
						(username, page, page2, rid, new, type, timestamp, timezone) VALUES
						(:username, :page, :page2, :randID, :new, :type, :timestamp, :timezone)"
						);
						$logEntry->execute([
							':username' 	=> $User,
							':page'		 	=> $values['page'],
							':page2'		=> $values['rid_new'],
							':randID'		=> $values['rid_log'],
							':new' 		 	=> $values['content'],
							':type' 		=> $logAction['save'],
							':timestamp'	=> $timestamp,
							':timezone' 	=> $timezone
						]);
					} else {
						echo 'error_missingInformation';
					}
				} else {
					echo 'error_Permission';
				}
			break;
			case 'edit':
				if ($Reply) {
					if (p( 'editreplies', $Comment['writer'] )) {
						$sql = $dbc->prepare( "UPDATE comments SET content = :content, edited = :logID WHERE rid = :id AND toRid = :toRid AND page = :page AND pagetype = :pagetype" );

						$values['content']	= (empty( $_POST['r_Content'] )) ? '' : $_POST['r_Content'];

						if (!empty( $values['content'] )) {
							if ($Reply['content'] != $values['content']) {

							$logEntry = $dbc->prepare(
							"INSERT INTO log
							(username, page, page2, rid, old, new, type, timestamp, timezone) VALUES
							(:username, :page, :page2, :randID, :old, :new, :type, :timestamp, :timezone)"
							);
							$logEntry->execute([
								':username'		=> $User,
								':page'			=> $values['page'],
								':page2'		=> $Comment['rid'] . '>>>' . $Reply['rid'],
								':randID'		=> $values['rid_log'],
								':old'			=> $Comment['content'],
								':new' 			=> $values['content'],
								':type' 		=> $logAction['edit'],
								':timestamp'	=> $timestamp,
								':timezone' 	=> $timezone
							]);

							$test = $sql->execute([
								':id'			=> $Reply['rid'],
								':toRid'		=> $Comment['rid'],
								':content'		=> $values['content'],
								':page'			=> $values['page'],
								':pagetype'		=> $values['pagetype'],
								':logID'		=> $values['rid_log']
							]);

							} else {
								$test = true; // There are no changes, so there does not need to be a SQL operation. But success is thrown out anyway.
							}
						} else {
							echo 'error_Empty';
						}
					} else {
						echo 'error_Permission';
					}
				} else {
					echo 'error_replyNotFound';
				}
			break;
			case 'hide':
				if ($Reply) {
					if (p( 'hidecomments', $Reply['writer'] )) {
						$sql = $dbc->prepare( "UPDATE comments SET hidden = :hidden WHERE rid = :rID AND page = :page AND toRid = :toRid AND pagetype = :pagetype" );

						if ($Reply['hidden']) {
							$values['hidden']		= false;
							$values['log-action']	= $logAction['unhide'];
						} else {
							$values['hidden']		= true;
							$values['log-action']	= $logAction['hide'];
						}

						$test = $sql->execute([
							':page'		=> $Comment['page'],
							':pagetype'	=> $Comment['pagetype'],
							':rID'		=> $Reply['rid'],
							':toRid'	=> $Comment['rid'],
							':hidden'	=> $values['hidden']
						]);

						$logEntry = $dbc->prepare(
						"INSERT INTO log
						(username, page, page2, type, timestamp, timezone) VALUES
						(:username, :page, :page2, :type, :timestamp, :timezone)
						");
						$logEntry->execute([
							':username'	 => $User,
							':page'		 => $values['page'],
							':page2'	 => $Comment['rid'] . '>>>' . $Reply['rid'],
							':type'		 => $values['log-action'],
							':timestamp' => $timestamp,
							':timezone'	 => $timezone
						]);
					} else {
						echo 'error_Permission';
					}
				} else {
					echo 'error_replyNotFound';
				}
			break;
			case 'delete':
				if ($Reply) {
					if (p( 'deletecomments', $Reply['writer'] )) {
						$sql = $dbc->prepare( "DELETE FROM comments WHERE rid = :rID AND page = :page AND toRid = :toRid AND pagetype = :pagetype" );

						$test = $sql->execute([
							':page'		=> $Comment['page'],
							':pagetype'	=> $Comment['pagetype'],
							':rID'		=> $Reply['rid'],
							':toRid'	=> $Comment['rid']
						]);

						$logEntry = $dbc->prepare(
						"INSERT INTO log
						(username, page, page2, type, timestamp, timezone) VALUES
						(:username, :page, :page2, :type, :timestamp, :timezone)
						");
						$logEntry->execute([
							':username'	 => $User,
							':page'		 => $values['page'],
							':page2'	 => $Comment['rid'] . '>>>' . $Reply['rid'],
							':type'		 => $logAction['delete'],
							':timestamp' => $timestamp,
							':timezone'	 => $timezone
						]);
					} else {
						echo 'error_Permission';
					}
				} else {
					echo 'error_replyNotFound';
				}
			break;
			default:
				return false;
			break;
		}
	} else {
		echo 'error_commentNotFound';
	}

	if (isset( $test ) && $test) {
		switch ($_POST['action']) {
			case 'save':
$Reply = $dbc->prepare("SELECT * FROM comments WHERE rid = :rid AND page = :page AND pagetype = :pagetype AND toRid = :toRid AND writer = :username LIMIT 1");
$Reply->execute([
	':rid'		=> $values['rid_new'],
	':page'		=> $_POST['page'],
	':pagetype' => $_POST['pagetype'],
	':toRid'	=> $Comment['rid'],
	':username'	=> $User
]);
$Reply = $Reply->fetch();

$writer = $dbc->prepare("SELECT * FROM user WHERE username = :username LIMIT 1");
$writer->execute([
	':username' => $Reply['writer']
]);
$writer = $writer->fetch();
?>
<a name="r-<?php echo $Reply['rid']; ?>" ></a>
<div class="reply<?php echo ($Reply['hidden']) ? ' hidden-comment' : ''; ?>" data-writer="<?php echo $Reply['writer']; ?>" >
	<a href="<?php echo fl( 'user', ['?' => $Reply['writer']] ); ?>" title="<?php echo $Reply['writer']; ?>" >
		<div class="replyavatar" style="background: url('<?php echo $writer['usericon']; ?>');" >
			<img src="<?php echo $writer['usericon']; ?>" height="60px" width="60px" />
		</div>
	</a>
	<div id="cc<?php echo $Reply['rid']; ?>" class="replycontent bw" title="<?php timestamp($Reply['timestamp']); ?>" onclick="activateReplyEdit('.del<?php echo $Reply['rid']; ?>', '.ceL<?php echo $Reply['rid']; ?>')" >
		<?php echo $Reply['content']; ?>
	</div>
	<?php
	if (p( 'editreplies', $Reply['writer'] )) {
	?>
	<div id="ce<?php echo $Reply['rid']; ?>" class="commenteditor replyeditor" >
		<form method="post" class="commenteditform" >
			<input type="hidden" name="rAction" class="cAction<?php echo $Reply['rid']; ?>" value="edit" />
			<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
			<textarea type="text" name="cContent" class="cTextarea cContent cContent<?php echo $Reply['rid']; ?>" placeholder="<?php msg( 'reply-ph-content' ); ?>" ><?php echo $Reply['content']; ?></textarea>
			<input type="submit" id="write_cSubmit" class="cInput rSubmit cSubmit<?php echo $Reply['rid']; ?>" value="<?php msg( 'reply-edit-send' ); ?>" />
		</form>
		<div class="commenteditlink" >
			<span class="activateCommentEditor deactivateCommentEditor editlink" onclick="deactivateCommentForm('#ceL<?php echo $Reply['rid']; ?>', '#cc<?php echo $Reply['rid']; ?>', 'ce<?php echo $Reply['rid']; ?>');" ><?php msg( 'comm-edit-quit' ); ?></span>
		</div>
	</div>

	<div id="ceL<?php echo $Reply['rid']; ?>" class="commenteditlink replyeditdeletelinks ceL<?php echo $Reply['rid']; ?>" >
			<span class="activateCommentEditor editlink" onclick="activateCommentForm('#ce<?php echo $Reply['rid']; ?>', 'cc<?php echo $Reply['rid']; ?>', 'ceL<?php echo $Reply['rid']; ?>'); $('#del<?php echo $Reply['rid']; ?>').css('display', 'none');" ><small><?php msg( 'comm-edit' ); ?></small></span>
		</div>
	<div id="del<?php echo $Reply['rid']; ?>" class="commentdeletelink replyeditdeletelinks del<?php echo $Reply['rid']; ?>" >
		<?php
		if (p( 'deletereplies', $Reply['writer'] )) {
			?>
		<form method="post" style="display: inline-block;" >
			<input type="hidden" name="rAction" value="delete" />
			<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
			<input type="submit" id="deleteSubmit<?php echo $Reply['rid']; ?>" />
			<label for="deleteSubmit<?php echo $Reply['rid']; ?>" class="deletelink editlink" ><small><?php msg( 'comm-delete' ); ?></small></label>
		</form>
		<?php
		}
		?>
		<form method="post" style="display: inline-block;" >
			<input type="hidden" name="rAction" value="hide" />
			<input type="hidden" name="cRId" value="<?php echo $Reply['rid']; ?>" />
			<input type="submit" id="hideSubmit<?php echo $Reply['rid']; ?>" />
			<label for="hideSubmit<?php echo $Reply['rid']; ?>" class="deletelink editlink" ><small><?php if (!$Reply['hidden']) { msg( 'comm-hide' ); } else { msg( 'comm-unhide' ); } ?></small></label>
		</form>
	</div>
	<?php
	}
	?>
</div>
<?php
			break;
			default:
				echo 'success';
			break;
		}
	} else {
		# echo 'error';
	}
}

// Requests
if (isset($_POST['page_requests']) && !empty($_POST['action']) && !empty($_POST['randID']) && p('requests')) {
	$randID		= $_POST['randID'];

	switch ($_POST['action']) {
		case 'review':
			if (!empty($_POST['randID']) && !empty($_POST['status'])) {
				$RequestStatus	= $_POST['status'];

				$Request = $dbc->prepare("SELECT * FROM requests WHERE rid = :randID");
				$Request->execute([
				':randID' => $randID
				]);
				$Request = $Request->fetch();

				if ($Request) {
					$RequestType = $Request['type'];

					switch ($RequestType) {
						default:
						break;
						case 'usericon':
							$TargetUser = new User();
							$TargetUser->set_user_by_username($Request['username']);

							$Update = false;

							if ($RequestStatus == 'accept') {
								$MediaDelete = $dbc->prepare(
					"DELETE FROM media WHERE id = (SELECT id FROM (SELECT id FROM media WHERE user = :user AND type = :type ORDER BY timestamp DESC LIMIT 3,1) AS t)"
								);
								$MediaDelete = $MediaDelete->execute([
									':user' => $TargetUser->get_rand_id(),
									':type'		=> 'usericon'
								]);

								$MediaUpload = $dbc->prepare("UPDATE media SET access = :access WHERE rid = :rid");
								$MediaUpload = $MediaUpload->execute([
									':access' => '',
									':rid' => $Request['filelocation']
								]);

								$Accept = false;
								if ($MediaUpload) {
									$Accept = $dbc->prepare("UPDATE user SET usericon = :usericon WHERE username = :username");
									$Accept = $Accept->execute([
										':usericon' => $Request['filelocation'],
										':username' => $Request['username']
									]);
								}

								if ($Accept) {
									$Update = $dbc->prepare("UPDATE requests SET status = :status WHERE rid = :rid");
									$Update = $Update->execute([
									':status' => 'accepted',
									':rid' => $randID
									]);
								}
							} elseIf ($RequestStatus == 'decline') {
								$Media = $dbc->prepare("DELETE FROM media WHERE rid = :rid");
								$Media = $Media->execute([
									':rid' => $Request['filelocation']
								]);

								if ($Media) {
									$Update = $dbc->prepare("UPDATE requests SET status = :status WHERE rid = :rid");
									$Update = $Update->execute([
										':status' => 'declined',
										':rid' => $randID
									]);
								}
							}

							$RequestSuccess	= $Update;
						break;
					}
				}
			}
		break;
		case 'remove':
			$Request = $dbc->prepare("DELETE FROM requests WHERE rid = :randID");
			$Request = $Request->execute([
				':randID' => $randID
			]);

			if ($Request)
				$RequestSuccess = true;
		break;
	}

	echo ($RequestSuccess) ? 'success' : 'error';
}

// ADD: check if page exists or is user page, if parent comment is reply itself