<?php
if (!defined('VALIDACCESS')) {
	exit();
}


/* Directories */
$Wiki['dir']['scripts']				= 'scripts/';
$Wiki['dir']['skins']				= 'Skins/';
$Wiki['dir']['langs']				= 'l10n/';
$Wiki['dir']['pages']				= 'pages/';
$Wiki['dir']['media']				= 'media/';
$Wiki['dir']['usercontent']			= $Wiki['dir']['media'] . 'usercontent/';
$Wiki['dir']['usericons']			= $Wiki['dir']['media'] . 'usericon/';
$Wiki['dir']['extensions']			= $Wiki['dir']['pages'] . 'resources/';


/* Localization */
$Wiki['config']['encoding']			= "utf-8";
$Wiki['config']['lang']['codes']	= 'en,de,msg,';
$Wiki['config']['lang']['default']	= 'en';


/* Skins */
$Wiki['config']['skin']				= 'Andromeda';
$Wiki['config']['available-skins']	= 'Andromeda,Project';


/* Error pages */
$Wiki['error-page']['not-found']	= $Wiki['dir']['pages'] . 'error.php';
$Wiki['error-page']['page-implementation-error'] = $Wiki['dir']['pages'] . 'error.php';


/* Users */
$Wiki['userdata']['pw']['lenmin']			= 8;
$Wiki['userdata']['pw']['lenmax']			= 20;
$Wiki['userdata']['name']['lenmin']			= 3;
$Wiki['userdata']['name']['lenmax']			= 26;

/* Users: Media */
$Wiki['custom']['usericon']			= 'custom/usericon.png';
$Wiki['media-config']['usericon']['extensions-regex'] = "/^(png|jpe?g|gif)$/i";
$Wiki['uc']['img']['limit']			= 500000;

/* Users: Passwords */
$Wiki['hashfuncs']['user_pw']['algo']		= defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;


/* URL parameters */
$Wiki['config']['urlparam']['skin']			= 'skin';
$Wiki['config']['urlparam']['language']		= 'lang';
$Wiki['config']['urlparam']['lang']			= $Wiki['config']['urlparam']['language'];
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

$Param['url'] = $Wiki['config']['urlparam'];


/* POST parameters */
$Param['post']['submit']					= 'submit';
$Param['post']['user']						= 'username'; // equivalent to [username]
$Param['post']['username']					= 'username'; // equivalent to [user]
$Param['post']['password']					= 'password';
$Param['post']['reason']					= 'reason';


/* Auto-groups */
$Wiki['autogroups'] = [
	'*'		=> [],
	'users'	=> [],
	'-'		=> []
];


/* User groups */
$Wiki['groups'] = [
    'allrights' => [
        'groups-add'        => [
            'allrights',
            'support',
            'helper',
            'admin',
            'blocked',
            'test_group'
        ],
        'groups-remove'     => [
            'allrights',
            'support',
            'helper',
            'admin',
            'blocked',
            'test_group'
        ],
        'types-add'         => [
            'hidden',
            'nomsg',
            'testing'
        ],
        'types-remove'      => [
            'hidden',
            'nomsg',
            'testing'
        ]
    ],
    'support'   => [
        'show-on-userpage'	=> false,
        'show-in-log'		=> false
    ],
    'helper' => [
        'groups-add'        => [
            'support',
            'admin',
            'blocked',
            'test_group'
        ],
        'groups-remove'        => [
            'admin',
            'blocked',
            'test_group'
        ],
        'groups-remove-self'     => [
            'helper'
        ],
        'types-add'         => [
            'hidden',
            'nomsg',
            'testing'
        ],
        'types-remove'      => [
            'hidden',
            'nomsg',
            'testing'
        ]
    ],
    'admin' => [
        'groups-add'            => [
            'blocked',
            'test_group'
        ],
        'groups-remove'         => [
            'blocked',
            'test_group'
        ],
        'groups-remove-self'     => [
            'admin'
        ],
        'types-add'         => [
            'hidden'
        ],
        'types-remove'      => [
            'hidden'
        ]
    ],
    'blocked' => [
        'groups-remove-self' => []
    ],
    'test_group' => [
        'show-on-userpage'	=> false,
        'show-in-log'		=> false,
        'groups-remove-self' => [
            'test_group'
        ]
    ]
];


/* User types */
$Wiki['types'] = [
	'hidden' => [
		'show-on-userpage'	=> false,
		'show-in-log'		=> false,
        'type-remove-self'  => false
    ],
	'nomsg' => [
		'show-on-userpage'	=> false,
        'type-remove-self'  => true
    ],
	'testing' => [
		'show-on-userpage'	=> false,
        'type-remove-self'  => true
	]
];


/* Page protection levels */
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


/* Namespaces */
$Wiki['namespace'] = [
    'msg' => [
        'autoPrefix'    => 'msg',
        'prefix'        => ['msg'],
        'page'          => [
            'comments'      => false,
            'customtitle'   => false
        ],
        'groups'        => [
            'allrights'
        ]
    ],
    'pagelink' => [
        'autoPrefix'    => 'id',
        'prefix'        => 'id',
        'page'          => [
            'comments'      => false,
            'customtitle'   => false,
        ],
        'groups'        => ['-']
    ],
	'inwikilink' => [
		'autoPrefix'	=> 'w',
		'prefix'		=> 'w',
		'page'          => [
			'comments'      => false,
			'customtitle'   => false
		],
		'groups'		=> ['-']
	],
	'aet' => [
		'autoPrefix'	=> 'AET',
		'prefix'		=> 'aet',
		'groups'		=> ['allrights', 'helper', 'admin']
	],
	'blog' => [
		'autoPrefix'	=> 'Blog',
        'prefix'		=> 'blog',
        'groups'        => ['allrights', 'helper', 'admin', 'own']
	],
	'system' => [
		'autoPrefix'	=> 'System',
		'prefix'        => ['sys', 'system'],
		'page'          => [
			'comments'      => false,
			'customtitle'   => false
		],
		'groups'        => ['allrights']
	],
	'template' => [
		'autoPrefix'	=> 'Template',
		'prefix'		=> 'template',
		'page'			=> [
			'comments'      => false,
			'customtitle'   => false
		],
		'groups'		=> ['allrights', 'helper', 'admin']
	],
	'user' => [
		'autoPrefix'	=> 'User',
		'prefix'		=> 'user',
		'groups'		=> ['allrights', 'helper', 'admin', 'own']
	],
	'help' => [
		'autoPrefix'	=> 'Help',
		'prefix'		=> 'help',
		'groups'		=> ['*']
	]
];


/* WIKI SETUP */


/* Wiki name */
$Wiki['name']['wiki-name']			= 'rpWiki';


/* URL up to base dir */
$Wiki['config']['base-url']			= 'http://localhost/';


/* Database connection */
$Wiki['config']['dbc']['host']		= 'localhost';
$Wiki['config']['dbc']['name']		= $Wiki['name']['wiki-name'];
$Wiki['config']['dbc']['user']		= 'root';
$Wiki['config']['dbc']['pass']		= '';


/* Title tags */
$Wiki['name']['title']				= ' – ' . $Wiki['name']['wiki-name'];
$Wiki['name']['title-start-page']	= $Wiki['name']['wiki-name'];


/* Add scripts */
$Wiki['config']['scripts']			= [ 'node_modules/requirejs/require.js', 'var.js.php' ];


/* Add styles */
$Wiki['config']['styles']			= [];


/* Logos, Wordmarks and Icons */
$Wiki['custom']['logo']['url']		= 'custom/Logo.png';
$Wiki['custom']['logo-small']['url']= 'custom/Logo-small.png';
$Wiki['custom']['icon']['favicon']	= 'custom/Icon.png';
$Wiki['custom']['icon']['apple']	= 'custom/Icon-Apple.png';
$Wiki['custom']['icon']['google']	= 'custom/Icon-Google.png';


/* On-wiki JavaScript page */
$Wiki['custom']['thru_page']['js']	= 'Sys:Main.js';


/* On-wiki CSS page */
$Wiki['custom']['thru_page']['css']	= 'Sys:Main.css';


/* On-wiki pages allowed for CSS rendering */
$Wiki['custom']['thru_page']['allow_css']	= [];


/* Compatibility setting for web apps */
$Wiki['custom']['compatibility']['web-app']	= 0;


/* License footer text */
$Wiki['custom']['footer']['license']	= '';


/* Landing page name (Address for editor) */
$Wiki['config']['startpage']		= 'Home';


/* Account for posting News */
$Wiki['nav']['sidebar']['News']		= 'u/' . $Wiki['name']['wiki-name'] . '?p=blogs#list';


/* Account of user who operates the data base (adds a tag) */
$Wiki['config']['dbusertag']		= $Wiki['name']['wiki-name'];
