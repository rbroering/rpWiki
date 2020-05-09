<?php

/* This script adds variables that depend on configured
 * variables in Defaults.php and Config.php that can
 * later be accessed in scripts. This helps keeping the
 * Config.php simple and short. No values are overwritten.
*/

foreach ($Wiki['groups'] as $Group_Name => $Group_Settings) {
	if (!isset( $Wiki['groups'][$Group_Name]['show'] ))
		$Wiki['groups'][$Group_Name]['show'] = true;
	if (!isset( $Wiki['groups'][$Group_Name]['show-on-userpage'] ))
		$Wiki['groups'][$Group_Name]['show-on-userpage'] = $Wiki['groups'][$Group_Name]['show'];
}

foreach ($Wiki['groups'] as $Group => $Info) {
	if (!isset( $Info['msg'] ) || empty( $Info['msg'] ))
		$Wiki['groups'][$Group]['msg'] = msg( 'group-' . $Group, 1 );
}
foreach ($Wiki['autogroups'] as $Group => $Info) {
	if (!isset( $Info['msg'] ) || empty( $Info['msg'] ))
		$Wiki['autogroups'][$Group]['msg'] = msg( 'group-' . $Group, 1 );
}
$Wiki['allgroups'] = array_merge($Wiki['groups'], $Wiki['autogroups']);

if (empty( $Wiki['types'] ))
	$Wiki['types'] = [];

if (!empty( $Wiki['groups'] ))
	$Wiki['list-groups']	= array_keys( $Wiki['groups'] );
else
	$Wiki['list-groups']	= [];
$Wiki['list-groups-string']	= implode( ',', $Wiki['list-groups'] );

if (!empty( $Wiki['autogroups'] ))
	$Wiki['list-autogroups']	= array_keys( $Wiki['autogroups'] );
else
	$Wiki['list-autogroups']	= [];
$Wiki['list-autogroups-string']	= implode( ',', $Wiki['list-autogroups'] );

if (!empty( $Wiki['allgroups'] ))
	$Wiki['list-allgroups']	= array_keys( $Wiki['allgroups'] );
else
	$Wiki['list-allgroups']	= [];
$Wiki['list-allgroups-string']	= implode( ',', $Wiki['list-allgroups'] );

if (!empty( $Wiki['types'] ))
	$Wiki['list-types']		= array_keys( $Wiki['types'] );
else
	$Wiki['list-types']		= [];
$Wiki['list-types-string']	= implode( ',', $Wiki['list-types'] );

if (!empty( $Wiki['select-groups']['protection'] )) {
	foreach ($Wiki['select-groups']['protection'] as $i => $Group) {
		foreach ($Group as $Groupname) {
			if (is_int( $Groupname )) {
				if (array_key_exists( $Groupname, $Wiki['select-groups']['protection'] )) {
					foreach (array_values( $Wiki['select-groups']['protection'][$Groupname] ) as $Enter ) {
						#array_unshift( $Wiki['select-groups']['protection'][$i], $Enter );
						array_push( $Wiki['select-groups']['protection'][$i], $Enter );
						unset( $Wiki['select-groups']['protection'][$i][$Groupname] );
					}
				}
			}
		}
	}
} else
	$Wiki['select-groups']['protection'] = $Wiki['list-groups'];

foreach ($Wiki['namespace'] as $i => $Namespace) {
	if (is_string($Namespace['prefix']))
		$Wiki['namespace'][$i]['prefix'] = [$Namespace['prefix']];
}

if (empty( $Wiki['config']['base-url'] ))
	$Wiki['config']['base-url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';