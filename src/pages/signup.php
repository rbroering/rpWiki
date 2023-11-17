<?php

class Login {
	public function signup( $username, $password ) { // Start sign up process
		global $dbc, $Signup, $Error_Message_Singup, $HashOptions;

		$password	= password_hash( $password, $HashOptions['algo'] );
		$user_id	= randStr( 10 );

		try {
			$Test = $dbc->prepare( "INSERT INTO users (username, password, user_id) VALUES (:username, :password, :user_id)" );
			$Test = $Test->execute([
				':username' => $username,
				':password' => $password,
				':user_id'  => $user_id
			]);
		} catch (Exception $e) {
			$Error_Message_Singup = 'Original error message (sql)<hr /><br />' . $e . '<br /><br />';
		}

		if ($Test) {
			$Signup = true;
			$_SESSION['user'] = $user_id;
		} else {
			#return false; // Sign up process failed (not aborted)
			/* Throw error message */
			$Error_Message_Singup .= 'There was an error creating your account. We\'re sorry. Please try signing up again later.';
			/* Error message thrown */
		}
	}
}

class Page extends PageBase {
	private $Signup = array();
	private $Checks = true;
	private $Created = false;
	private $Errors = array();

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-register', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	private function signup() {
		global $GlobalVariables;
		extract( $GlobalVariables );
		
		# $Wiki['hashfuncs']['user_pw']['algo']
		
		if (!empty( $_POST['user'] ) && !empty( $_POST['pw'] ) && !empty( $_POST['pw2'] )) {
			$this->Signup['username'] = $_POST['user'];
			$this->Signup['password'] = $_POST['pw'];
			$this->Checks = true;

			if (!p( 'reg' )) { // Just to make sure, you know...
				$this->Checks = false;
				$this->Errors[] = msg( 'error-action-disabled', 1 ) . msg( 'try_again_later', 1 );
			}

			$FindUsername = $dbc->prepare( 'SELECT username FROM user WHERE LOWER(username) = :username' );
			$FindUsername->execute([
				':username' => strtolower( $this->Signup['username'] )
			]);
			$FindUsername = $FindUsername->rowCount();

			if ($FindUsername !== 0) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-name-exists', 1 );
			}

			if (preg_match_all('/[^A-Z0-9\pL\._\- ]/ui', $this->Signup['username'], $matches)) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-invchar-username', 1, implode(', ', array_unique($matches[0])));
			}
			if (preg_match_all('/[^A-Z0-9\pL\._\- \(\)\/\{\}\/\\,#\+~\*\'´`\"§\$\%\&<>\|=:;\!]/ui', $this->Signup['password'], $matches)) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-invchar-password', 1, implode(', ', array_unique($matches[0])));
			}

			if (strlen( $this->Signup['username'] ) < $Wiki['userdata']['name']['lenmin'] || strlen( $this->Signup['username'] ) > $Wiki['userdata']['name']['lenmax']) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-name-length-range', 1, [strlen( $this->Signup['username'] ), $Wiki['userdata']['name']['lenmin'], $Wiki['userdata']['name']['lenmax']] );
			}

			if (strlen( $this->Signup['password'] ) < $Wiki['userdata']['pw']['lenmin'] || strlen( $this->Signup['password'] ) > $Wiki['userdata']['pw']['lenmax']) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-pw-length-range', 1, [strlen( $this->Signup['password'] ), $Wiki['userdata']['pw']['lenmin'], $Wiki['userdata']['pw']['lenmax']] );
			}

			if ((stristr( strtolower($this->Signup['password']), strtolower($this->Signup['username']) )) ||
				 stristr( strtolower(strrev($this->Signup['password'])), strtolower($this->Signup['username']) )) {
			// if-Statement // First line: Password contains username? Second line: Reversed password contains username?
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-password-is-name', 1 );
			} elseIf (levenshtein(strtolower($this->Signup['password']), strtolower($this->Signup['username'])) <= 4 ||
					  levenshtein(strtolower(strrev($this->Signup['password'])), strtolower($this->Signup['username'])) <= 4) {
			// if-Statement // First line: Password and username diff less than allowed? Second line: Reversed password and username diff less than allowed?
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-name-pw-too-similar', 1 );
			}

			$CountChars = [];
			foreach (count_chars( $this->Signup['password'], 1 ) as $i => $Char) {
				if ($Char / strlen( $this->Signup['password'] ) > 1 / 5)
					$CountChars[] = chr( $i );
			}
			if (!empty( $CountChars )) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-pw-chars', 1, rtrim( implode( ', ', $CountChars ), ', ' ) );
			}

			if ($this->Signup['password'] !== $_POST['pw2']) {
				$this->Checks = false;
				$this->Errors[] = msg( 'signup-pw-mismatch', 1 );
			}

			if (empty( $Wiki['hashfuncs']['user_pw']['algo'] ))
				$Wiki['hashfuncs']['user_pw']['algo'] = PASSWORD_DEFAULT;

			if ($this->Checks) {
				$this->Signup['active'] = true;

				$this->Signup['password']		= password_hash( $this->Signup['password'], $Wiki['hashfuncs']['user_pw']['algo'] );
				$this->Signup['randId']			= randId( 10, 'user' );
				$this->Signup['firstGroups']	= '';

				// Give rights to first user
				if ($dbc->query( 'SELECT COUNT(*) FROM user' )->fetchColumn() == 0) {
					$this->Signup['firstGroups'] = $Wiki['list-groups'][0];
				}

				$SQL_User = $dbc->prepare(
					'INSERT INTO user
					(rid, username, password, rights, signature, usericon, css, js) VALUES
					(:rid, :username, :password, :rights, :signature, :usericon, :css, :js)'
				);
				$SQL_User = $SQL_User->execute([
					':rid'			=> $this->Signup['randId'],
					':username'		=> $this->Signup['username'],
					':password'		=> $this->Signup['password'],
					':rights'		=> $this->Signup['firstGroups'],
					':signature'	=> '–' . $this->Signup['username'],
					':usericon'		=> '', # (!empty( $Wiki['custom']['usericon'] )) ? $Wiki['custom']['usericon'] : '',
					':css'			=> '/* This is your CSS. It will only be loaded for you. */',
					':js'			=> '// This is your JS. It will only be loaded for you.'
				]);

				if ($SQL_User) {
					$this->Created = true;
					
					$SQL_Pref = $dbc->prepare(
						'INSERT INTO pref
						(username, rid, lang) VALUES
						(:username, :rid, :lang)'
					);
					$SQL_Pref = $SQL_Pref->execute([
						':username' => $this->Signup['username'],
						':rid'		=> $this->Signup['randId'],
						':lang'		=> (isset($Wiki['config']['lang']['default']) && !empty($Wiki['config']['lang']['default'])) ? $Wiki['config']['lang']['default'] : 'en'
					]);
				} else
					$SQL_Pref = false;

				if ($SQL_User) {
					$SQL_Page = $dbc->prepare(
						'INSERT INTO pages
						(rid, url, pagetitle, disptitle, content, type) VALUES
						(:rid, :url, :pagetitle, :disptitle, :content, :type)'
					);
					$SQL_Page = $SQL_Page->execute(array(
						':rid' => randId( 10, 'pages' ),
						':url' => $Wiki['namespace']['user']['autoPrefix'] . ':' . $this->Signup['username'],
						':pagetitle' => $this->Signup['username'],
						':disptitle' => $this->Signup['username'],
						':content' => '<!-- This is your userpage. -->',
						':type' => 'userpage'
					));
				} else
					$SQL_Page = false;

				if ($SQL_User) {
					$SQL_Rights = $dbc->prepare(
					'INSERT INTO rights
						(username, staff, helper, sysop, rightsuser, deleteuser, dbdelete, hideuser, trusted, blocked) VALUES
						(:username, :staff, :helper, :sysop, :rightsuser, :deleteuser, :dbdelete, :hideuser, :trusted, :blocked)'
					);
					$SQL_Rights->execute([
						':username' => $this->Signup['username'],
						':staff' => (empty( $this->Signup['firstGroups'] )) ? 0 : 1,
						':helper' => 0,
						':sysop' => 0,
						':rightsuser' => 0,
						':deleteuser' => 0,
						':dbdelete' => 0,
						':hideuser' => 0,
						':trusted' => 0,
						':blocked' => 0
					]);
				} else
					$SQL_Rights = false;

				if ($SQL_User) {
					timestamp( 'GET' );

					$SQL_Log = $dbc->prepare("INSERT INTO log (username, type, rid, timestamp, timezone) VALUES (:username, :type, :rid, :timestamp, :timezone)");
					$SQL_Log->execute([
						':username' => $this->Signup['username'],
						':type' => 'usersignup',
						':rid' => randId( 10, 'log' ),
						':timestamp' => $timestamp,
						':timezone' => $timezone
					]);

					msg( 'register-succeeded', 0, $Wiki['name']['wiki-name'] );

					if ($SQL_Page) {
						echo '<br />';
						msg( 'reg-userpage-created', 0, fl('user', ['?' => $this->Signup['username'], 'new_user']), 1);
					} else {
						msg( 'reg-userpage-fail' );
						echo '<br />';
					}

				} else {
					msg( 'error' );
					msg( 'reg-failed', 0, $Wiki['name']['wiki-name'] );
				}
			} else {
				if (!empty( $this->Errors ))
					return ["errors" => $this->Errors];
			}

			$this->Signup['password'] = '';
			$this->Signup['username'] = '';
			unset( $this->Signup['username'], $this->Signup['password'], $_POST['user'], $_POST['pw'], $_POST['pw2'] );
		} elseif (!empty($_POST)) {
			return ["errors" => [msg('register-err-requiredtextfields', 1)]];
		}
		
		return [];
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

?>
<style type="text/css" >
	.headerlink.registerlink, #userLinks .loggedout .separator {
		display: none;
	}
	#errorList {
		margin-top: 30px;
		padding: 10px 30px;
		background: #f3eaea;
		border-radius: 12px;
	}
	input[type="submit"] {
		padding: 15px 18px;
		background: #60A8ED;
		border: 0;
		border-bottom: 3px solid #113c7d;
		border-radius: 8px;
		box-shadow: 0 0 10px -5px #113c7d;
		color: #ffffff;
		font-weight: 700;
		line-height: 0;
		cursor: pointer;
		transition: .2s;
	}
	input[type="submit"]:hover {
		background: #60A8ED;
		/* border-bottom: 3px solid #28ea3e; */
		box-shadow: 0 0 10px -2px #113c7d;
	}
</style>
<?php
		if (p( 'reg' )) {
			if (!isset( $this->Signup['active'] ) && (empty( $User ) || p( 'reg-while-loggedin' ))) {
				$SignupAttempt = $this->signup();
				
				$HideForm = $this->Created;
				
				if (!$HideForm)
					echo "<span>" . msg('reg-introtext', 1, $Wiki['name']['wiki-name']) . '</span>';
				
				if (array_key_exists("errors", $SignupAttempt)) {
					echo "<ul id=\"errorList\" >\r\n";
					foreach ($SignupAttempt['errors'] as $errorMessage) echo "<li>" . $errorMessage . "</li>\r\n";
					echo "</ul>";
				}
				
				if (!$HideForm) {
?>
<form method="post" class="top30" >
	<input type="hidden" name="send" value="1" /><!-- -->
	<input type="text" class="big-input input-login" name="user" maxlength="<?php echo $Wiki['userdata']['name']['lenmax']; ?>" placeholder="<?php
	msg( 'placeholder-username' ); ?>"<?php if (!empty( $_POST['user'] )) echo ' value="' . $_POST['user'] . '"'; ?> /><br />
	<input type="password" class="big-input input-login top10" name="pw" maxlength="<?php echo $Wiki['userdata']['pw']['lenmax']; ?>" placeholder="<?php
	msg( 'placeholder-password' ); ?>"<?php if (!empty( $_POST['pw'] )) echo ' value="' . $_POST['pw'] . '"'; ?> /><br />
	<input type="password" class="big-input input-login top10" name="pw2" maxlength="<?php echo $Wiki['userdata']['pw']['lenmax']; ?>" placeholder="<?php
	msg( 'placeholder-repeatpassword' ); ?>" /><br />
	<input type="submit" class="big-submit submit-login top10" value="<?php msg( 'btn-register' ); ?>" />
</form>
<?php
				}
			} elseIf (!empty( $User ))
				msg( 'register-err-loggedin' );
		} else {
			msg('error-action-disabled');
			msg('try_again_later');
		}
	}
}