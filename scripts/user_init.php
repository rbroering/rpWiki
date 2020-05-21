<?php
if (!defined('VALIDACCESS')) {
	exit();
}

$Actor		= new CurrentUser();

$USER_ID	= $Actor->getRandId();
$USERNAME	= $Actor->getName();

define('USER_ID', $USER_ID);
define('USERNAME', $USERNAME);