<?php
if (!defined('VALIDACCESS')) {
	exit();
}

$Actor = new CurrentUser();

$USERNAME = $Actor->getName();

define('USERNAME', $USERNAME);