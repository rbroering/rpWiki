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
	 * @param string $id_rand
	 * @return object
	 */
	final public function setUser($id_rand) {
		global $dbc;

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
	 * @param string $username
	 * @return object
	 */
	final public function setUserByName($username) {
		global $dbc;

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
	 * @return boolean
	 */
	final public function exists() {
		return ($this->Data) ? true : false;
	}


	/**
	 * Get user's random ID string
	 *
	 * @return string
	 */
	final public function getRandId() {
		return $this->Data['rid'];
	}


	/**
	 * Get user's user name
	 *
	 * @return string
	 */
	final public function getName() {
		return $this->Data['username'];
	}


	/**
	 * Get user's password, must be authorized by child class method
	 *
	 * @param string $Auth
	 * @return string
	 */
	final public function getPassword($Auth) {
		return ($Auth === $this->Authorize) ? $this->Data['password'] : 'Unauthorized';
	}


	/**
	 * Get user's user icon
	 * 
	 * @param array $size
	 * @return string
	 */
	public function getIcon($size = [200, 200]) {
		global $Wiki;

		if (is_integer($size))
			$size = [$size];
		
		if (count($size) === 1)
			$size = [$size[0], $size[0]];

		$size = ($size[0] == $size[1]) ? 'w' . $size[0] : 'w' . $size[0] . '-h' . $size[1];
		$size = '/' . $size;
		if ($size == '/w200') $size = '';

		return ($this->exists()) ? $Wiki['dir']['usericons'] . $this->Data['usericon'] . $size . '/' . $this->getName() . '.png' : $Wiki['custom']['usericon'];
	}


	/**
	 * Get the user page address
	 *
	 * @return string
	 */
	final public function getPageAddress() {
		return 'User:' . $this->getName();
	}


	/**
	 * Get the user page random id
	 *
	 * @return string
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


	/**
	 * Check whether current user is logged in
	 *
	 * @return boolean
	 */
	final public function isLoggedIn() {
		return $this->isLoggedIn;
	}


	/**
	 * Check if valid log in and create session
	 *
	 * @param string $Username
	 * @param string $Password
	 * @return object
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
	 * @return void
	 */
	final public function logOut() {
		session_destroy();
		$this->isLoggedIn = false;
	}


	/**
	 * Get current user's user icon
	 *
	 * @param array $size
	 * @return string
	 */
	final public function getIcon($size = [200, 200]) {
		global $Wiki;

		return ($this->isLoggedIn()) ? parent::getIcon($size) : $Wiki['custom']['usericon'];
	}


	/**
	 * Get random id array of unread messages on profile
	 *
	 * @return array
	 */
	final public function getUnreadMessages() {
		global $dbc;

		$Messages = $dbc->prepare('SELECT rid FROM comments WHERE page = :page AND hidden = false AND writer != :user AND data NOT IN(:data)');
		$Messages->execute([
			':page' => $this->getPageAddress(),
			':data' => 'user-read-hide',
			':user' => $this->getName()
		]);
		$Messages = $Messages->fetchAll();

		return ($this->isLoggedIn()) ? $Messages : [];
	}
}

/* if (isset( $_SESSION['user'] ))
	$User = $_SESSION['user'];
else
	$User = null; */