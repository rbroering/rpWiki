<?php
/*
* THIS FILE MUST BE INCLUDED IN SCRIPTS AND STYLES THAT USE PHP
* JavaScript and Stylesheets that use PHP to access data look like
* this:
*
* "example.js.php" for a script,
* "example.css.php" for a CSS.
* Please keep this file in the main directory!
*/

session_start();

define('VALIDACCESS', true);

include_once 'Defaults.php';
require_once 'Config.php';

require_once $Wiki['dir']['scripts'] . 'data.php';
require_once $Wiki['dir']['scripts'] . 'func.php';
require_once $Wiki['dir']['scripts'] . 'cont.php';
require_once $Wiki['dir']['scripts'] . 'lang.php';
require_once $Wiki['dir']['scripts'] . 'user.php';
require_once $Wiki['dir']['scripts'] . 'time.php';
require_once $Wiki['dir']['scripts'] . 'link.php';
require_once $Wiki['dir']['scripts'] . 'permissions.php';
require_once $Wiki['dir']['scripts'] . 'html.php';
require_once $Wiki['dir']['scripts'] . 'tags.php';
require_once $Wiki['dir']['scripts'] . 'ui-inputs.php';

require_once 'AutofillVariables.php';

$GlobalImport = compact( 'Wiki', 'User', 'dbc' );
?>