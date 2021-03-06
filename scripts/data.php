<?php
if (!defined('VALIDACCESS')) {
	exit();
}

if (
	empty( $Wiki['config']['dbc']['host'] ) ||
	empty( $Wiki['config']['dbc']['name'] ) ||
	empty( $Wiki['config']['dbc']['user'] ) ||
	!isset( $Wiki['config']['dbc']['pass'] )
)
	exit(
		"Credentials for a connection to a database ".
		"have not been provided to the wiki yet. If ".
		"you are a site administrator, please set up ".
		"a database and make adjustments to Config.php ".
		"accordingly."
	);
else {

try {
	$DbHost = $Wiki['config']['dbc']['host'];
	$DbName = $Wiki['config']['dbc']['name'];
	$DbUser = $Wiki['config']['dbc']['user'];
	$DbPass = $Wiki['config']['dbc']['pass'];

	$dbc = new PDO("mysql:host=$DbHost;dbname=$DbName", $DbUser, $DbPass, array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_EMULATE_PREPARES => true));
} catch(Exception $e) {
	exit(
		"Fatal error: The wiki has been unable to establish ".
		"a connection to the database using the credentials ".
		"specified in Config.php. If you are a site administrator, ".
		"please check for errors with the database and misspellings ".
		"in the credentials."
	);
}

unset( $Wiki['config']['dbc']['host'], $Wiki['config']['dbc']['name'], $Wiki['config']['dbc']['user'], $Wiki['config']['dbc']['pass'] );

};

if (isset($_SESSION['user'])) {
	$User = $user = $_SESSION['user'];

	$UserData = $dbc->prepare('SELECT * FROM user WHERE username = :username LIMIT 1');
	$UserData->execute([
		':username' => $User
	]);
	$UserData = $UserData->fetch();

	/* Updated usericon path */
	$UserData['usericon'] = (!empty($UserData['usericon'])) ? "media/usericon/" . $UserData['usericon'] . "/$User.png" : "custom/usericon.png";

	$UserPref = $dbc->prepare('SELECT * FROM pref WHERE username = :username LIMIT 1');
	$UserPref->execute([
		':username' => $User
	]);
	$UserPref = $UserPref->fetch();

	if ($UserPref['bgfx_heavy'] === null)
		$UserPref['bgfx_heavy'] = true;
	else
	$UserPref['bgfx_heavy'] = boolval($UserPref['bgfx_heavy']);

	$userLoginStatus = 1;
} else {
	$UserData = null;
	$UserPref = [];
	$UserPref['bgfx_heavy'] = true;
	$User = $user = null;
	$userLoginStatus = 0;
}