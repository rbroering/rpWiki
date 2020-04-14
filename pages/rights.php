<?php

class Page extends PageBase {

	public function msg ($str) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-rights', 1 );
				break;
			default:
				return '';
				break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if ((isset($_GET['user']) && !empty($_GET['user'])) || (isset($_POST['username']) && !empty($_POST['username']))) {
			$TargetUser['username'] = (isset($_POST['username']) && !empty($_POST['username'])) ? $_POST['username'] : $_GET['user'];

			$Profile['data'] = $dbc->prepare('SELECT * FROM user WHERE username = :username LIMIT 1');
			$Profile['data']->execute(array(':username' => $TargetUser['username']));
			$Profile['data'] = $Profile['data']->fetch();
		}

		if (isset($User)) {
			if (p('editusergroups')) {
				if(!isset($_POST['editrights']) && !isset($_POST['submit'])) {
			?>
				<form method="post" >
					<input type="hidden" name="editrights" /><!-- -->
					<input type="text" name="username" class="fi" placeholder="<?php msg('global-ph-username') ?>" autocomplete="off" <?php if (isset($TargetUser['username'])) { echo 'value="'. $TargetUser['username'] .'" '; } ?>/><br />
					<input type="submit" class="top10" value="<?php msg('rights-btn-edit') ?>" />
				</form>
			<?php
				} elseIf(isset($_POST['editrights']) && !isset($_POST['submit'])) {
					if($Profile['data']) {
						if(ur('allrights', $TargetUser['username']) && !ur('allrights')) {
							msg('action-denied-editrights-allrights');
						} else {
							?>
								<form method="post" id="rightsform" >
									<input type="hidden" name="submit" /><!-- -->
									<h3 class="sectiontitle top0" ><?php msg('rights-section-groups') ?></h3>
									<input type="hidden" name="username" value="<?php echo $TargetUser['username']; ?>" />
									<?php if(!isset($_GET['block'])) { ?><div class="checkbox-list" >
									<?php
										$checkBoxes = array();

										foreach ($Wiki['groups'] as $Groupname => $Group) {
											$checkBoxes[$Groupname] = [
												'checked'	=> ur( $Groupname, $TargetUser['username'] ),
												'label'		=> '<span>' . $Group['msg'] . '</span> <small>(' . $Groupname . ')</small>'
											];
										}
										$this->__insertCheckbox( $checkBoxes );
									?>
									</div><?php } if(isset($_GET['block'])) { ?><div class="checkbox-list" >

									<!-- Blocked -->
									<div class="checkbox" ><input type="checkbox" name="blocked" id="blocked" class="check-hidden" <?php if(ur('blocked', $TargetUser['username'])) { echo 'checked="checked" '; } ?> /><label for="blocked" id="blocked_label" class="check-label <?php if(!ur('blocked', $TargetUser['username'])) { echo 'un'; } ?>checked" data-checked="<?php if(!ur('blocked', $TargetUser['username'])) { echo 'un'; } ?>checked" ><div></div><span class="label-desc" ><?php msg('group-blocked') ?> <small><?php msg('group-sys-blocked') ?></small></span></label></div>

									</div><?php } ?>

									<h3 class="sectiontitle top30" ><?php msg('rights-section-types') ?></h3><div class="checkbox-list" >

									<!-- Hidden -->
									<div class="checkbox" ><input type="checkbox" name="tHidden" id="tHidden" class="check-hidden" <?php if(ur('hidden', $TargetUser['username'], 1)) { echo 'checked="checked" '; } ?> /><label for="tHidden" id="tHidden_label" class="check-label <?php if(!ur('hidden', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" data-checked="<?php if(!ur('hidden', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" ><div></div><span class="label-desc" ><?php msg('group-hidden') ?> <small><?php msg('group-sys-hidden') ?></small></span></label></div>

									<?php if(!isset($_GET['block'])) { ?>

									<!-- Testing -->
									<div class="checkbox" ><input type="checkbox" name="tTesting" id="tTesting" class="check-hidden" <?php if(ur('testing', $TargetUser['username'], 1)) { echo 'checked="checked" '; } ?> /><label for="tTesting" id="tTesting_label" class="check-label <?php if(!ur('testing', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" data-checked="<?php if(!ur('testing', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" ><div></div><span class="label-desc" ><?php msg('group-testing') ?> <small><?php msg('group-sys-testing') ?></small></span></label></div>

									<!-- NoMsgWall -->
									<div class="checkbox" ><input type="checkbox" name="tNomsg" id="tNomsg" class="check-hidden" <?php if(ur('nomsg', $TargetUser['username'], 1)) { echo 'checked="checked" '; } ?> /><label for="tNomsg" id="tNomsg_label" class="check-label <?php if(!ur('nomsg', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" data-checked="<?php if(!ur('nomsg', $TargetUser['username'], 1)) { echo 'un'; } ?>checked" ><div></div><span class="label-desc" ><?php msg('group-nomsg') ?> <small><?php msg('group-sys-nomsg') ?></small></span></label></div>

									</div><?php } ?>

									<div class="invisible-break" ></div>

									<textarea type="text" name="reason" class="big-textarea Areal top50" placeholder="<?php msg('global-ph-reason') ?>" ></textarea><br />
									<input type="submit" class="big-submit top10" value="<?php msg('rights-btn-submit') ?>" />
								</form>
								
							<?php
						}
					} else {
						msg('user_not_existing');
						echo ' <a href="rights" >' . msg('back', 1) . '</a>';
					}
				} elseIf (isset($_POST['submit']) && !isset($_POST['editrights'])) {
					$TargetUser['username'] = $_POST['username'];
					$rId = randStr(10);
					$rights = '';
					$types = '';
					$prefix = 'User:';
					$reason = $_POST['reason'];
					timestamp('GET');
					$Separator = ',';

					$TestUser = $dbc->prepare('SELECT rid, rights, username FROM user WHERE username = :username LIMIT 1');
					$TestUser->execute([
						':username' => $TargetUser['username']
					]);
					$TargetUser = $TestUser->fetch();

					if (!empty( $TargetUser )) {
						/* RIGHTS */
						$SelectedGroups = array('groups' => [], 'types' => []);
						foreach ($Wiki['groups'] as $Groupname => $Group) {
							if (isset( $_POST[$Groupname] )) {
								$rights .= $Groupname . $Separator;
								array_push($SelectedGroups['groups'], $Groupname);
							}
						}
						// ---------
						/* TYPES */
							if (isset($_POST['tHidden'])) {
								$types .= 'hidden,';
								array_push($SelectedGroups['types'], 'hidden');
							}
							if (isset($_POST['tTesting'])) {
								$types .= 'testing,';
								array_push($SelectedGroups['types'], 'testing');
							}
							if (isset($_POST['tNomsg'])) {
								$types .= 'nomsg,';
								array_push($SelectedGroups['types'], 'nomsg');
							}
						// --------
						if (ur('allrights', $TargetUser['username']) && !ur('allrights'))
							echo 'You are not allowed to edit rights of a staff.';
						else {
							if (!ur('allrights') && stristr($rights, 'allrights')) {
								echo $types;
								echo 'You are not allowed to give staff rights.';
							} else {
								# $old = 'groups:' . ur('*', $TargetUser['username']) . 'types:' . ur('*', $TargetUser['username'], 1);
								# $new = 'groups:' . $rights . 'types:' . $types;
								$old = [
									'groups' => array_filter(explode(',', ur('*', $TargetUser['username']))),
									'types' => array_filter(explode(',', ur('*', $TargetUser['username'], true)))
								];
								$new = $SelectedGroups;

								$updateUserRights = $dbc->prepare("UPDATE user SET rights = :userrights, types = :usertypes WHERE username = :username");
								$updateUserRights = $updateUserRights->execute([
									':userrights' => $rights,
									':usertypes' => $types,
									':username' => $TargetUser['username']
								]);

								$action = (!in_array('hidden', $old['types']) && in_array('hidden', $new['types'])) ? 'hideuser' : 'rights';

								$updateLog = $dbc->prepare("INSERT INTO log
									(rid, username, page, pageURL, old, new, type, notice, timestamp, timezone)

									VALUES
									(:rid, :username, :targetuserid, :targetusername, :old, :new, :action, :note, :timestamp, :timezone)");
								$updateLog = $updateLog->execute([
									':rid'				=> randId(),
									':username'			=> $User,
									':targetuserid'		=> $TargetUser['rid'],
									':targetusername'	=> $TargetUser['username'],
									':old'				=> json_encode($old),
									':new'				=> json_encode($new),
									':action'			=> $action,
									':note'				=> $reason,
									':timestamp'		=> $timestamp,
									':timezone'			=> $timezone
								]);

								if ($updateUserRights)
									msg('success-editrights', 0, $TargetUser['username']);
								else {
									msg('error');
									msg('error-editrights');
								}
							}
						}
					}
				}
			} else
				msg('action-denied-editrights');
		} else
			msg('action-denied-editrights');
	}
}
?>