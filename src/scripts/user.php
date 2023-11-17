<?php
if (!defined('VALIDACCESS')) {
	exit();
}

class UserChecks {
	public function isValidName($name) {
		return true;
	}
}


class User {
	private $Data;
	protected $Authorize;

	public function __construct() {
		$this->Authorize = bin2hex(random_bytes(32));
	}

	/**
	 * Set user by their random ID string
	 *
	 * @param	string	$id_rand
	 */
	public function setUser($id_rand) {
		global $dbc;

		if (empty($id_rand)) {
			$this->Data = false;
			return;
		}

		$GetUser = $dbc->prepare('SELECT * FROM user WHERE rid = :rid LIMIT 1');
		$GetUser->execute([
			':rid' => $id_rand
		]);
		$GetUser = $GetUser->fetch();

		$this->Data = $GetUser;
	}


	/**
	 * Set user by their unique user name
	 *
	 * @param	string	$username
	 */
	public function setUserByName($username) {
		global $dbc;

		if (empty($username)) {
			$this->Data = false;
			return;
		}

		$GetUser = $dbc->prepare('SELECT * FROM user WHERE username = :username LIMIT 1');
		$GetUser->execute([
			':username' => $username
		]);
		$GetUser = $GetUser->fetch();

		$this->Data = $GetUser;
	}


	/**
	 * Check whether the user exists
	 *
	 * @return	boolean
	 */
	final public function exists() {
		return ($this->Data) ? true : false;
	}


	/**
	 * Get user's random ID string
	 *
	 * @return	string
	 */
	final public function getRandId() {
		return ($this->exists()) ? $this->Data['rid'] : false;
	}


	/**
	 * Get user's user name
	 *
	 * @return	string
	 */
	final public function getName() {
		return $this->exists() ? $this->Data['username'] : '';
	}


	/**
	 * Get user's password, must be authorized by child class method
	 *
	 * @param	string	$Auth
	 * @return	string
	 */
	final public function getPassword($Auth) {
		return ($Auth === $this->Authorize) ? $this->Data['password'] : 'Unauthorized';
	}


	/**
	 * Check if two users are the same
	 * 
	 * @param	object	$UserObj
	 * @return	boolean
	 */
	final public function isUser($UserObj) {
		return ($UserObj->getRandId() === $this->getRandId());
	}


	/**
	 * Get user's groups and list them in an array
	 *
	 * @return	array
	 */
	final public function listGroups() : array {
		global $Wiki;

		if (!$this->exists()) return [];

		$Groups = explode(',', rtrim($this->Data['rights'], ','));

		// Remove deprecated groups
		foreach ($Groups as $i => $Group) {
			if (!array_key_exists($Group, $Wiki['groups']))
				unset($Groups[$i]);
		}

		return $Groups;
	}


	/**
	 * Get user's groups and list them in a string
	 * The string will separate the groups with a
	 * comma.
	 *
	 * @param	string	$connector	A string which will glue the groups together
	 * @param	bool	$trail		Whether to add a trailing connector to the list
	 * @return	string
	 */
	final public function listGroupsInString(string $connector = ',', bool $trail = false) : String {
		return implode($connector, $this->listGroups()) . ($trail ? $connector : '');
	}


	/**
	 * Check if user is in a given group
	 *
	 * @param	string	$Group
	 * @return	boolean
	 */
	final public function isInGroup($Group) {
		return in_array($Group, $this->listGroups());
	}


	/**
	 * Get user's groups and list them in an array
	 *
	 * @return	array
	 */
	final public function listTypes() {
		return explode(',', rtrim($this->Data['types'], ','));
	}


	/**
	 * Check if a user is of a given type
	 *
	 * @param	string	$Type
	 * @return	boolean
	 */
	final public function isOfType($Type) {
		return in_array($Type, $this->listTypes());
	}


	/**
	 * Check if a user is blocked
	 *
	 * @return	boolean
	 */
	final public function isBlocked() {
		return $this->exists() && in_array('blocked', $this->listGroups());
	}


	/**
	 * Check if a user has the permission for a given
	 * action based on settings.
	 *
	 * @param	string	$Auth
	 * @return	boolean
	 */
	public function hasPermission($Action, $Self = "") {
		global $GlobalImport;
		extract($GlobalImport);

		// Deny if user is blocked
		if ($this->isBlocked()) return false;

		$Allowed = $dbc->prepare("SELECT groups, users FROM permissions WHERE permission = :permission LIMIT 1");
		$Allowed->execute([
			':permission' => $Action
		]);
		$Allowed = $Allowed->fetch();

		// Action not found in permissions table
		if (!$Allowed) {
			echo "Fatal error: There is no entry in the 'permissions' database table for \"$Action\". Please contact a developer for further help.";
			return false;
		}

		$Groups = $Allowed['groups'];
		$Users	= (!empty($Allowed['users'])) ? explode(',', rtrim($Allowed['users'])) : false;

		switch ($Groups) {
			case '*': return true;		// Allow for everyone
			case '-': return false;		// Disallow for everyone
			case 'users': return $this->exists();	// Allow for users
			default:
				$Groups = explode(',', rtrim($Groups));

				if ($Users && in_array($this->getName(), $Users, true))
					return true;		// Permission for specified users

				if (in_array('own', $Groups) && !empty($Self) && ($Self == $this->getName()) || $Self === $this->getRandId())
					return true;		// Permission as owner

				foreach ($Groups as $Group) {
					if ($this->isInGroup($Group))
						return true;	// Allow for group
				}

				return false;			// If none of the above apply, then deny.
		}

		return false;	// Fallback -- If in doubt, deny.
	}


	/**
	 * Get user's user icon
	 * 
	 * @param	array	$size
	 * @return	string
	 */
	public function getIcon($size = [200, 200], $usecase = false) {
		global $Wiki;

		if (is_integer($size))
			$size = [$size];
		
		if (count($size) === 1)
			$size = [$size[0], $size[0]];

		$size = ($size[0] == $size[1]) ? 'w' . $size[0] : 'w' . $size[0] . '-h' . $size[1];
		$size = '/' . $size;
		if ($size == '/w200') $size = '';

		$url = (($this->exists() && !empty($this->Data['usericon'])) || $this->Data['usericon'] === $Wiki['custom']['usericon'])
			? $Wiki['dir']['usericons'] . $this->Data['usericon'] . $size . '/' . $this->getName() . '.png'
			: $Wiki['custom']['usericon'];

		switch ($usecase) {
			case 'cssurl':
				$url = "url('$url')";
			break;
		}

		return $url;
	}


	/**
	 * Get the user page address
	 *
	 * @return	string
	 */
	final public function getPageAddress() {
		global $Wiki;

		return 'User:' . $this->getName();
	}


	/**
	 * Get the user page random id
	 *
	 * @return	string
	 */
	final public function getPageRandId() {
		global $dbc;

		$ProfilePage = $dbc->prepare('SELECT rid FROM pages WHERE url = :url LIMIT 1');
		$ProfilePage->execute([
			':url' => $this->getPageAddress()
		]);
		$ProfilePage = $ProfilePage->fetch();

		return $ProfilePage['rid'];
	}
}


class CurrentUser extends User {
	private $isLoggedIn;

	/**
	 * Constructor function
	 * 
	 * Automatically sets user if logged-in
	 */
	public function __construct() {
		if (isset($_SESSION['user']))
			$this->setUserByName($_SESSION['user']);

		if ($this->exists())
			$this->isLoggedIn = true;
		else {
			unset($_SESSION['user'], $_SESSION['user_id']);
		}
	}


	// Override parent method that shall not be usable
	/* debug_backtrace() */
	/*
	final public function setUser($µ) { throw new Exception("Trying to set user for CurrentUser object."); exit(0); }
	final public function setUserByName($µ) { throw new Exception("Trying to set user for CurrentUser object."); exit(0); }
	*/


	/**
	 * Check whether current user is logged in
	 *
	 * @return	boolean
	 */
	final public function isLoggedIn() {
		return $this->isLoggedIn;
	}


	/**
	 * Check if valid log in and create session
	 *
	 * @param	string $Username
	 * @param	string $Password
	 * @return	object
	 */
	final public function logIn($Username, $Password) {
		global $Wiki, $dbc;

		#session_start();

		$success	= false;
		$errors		= [];
		$info		= [];

		// Set default Hash Algo if not set in Wiki config
		if (empty( $Wiki['hashfuncs']['user_pw']['algo'] ))
				$Wiki['hashfuncs']['user_pw']['algo'] = PASSWORD_DEFAULT;

		// Create class for target user and initiate
		$TargetUser = new User();
		$TargetUser->setUserByName($Username);

		// Check if target user exists
		if ($TargetUser->exists()) {
			$TargetPassword = $dbc->prepare('SELECT password FROM user WHERE rid = :rid LIMIT 1');
			$TargetPassword->execute([
				':rid' => $TargetUser->getRandId()
			]);
			$TargetPassword = $TargetPassword->fetch()['password'];

			// Verify password
			if (password_verify($Password, $TargetPassword)) {
				// Rehash password if updated Hash Algo
				if (password_needs_rehash($TargetPassword, $Wiki['hashfuncs']['user_pw']['algo'])) {
					$UpdatePassword = $dbc->prepare('UPDATE user SET password = :password WHERE rid = :rid LIMIT 1');
					$UpdatePassword->execute([
						':password'	=> password_hash($Password, $Wiki['hashfuncs']['user_pw']['algo']),
						':rid'		=> $TargetUser->getRandId()
					]);

					$info[]		= 'password-rehashed';
				}

				// Create session
				$_SESSION['user']		= $Username;
				$_SESSION['user_id']	= $TargetUser->getRandId();

				$this->setUser($TargetUser->getRandId());

				$success	= true;
			} else {
			// Password is wrong
				$success	= false;
				$errors[]	= 'wrong-password';
			}
		} else {
		// Target user does not exist
			$success	= false;
			$errors[]	= 'user-does-not-exist';
		}

		// Return status (success => 'success' | 'error') and errors (errors => 'user-does-not-exist')
		return [
			'success'	=> $success,
			'errors'	=> $errors,
			'info'		=> $info
		];
	}


	/**
	 * Destroy sessions
	 *
	 * @return	void
	 */
	final public function logOut() {
		session_destroy();
		$this->isLoggedIn = false;
	}


	/**
	 * Get current user's user icon
	 *
	 * @param	array	$size
	 * @return	string
	 */
	final public function getIcon($size = [200, 200], $usecase = false) {
		global $Wiki;

		return ($this->isLoggedIn()) ? parent::getIcon($size, $usecase) : $Wiki['custom']['usericon'];
	}


	/**
	 * Get random id array of unread messages on profile
	 *
	 * @return	array
	 */
	final public function getUnreadMessages() {
		global $dbc;

		// If not logged in: No unread messages
		if (!$this->isLoggedIn()) return [];

		// If logged in: Fetch unread messages
		$Messages = $dbc->prepare('SELECT rid FROM comments WHERE page = :page AND hidden = false AND writer != :user AND data NOT IN(:data)');
		$Messages->execute([
			':page' => $this->getPageAddress(),
			':data' => 'user-read-hide',
			':user' => $this->getName()
		]);
		$Messages = $Messages->fetchAll();

		return $Messages;
	}
}
