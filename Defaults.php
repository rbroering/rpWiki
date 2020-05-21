<?php
if (!defined('VALIDACCESS')) {
	exit();
}

$Wiki['config']['encoding']			= "utf-8";
$Wiki['config']['lang']['codes']	= 'en,de,msg,';
$Wiki['config']['lang']['default']	= 'en';

$Wiki['name']['wiki-name']			= 'RuvenProductions';
$Wiki['name']['title']				= ' – ' . $Wiki['name']['wiki-name'];
$Wiki['name']['title-start-page']	= $Wiki['name']['wiki-name'];

$Wiki['config']['skin']				= 'Andromeda';
$Wiki['config']['available-skins']	= 'Andromeda,Project';

#$Wiki['hashfuncs']['user_pw']['algo']		= PASSWORD_ARGON2I;

$Wiki['userdata']['pw']['lenmin']			= 8;
$Wiki['userdata']['pw']['lenmax']			= 20;
$Wiki['userdata']['name']['lenmin']			= 3;
$Wiki['userdata']['name']['lenmax']			= 26;

$Wiki['config']['urlparam']['skin']			= 'skin';
$Wiki['config']['urlparam']['lang']			= 'lang';
$Wiki['config']['urlparam']['page']			= 'page';
$Wiki['config']['urlparam']['user']			= 'user';
$Wiki['config']['urlparam']['groups']		= 'rights';
$Wiki['config']['urlparam']['redirect']		= 'cto'; // continue to
$Wiki['config']['urlparam']['redirect_c']	= 'cto'; // continue to
$Wiki['config']['urlparam']['own-profile']	= 'myprofile';
$Wiki['config']['urlparam']['pagename']		= 'url';
$Wiki['config']['urlparam']['blogname']		= 'blog';
$Wiki['config']['urlparam']['pageversion']	= 'ver';
$Wiki['config']['urlparam']['compare']		= 'compare';
$Wiki['config']['urlparam']['versionindex'] = 'versions';
$Wiki['config']['urlparam']['no-redirect']	= 'noredirect';
$Wiki['config']['urlparam']['simpleprotect']= 'simpleprotect';
$Wiki['config']['urlparam']['action']		= 'action';

$Wiki['config']['urlparam']['media-id']		= 'url';
$Wiki['config']['urlparam']['media-size']	= 'size';
$Wiki['config']['urlparam']['media-height']	= 'height';
$Wiki['config']['urlparam']['media-width']	= 'width';
$Wiki['config']['urlparam']['media-crop']	= 'crop';

$Param['post']['submit']					= 'submit';
$Param['post']['user']						= 'username'; // equivalent to [username]
$Param['post']['username']					= 'username'; // equivalent to [user]
$Param['post']['password']					= 'password';
$Param['post']['reason']					= 'reason';

$Param['url'] = $Wiki['config']['urlparam'];

$Wiki['media-config']['usericon']['extensions-regex'] = "/^(png|jpe?g|gif)$/i";

$Wiki['nav']['sidebar']['News']		= 'RuvenProductions';

$Wiki['dir']['scripts']				= 'scripts/';
$Wiki['dir']['skins']				= 'Skins/';
$Wiki['dir']['langs']				= 'l10n/';
$Wiki['dir']['pages']				= 'pages/';
$Wiki['dir']['media']				= 'media/';
$Wiki['dir']['usercontent']			= $Wiki['dir']['media'] . 'usercontent/';
$Wiki['dir']['usericons']			= $Wiki['dir']['media'] . 'usericon/';
$Wiki['dir']['extensions']			= $Wiki['dir']['pages'] . 'resources/';


$Wiki['uc']['img']['limit']			= 500000;

$Wiki['error-page']['not-found']	= $Wiki['dir']['pages'] . 'error.php';
$Wiki['error-page']['page-implementation-error'] = $Wiki['dir']['pages'] . 'error.php';

$Wiki['autogroups'] = [
	'*' => [

	],
	'users' => [
		
	],
	'-' => [

	]
];

$Wiki['groups'] = [
	'allrights' => [
		
	],
	'helper' => [
		
	],
	'admin' => [
		
	],
	'blocked' => [
		
	],
	'test_group' => [
		'show-on-userpage'	=> false,
		'show-in-log'		=> false
	]
];

$Wiki['types'] = [
	'testing' => [
		'show-on-userpage'	=> false,
		'show-in-log'		=> false
	]
];

/* Strongest first */
$Wiki['select-groups']['protection'] = [
	0 => [
		'allrights',
		'helper',
	],
	1 => [
		0,
		'admin'
	],
	2 => [
		'test_group'
	]
];

$Wiki['namespace'] = [
	'inwikilink' => [
		'autoPrefix'	=> 'w',
		'prefix'		=> 'w',
		'page' => [
			'comments' => false,
			'customtitle' => false
		],
		'groups'		=> ['allrights']
	],
	'aet' => [
		'autoPrefix'	=> 'AET',
		'prefix'		=> 'aet',
		'groups'		=> [
			'allrights',
			'helper',
			'admin'
		]
	],
	'blog' => [
		'autoPrefix'	=> 'Blog',
		'prefix'		=> 'blog'
	],
	'system' => [
		'autoPrefix'	=> 'System',
		'prefix' => [
			'sys',
			'system'
		],
		'page' => [
			'comments' => false,
			'customtitle' => false
		],
		'groups' => [
			'allrights'
		]
	],
	'template' => [
		'autoPrefix'	=> 'Template',
		'prefix'		=> 'template',
		'page'			=> [
			'comments'	=> false,
			'customtitle' => false
		],
		'groups'		=> [
			'allrights',
			'helper',
			'admin'
		]
	],
	'user' => [
		'autoPrefix'	=> 'User',
		'prefix'		=> 'user'
	],
	'help' => [
		'autoPrefix'	=> 'Help',
		'prefix'		=> 'help'
	]
];