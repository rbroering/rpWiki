<?php

/*if (!function_exists('ur')) {

	// USER RIGHTS
	function ur($str, $usernamefunc = 1, $GoT = 0) {
		// GoT = Group or Type
		global $dbc;
		global $User;
		global $username;

		if($usernamefunc != 1) {
			$fUser = $usernamefunc;
		} else {
			if(isset($username)) {
				$fUser = $username;
			} else {
				if(isset($User)) {
					$fUser = $User;
				} else {
					unset($fUser);
					return false;
				}
			}
		}

		$query = $dbc->prepare("SELECT * FROM user WHERE username = :username LIMIT 1");
		$userN = $query->execute(array(':username' => $fUser));
		$userR = $query->fetch();

		if($GoT === 1) {
			$userR = $userR['types'];
		} else {
			$userR = $userR['rights'];
		}
		if(!$userN) {
			unset($fUser);
		}

		if($str == '*') {
			return $userR;
		} else {
			if(isset($fUser)) {
				if(stristr($userR, $str) == true) {
					return 1;
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		}
	}

}*/

if (!function_exists('ur')) {

	// USER RIGHTS
	function ur( $Group, $_User = false, $Type = false ) {
		global $GlobalImport;
		extract( $GlobalImport );

		// When $_User is a boolean set to true, it is assumed that this means that the function shall check for a User's Types.
		/*if ($_User === true) {
			$Type = true;
		}

		// When $_User is a boolean and $Type is a string, it is assumed that their values have been switched.
		if (is_bool( $_User ) && is_string( $Type )) {
			$_Type	= $_User;
			$_User	= $Type;
			$Type	= $_Type;
		}

		if ($_User === false) {
			if (!empty( $User ))
				$_User = $User;
			else
				return false;
		}*/

		if ($Group === 'users' && $User)
			return true;
		elseIf ($Group === 'users' && !$User)
			return false;

		if ($_User) {
			if (is_bool( $_User ) && is_string( $Type )) {
				$_Type	= $_User;
				$_User	= $Type;
				$Type	= $_Type;
			} elseIf (is_bool( $_User ) && $Type === false) {
				$Type = $_User;
				if (!empty( $User ))
					$_User = $User;
				else
					return false;
			}
		} elseIf (!$_User) {
			if (!empty( $User ))
				$_User = $User;
			else
				return false;
		}

		$Test = $dbc->prepare( "SELECT * FROM user WHERE username = :username LIMIT 1" );
		$Test->execute([
			':username' => $_User
		]);
		$Test = $Test->fetch();

		switch ($Type) {
			case true:
			case 'types':
				$Type = 'types';
			break;
			default:
				$Type = 'rights';
			break;
		}

		if ($Test) {
			if ($Group === '*')
				return $Test[$Type];
			else
				if (!empty($Group))
					return (stristr( $Test[$Type], $Group )) ? true : false;
				else
					return false;
		} else
			return false;
	}

}

if (!function_exists('p')) {

	function p($Action, $CheckUser = false, $OutputAll = false) {
		global $GlobalImport;
		extract( $GlobalImport );

		if (!ur( 'blocked' )) {
			$Permission = $dbc->prepare( "SELECT * FROM permissions WHERE permission = :permission LIMIT 1" );
			$Permission->execute([
				':permission' => $Action
			]);
			$Permission = $Permission->fetch();

			if ($Permission) {
				if (is_array( $OutputAll )) {
					foreach( $OutputAll as $Output ) {
						return $Output . ' = {' . $Permissions[$Output] . '} ';
					}
				}
				#if ($OutputAll === true) {
				#	return $Permissions['groups'];
				#} elseIf ($OutputAll === 'users') {
				#	return $Permissions['users'];
				#}

				if ($Permission['groups'] === '*') {
					return true;
				} elseIf ($Permission['groups'] === 'users') {
					return (empty( $User )) ? false : true;
				} elseIf ($Permission['groups'] === '-') {
					return false;
				} else {
					$Test				= 0;
					$Permission_Groups	= explode( ',', $Permission['groups'] );
					$Permission_Users	= explode( ',', $Permission['users'] );
					$User_Groups		= explode( ',', ur( '*' ) );

					if (count( array_intersect( $Permission_Groups, $User_Groups ) ) > 0) {
						$Test++;
					}

					if (!empty( $User ) && count( array_intersect( $Permission_Users, array( $User ) ) ) > 0) {
						$Test++;
					}

					if (in_array( 'own', $Permission_Groups ) && $CheckUser) {
						if ($CheckUser === $User)
							$Test++;
					}

					return ($Test > 0) ? true : false;
				}
			} else {
				echo "Fatal error: There is no entry in the 'permissions' database table for \"$Action\". Please contact a developer for further help.";
				return false;
			}
		} else {
			return false;
		}
	}

}

if (!function_exists('pold')) {

	// PERMISSIONS
	function pold($action, $own = 0, $return = 0) {
		global $dbc;
		global $User;

		$pq = $dbc->prepare("SELECT * FROM permissions WHERE permission = :action LIMIT 1");
		$pn = $pq->execute(array(':action' => $action));
		$pq = $pq->fetch();
		$p = $pq['groups'];
		$pu = $pq['users'];
		$pe = explode(',', $p);
		$eu = explode(',', ur('*', $User));

		if($return !== 1) {

		if(!$pn) {
			echo "Error: no entry in database table 'permissions' for $action";
			return false;
		} else {
			//if(isset($User)) {
				if($p == '*') {
					return true;
				} elseIf($p == '-') {
				   return false; 
				} else {
					if(isset($User)) {
						$re = 0;
						if(str_ireplace($eu, '', $p) != $p) {
							$re++;
						} else { return ' '; }
						if((stristr($p, ',own') || stristr($p, 'own,') || $p === 'own') && $own === $User) {
							$re++;
						}
						if(str_ireplace($User, '', $pu) != $pu) {
							$re++;
						}
						if($re > 0) {
							return true;
						} else {
							return false;
						}
					}
				}
			/*} else {
				return false;
			}*/
		}

		} else {
			return $p;
		}
	}

}