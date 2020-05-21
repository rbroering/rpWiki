<?php

/* This script adds variables that depend on configured
 * variables in Defaults.php and Config.php that can
 * later be accessed in scripts. This helps keeping the
 * Config.php simple and short. No values are overwritten.
*/

// Default values for group settings; insert keys even if unused
foreach ($Wiki['groups'] as $Group_Name => $Group_Settings) {
	if (!isset( $Wiki['groups'][$Group_Name]['show'] ))
		$Wiki['groups'][$Group_Name]['show'] = true;
	if (!isset( $Wiki['groups'][$Group_Name]['show-on-userpage'] ))
		$Wiki['groups'][$Group_Name]['show-on-userpage'] = $Wiki['groups'][$Group_Name]['show'];
	if (!isset( $Wiki['groups'][$Group_Name]['groups-add'] ))
		$Wiki['groups'][$Group_Name]['groups-add'] = [];
	if (!isset( $Wiki['groups'][$Group_Name]['groups-remove'] ))
		$Wiki['groups'][$Group_Name]['groups-remove'] = [];
	if (!isset( $Wiki['groups'][$Group_Name]['types-add'] ))
		$Wiki['groups'][$Group_Name]['types-add'] = [];
	if (!isset( $Wiki['groups'][$Group_Name]['types-remove'] ))
		$Wiki['groups'][$Group_Name]['types-remove'] = [];
}

// Add a msg key for every group for system messages
foreach ($Wiki['groups'] as $Group => $Info) {
	if (!isset( $Info['msg'] ) || empty( $Info['msg'] ))
		$Wiki['groups'][$Group]['msg'] = msg( 'group-' . $Group, 1 );
}
foreach ($Wiki['autogroups'] as $Group => $Info) {
	if (!isset( $Info['msg'] ) || empty( $Info['msg'] ))
		$Wiki['autogroups'][$Group]['msg'] = msg( 'group-' . $Group, 1 );
}

// An array combining defined groups and auto-groups
$Wiki['allgroups'] = array_merge($Wiki['groups'], $Wiki['autogroups']);

// If there are no types, create an empty array
if (empty( $Wiki['types'] ))
	$Wiki['types'] = [];

// Collect all group names in an array
if (!empty( $Wiki['groups'] ))
	$Wiki['list-groups']	= array_keys( $Wiki['groups'] );
else
	$Wiki['list-groups']	= [];

// Collect all group names in a string
$Wiki['list-groups-string']	= implode( ',', $Wiki['list-groups'] );

// Collect all auto-group names in an array
if (!empty( $Wiki['autogroups'] ))
	$Wiki['list-autogroups']	= array_keys( $Wiki['autogroups'] );
else
	$Wiki['list-autogroups']	= [];

// Collect all auto-group names in a string
$Wiki['list-autogroups-string']	= implode( ',', $Wiki['list-autogroups'] );

// Collect all group names (including auto-groups) in an array
if (!empty( $Wiki['allgroups'] ))
	$Wiki['list-allgroups']	= array_keys( $Wiki['allgroups'] );
else
	$Wiki['list-allgroups']	= [];

// Collect all group names (including auto-groups) in a string
$Wiki['list-allgroups-string']	= implode( ',', $Wiki['list-allgroups'] );

if (!empty( $Wiki['types'] )) {
// Collect all type names in an array
	$Wiki['list-types']		= array_keys( $Wiki['types'] );

// Add a msg key for every type for system messages
	foreach ($Wiki['types'] as $Type => $Info) {
		if (!isset( $Info['msg'] ) || empty( $Info['msg'] ))
			$Wiki['types'][$Type]['msg'] = msg( 'type-' . $Type, 1 );
	}
} else
	$Wiki['list-types']		= [];

// Collect all type names in a string
$Wiki['list-types-string']	= implode( ',', $Wiki['list-types'] );

// If a protection level contains a number, interpret
// it as an index of another protection level and in-
// herit its groups.
if (!empty( $Wiki['select-groups']['protection'] )) {
	foreach ($Wiki['select-groups']['protection'] as $i => $Group) {
		foreach ($Group as $Groupname) {
			if (is_int( $Groupname )) {
				if (array_key_exists( $Groupname, $Wiki['select-groups']['protection'] )) {
					foreach (array_values( $Wiki['select-groups']['protection'][$Groupname] ) as $Enter) {
						#array_unshift( $Wiki['select-groups']['protection'][$i], $Enter );
						array_push( $Wiki['select-groups']['protection'][$i], $Enter );
						unset( $Wiki['select-groups']['protection'][$i][$Groupname] );
					}
				}
			}
		}
	}
} else
// If no protection levels are set, create one with
// each group separately.
	$Wiki['select-groups']['protection'] = $Wiki['list-groups'];

// Namespaces: String to array
foreach ($Wiki['namespace'] as $i => $Namespace) {
	if (is_string($Namespace['prefix']))
		$Wiki['namespace'][$i]['prefix'] = [$Namespace['prefix']];
}

// If no base-URL (protocol + full domain name + TLD + slash) is set
// try to detect it.
if (empty( $Wiki['config']['base-url'] ))
	$Wiki['config']['base-url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';