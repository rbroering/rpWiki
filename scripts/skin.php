<?php

function file_exists_ci($file) {
    if (file_exists($file))
		return $file;

	$browse = glob(dirname($file) . '/*', GLOB_NOSORT);
    foreach ($browse as $compare)
        if (strtolower($file) === strtolower($compare))
            return $compare;

    return false;
}

if (isset( $_GET[$Wiki['config']['urlparam']['skin']] ) && !empty( $_GET[$Wiki['config']['urlparam']['skin']] ))
	$System['skin'] = $_GET[$Wiki['config']['urlparam']['skin']];
elseif (isset($User) && !empty($UserPref['skin']))
	$System['skin'] = $UserPref['skin'];

if (!isset($System['skin'])
	|| !array_search(strtolower($System['skin']), array_map('strtolower', explode(',', $Wiki['config']['available-skins']))))
	$System['skin'] = $Wiki['config']['skin'];

switch (strtolower($System['skin'])) {
	case 'andromeda':
		$System['skin'] = 'Andromeda';
		break;
	case 'project':
		$System['skin'] = 'Project';
		break;
}
/*if (!isset( $System['skin'] ) || !in_array( $System['skin'], explode( ',', $Wiki['config']['available-skins'] ) ))
	$System['skin'] = $Wiki['config']['skin'];*/

$Wiki['dir']['current-skin'] = $Wiki['dir']['skins'] . $System['skin'] . '/';

// if (file_exists( $Wiki['dir']['current-skin'] . $System['skin'] . '.php' ))
// 	require_once $Wiki['dir']['current-skin'] . $System['skin'] . '.php';
if ($File = file_exists_ci( $Wiki['dir']['current-skin'] . $System['skin'] . '.php' ))
	require_once $File;
	# require_once $Wiki['dir']['current-skin'] . $System['skin'] . '.php';
else
	require_once $Wiki['dir']['skins'] . 'Andromeda/Andromeda.php';

$Skin = new Skin;
