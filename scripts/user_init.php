<?php
if (!defined('VALIDACCESS')) {
	exit();
}

$Actor		= new CurrentUser();


$USER_ID	= $Actor->isLoggedIn() ? $Actor->getRandId() : null;
$USERNAME	= $Actor->isLoggedIn() ? $Actor->getName() : null;

define('USER_ID', $USER_ID);
define('USERNAME', $USERNAME);
