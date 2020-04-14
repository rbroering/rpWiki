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
	echo "Fatal error: Could not connect to database. Please check your \$Wiki['config']['dbc'] variables (host, name, user, pass) in Config.php!\n";
else {

try {
	$DbHost = $Wiki['config']['dbc']['host'];
	$DbName = $Wiki['config']['dbc']['name'];
	$DbUser = $Wiki['config']['dbc']['user'];
	$DbPass = $Wiki['config']['dbc']['pass'];

$dbc = new PDO("mysql:host=$DbHost;dbname=$DbName", $DbUser, $DbPass, array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_EMULATE_PREPARES => true));

} catch(Exception $e) {
	echo "Fatal error: Could not connect to database.\nException: <b>001</b><br />\n";
	exit();
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