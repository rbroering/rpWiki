<?php

session_start();

define('VALIDACCESS', true);

mb_internal_encoding("UTF-8");

/* Imports the configuration files for setting up the
 * Wiki. Please avoid changing the variables in the
 * Default.php file, override them in Config.php
 * instead.
*/

include_once 'Defaults.php';

if (!file_exists('Config.php')) {
	exit(
		"This wiki has no configuration file yet. ".
		"If you are a site administrator, please ".
		"create a Config.php file to get this wiki running."
	);
}

require_once 'Config.php';


/* Imports every neccessary resource for the Wiki
 * manually.
 * DO NOT REMOVE UNLESS YOU ARE SURE THAT YOU WILL
 * NOT NEED A SCRIPT!
 * DO NOT CHANGE THE INCLUSION ORDER!
*/
require_once $Wiki['dir']['scripts'] . 'data.php';
require_once $Wiki['dir']['scripts'] . 'user.php';
require_once $Wiki['dir']['scripts'] . 'user_init.php';
require_once $Wiki['dir']['scripts'] . 'func.php';
require_once $Wiki['dir']['scripts'] . 'cont.php';
require_once $Wiki['dir']['scripts'] . 'lang.php';
require_once $Wiki['dir']['scripts'] . 'base.php';
require_once $Wiki['dir']['scripts'] . 'skin.php';
require_once $Wiki['dir']['scripts'] . 'link.php';
require_once $Wiki['dir']['scripts'] . 'time.php';
require_once $Wiki['dir']['scripts'] . 'permissions.php';
require_once $Wiki['dir']['scripts'] . 'html.php';
require_once $Wiki['dir']['scripts'] . 'tags.php';
require_once $Wiki['dir']['scripts'] . 'ui-inputs.php';


/* The section below contains operations similar to
 * these in AutofillVariables.php, but they depend on
 * the scripts imported above to work.
*/
require_once 'AutofillVariables.php';


/* Puts important variables into array "GlobalImport",
 * which can be accessed from functions by using the
 * PHP extract() function:
 * 
 * global $GlobalImport;
 * extract( $GlobalImport );
 * 
 * This will always import variables that include
 * valuable data. If your application uses incommon
 * resources/variables, make sure to import them via
 * global before, to make sure it will be used even
 * when it has been deleted from GlobalImport.
*/
timestamp( 'GET' ); // Getting fallback timestamp

$GlobalImport		= compact( 'Wiki', 'dbc', 'Param', 'User', 'Actor', 'HTML' );
$GlobalVariables	= compact(
	'Wiki', 'dbc', 'Param', 'User', 'Actor', 'UserData',
	'UserPref', 'HTML', 'UI_Inputs', 'timestamp', 'timezone'
);

// Removed inter-page data-exchange variable IPDE

$Skin = new Skin;


/* This detects the page that is requested by the
 * user. Change Wiki['config']['urlparam']['page']
 * for changing the parameter name for the page name.
*/
$System			= [];
$System['page'] = 'index';
if (isset( $Wiki['config']['urlparam']['page'] )) {
	if (!empty( $_GET[$Wiki['config']['urlparam']['page']] ))
		$System['page']	= $_GET[$Wiki['config']['urlparam']['page']];
}

if (substr( $System['page'], 0, 4 ) == "wiki")
	$System['page']		= 'page';


/* This switch allows for multiple diferrent URLs to
 * lead to one connected page in your "pages" folder.
 * Create cases for new URLs and set $System['page']
 * to the target page name as it is in the page folder.
*/
switch ($System['page']) {
	case 'home':
		$System['page'] = 'index';
	break;
	case 'register':
		$System['page'] = 'signup';
	break;
	case 'site':
		$System['page'] = 'page';
	break;
}


/* The following code looks for the page resources in
 * your "pages" path and eventually throws an error
 * when the requested page does not exist. The error
 * page is error.php.
*/
$Var = 0;
while ($Var !== 1) {
	$PageFile = $Wiki['dir']['pages'] . $System['page'] . '.php';
	
	if (file_exists( $PageFile )) {
		include_once $PageFile;

		if (class_exists( 'Page' )) {
			$Page	= new Page;
			$Var	= 1;
		} else {
			if ($Var === 0) {
				$IPDE['error']['case']		= 'page-implementation-error';
				$IPDE['error']['requested']	= $System['page'];

				$System['page'] = 'error';
				$Var = 2;
			} elseIf($Var === 2) {
				$System['error']['cat']		= 'fatal';
				$System['error']['desc']	= 'There is no detailed error page available.';

				$Page	= new PageBase;
				$Var	= 1;
			}
		}
	} elseIf (!file_exists( $PageFile )) {
		$Var = 2;
		$IPDE['error']['case']		= 'not-found';
		$IPDE['error']['requested']	= $System['page'];

		$System['page'] = 'error';
	}
}

$Load = [];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php echo $Wiki['config']['encoding']; ?>" />
		<base href="<?php
		// Absolute path to base directory, so that rewritten relative paths do not mess up resources with relative paths
		echo $Wiki['config']['base-url'];
		?>" />
		<title><?php
			if (!empty( $Page->msg( 'pagetitle' ) ))
				echo $Page->msg( 'pagetitle' ) . $Wiki['name']['title'];
			else
				echo $Wiki['name']['title-start-page'];
		?></title>

		<?php
			if (!empty( $Wiki['custom']['icon']['favicon'] )) {
		?><link rel="icon" type="icon/png" href="<?php echo $Wiki['custom']['icon']['favicon']; ?>" />
		<?php }
			if (!empty( $Wiki['custom']['icon']['apple'] )) {
		?><link rel="apple-touch-icon" type="image/png" href="<?php echo $Wiki['custom']['icon']['apple']; ?>" />
		<?php } ?>

		<meta name="viewport" content="width=device-width, initial-scale=<?php echo (isset( $Wiki['config']['viewport']['scale'] )) ? $Wiki['config']['viewport']['scale'] : '1.0'; ?>, user-scalable=<?php echo (isset( $Wiki['config']['viewport']['scalable'] ) && $Wiki['config']['viewport']['scalable'] == false) ? 'no' : 'yes'; ?>" />
		<?php
			if (isset( $Wiki['custom']['compability']['web-app'] ) && $Wiki['custom']['compatibility']['web-app'] == true) {
		?>
<meta name="mobile-web-app-capable" content="yes" />
		<!-- <meta name="apple-mobile-web-app-capable" content="yes" /> -->
		<?php
			}
		?>

		<script type="text/javascript" src="//code.jquery.com/jquery-latest.min.js" ></script>
		<script type="text/javascript" src="main.js" ></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $Wiki['dir']['skins'] ?>general.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $Wiki['dir']['skins'] ?>default.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $Wiki['dir']['skins'] ?>custom.css.php" />

		<!--  // CUSTOM SCRIPTS/STYLES \\ -->
		<?php
			// CUSTOM JAVASCRIPT
			unset($Var);
			$Var[0] = (!empty( $Wiki['config']['scripts'] ) && is_array( $Wiki['config']['scripts'] )) ? $Wiki['config']['scripts'] : [];
			$Var[1] = (!empty( $Wiki['config']['styles'] ) && is_array( $Wiki['config']['styles'] )) ? $Wiki['config']['styles'] : [];

			// SKIN
			if (!empty( $Skin->Scripts )) {
				if (is_array( $Skin->Scripts )) {
					foreach ($Skin->Scripts as $i => $Val) {
						if (substr( $Skin->Scripts[$i], 0, 1) === '/' && substr( $Skin->Scripts[$i], 1, 1) != '/' )
							$Skin->Scripts[$i] = $Wiki['dir']['current-skin'] . substr( $Val, 1 );
						else
							$Skin->Scripts[$i] = $Val;

						array_push( $Var[0], $Skin->Scripts[$i] );
					}
				}
			}
			if (!empty( $Skin->Styles )) {
				if (is_array( $Skin->Styles )) {
					foreach ($Skin->Styles as $i => $Val) {
						if (substr( $Skin->Styles[$i], 0, 1) === '/' && substr( $Skin->Styles[$i], 1, 1) != '/' )
							$Skin->Styles[$i] = $Wiki['dir']['current-skin'] . substr( $Val, 1 );
						else
							$Skin->Styles[$i] = $Val;

						array_push( $Var[1], $Skin->Styles[$i] );
					}
				}
			}

			// PAGE
			if (!empty( $Page->Scripts )) {
				if (is_array( $Page->Scripts )) {
					foreach ($Page->Scripts as $i => $Val) {
						if (substr( $Page->Scripts[$i], 0, 1) === '/'  && substr( $Page->Scripts[$i], 1, 1) != '/' ) {
							$Page->Scripts[$i] = $Wiki['dir']['pages'] . substr( $Val, 1 );
						} else {
							$Page->Scripts[$i] = $Val;
						}
						array_push( $Var[0], $Page->Scripts[$i] );
					}
				}
			}
			if (!empty( $Page->Styles )) {
				if (is_array( $Page->Styles )) {
					foreach ($Page->Styles as $i => $Val) {
						if (substr( $Page->Styles[$i], 0, 1) === '/'  && substr( $Page->Styles[$i], 1, 1) != '/' ) {
							$Page->Styles[$i] = $Wiki['dir']['pages'] . substr( $Val, 1 );
						} else {
							$Page->Styles[$i] = $Val;
						}
						array_push( $Var[1], $Page->Styles[$i] );
					}
				}
			}

			foreach ($Var[0] as $i => $Val) {
				if (!empty( $Val )) {
					#var_dump( $Val );
					// CUSTOM JAVASCRIPT ?>
<script type="text/javascript" src="<?php echo $Val ?>" ></script>
		<?php	}
			}

			foreach ($Var[1] as $i => $Val) {
				if (!empty( $Val )) {
					// CUSTOM CSS ?>
<link rel="stylesheet" type="text/css" href="<?php echo $Val ?>" />
		<?php	}
			}
		?>
<!--  \\ CUSTOM SCRIPTS/STYLES // -->
<?php
			if (!empty( $Wiki['custom']['thru_page']['css'] ) || !empty( $Wiki['custom']['thru_page']['js'] )) {
		?>

		<!-- WIKI CSS AND JS -->
		<?php
				if (!empty( $Wiki['custom']['thru_page']['css'] )) {
		?><link rel="stylesheet" type="text/css" href="Wiki.css.php" />
		<?php
				}
				if (!empty( $Wiki['custom']['thru_page']['js'] )) {
		?><script type="text/javascript" src="Wiki.js.php" ></script>
		<?php
				}
			}
?>

		<!-- USER CSS AND JS -->
		<style type="text/css" >
			<?php
			if (!empty( $User )) {
				echo $UserData['css'];
			}
		?>

		</style>
		<script type="text/javascript" >
			<?php
			if (!empty( $User )) {
				echo $UserData['js'];
			}
		?>

		</script>
	</head>
	<body data-site="<?php echo $System['page']; ?>" >
		<?php
			$SkinImport = compact( 'Wiki', 'dbc', 'User', 'Actor', 'UserData', 'UserPref', 'Page', 'Load' );
			$Skin->setup([
			'load' => $System['page']
			]);
			$Skin->construct();

			if (!$Actor->isLoggedIn() || $UserPref['bgfx_heavy']) {
		?>
		<!-- For the RuvenProductions website -->
		<style type="text/css" >
			#particles-js {
				height: 100%;
				width: 100%;
				position: fixed;
				top: 0;
				z-index: -2;
			}
		</style>
		<div id="particles-js" ></div>
		<script type="text/javascript" src="node_modules/particles.js/particles.js" ></script>
		<script type="text/javascript" >
			particlesJS.load('particles-js', 'custom/particles.json', function() {
				console.log('callback - particles.js config loaded');
			});
		</script>
<?php
			}
		?>
	</body>
</html>