<?php

/* Wiki name */
$Wiki['name']['wiki-name']            = 'Example Wiki';


/* URL up to base dir */
$Wiki['config']['base-url']           = 'https://example.com/';


/* Directories */
$Wiki['dir']['usericons']             = $Wiki['dir']['media'] . 'usericon/';


/* Database connection */
$Wiki['config']['dbc']['host']        = 'localhost';
$Wiki['config']['dbc']['name']        = 'rpwiki';
$Wiki['config']['dbc']['user']        = 'root';
$Wiki['config']['dbc']['pass']        = 'your_db_password';


/* Title tags */
$Wiki['name']['title']                = ' – ' . $Wiki['name']['wiki-name'];
$Wiki['name']['title-start-page']     = $Wiki['name']['wiki-name'];


/* Logos, Wordmarks and Icons */
$Wiki['custom']['logo']['url']        = 'custom/Wordmark.png';
$Wiki['custom']['logo-small']['url']  = 'custom/Wordmark_small.png';
$Wiki['custom']['icon']['favicon']    = 'custom/Icon.png';
$Wiki['custom']['icon']['apple']      = 'custom/Touchicon.png';
$Wiki['custom']['icon']['google']     = 'custom/Touchicon.png';


/* Compatibility setting for web apps */
$Wiki['custom']['compatibility']['web-app'] = 1;


/* License footer text */
$Wiki['custom']['footer']['license']  = '';


/* Landing page name (Address for editor) */
$Wiki['config']['startpage']          = 'Home';


/* Account for posting News */
$Wiki['nav']['sidebar']['News']       = "/u/News?p=blogs";


/* Account of user who operates the data base (adds a tag) */
$Wiki['config']['dbusertag']          = '';


/* ------------------------------------------ */


/* Localization */
$Wiki['config']['lang']['default']    = 'en';


/* Users */
$Wiki['userdata']['pw']['lenmin']     = 8;
$Wiki['userdata']['pw']['lenmax']     = 128;
$Wiki['userdata']['name']['lenmin']   = 3;
$Wiki['userdata']['name']['lenmax']   = 16;


/* URL parameters */
$Wiki['config']['urlparam']['skin']          = 'skin';
$Wiki['config']['urlparam']['lang']          = 'lang';
$Wiki['config']['urlparam']['page']          = 'page';
$Wiki['config']['urlparam']['user']          = 'user';
$Wiki['config']['urlparam']['groups']        = 'groups';
$Wiki['config']['urlparam']['redirect']      = 'goto'; // continue to
$Wiki['config']['urlparam']['redirect_c']    = 'goto'; // continue to
$Wiki['config']['urlparam']['own-profile']   = 'myprofile';
$Wiki['config']['urlparam']['pagename']      = 'address';
$Wiki['config']['urlparam']['blogname']      = 'blog';
$Wiki['config']['urlparam']['pageversion']   = 'version';
$Wiki['config']['urlparam']['compare']       = 'compare';
$Wiki['config']['urlparam']['versionindex']  = 'versionindex';
$Wiki['config']['urlparam']['no-redirect']   = 'noredirect';
$Wiki['config']['urlparam']['simpleprotect'] = 'simpleprotect';
$Wiki['config']['urlparam']['action']        = 'action';

$Param['url'] = $Wiki['config']['urlparam'];


/* User groups */
$Wiki['groups'] = [
    'staff' => [
        'groups-add' => [
            'staff',
            'support',
            'blocked',
            'test_group'
        ],
        'groups-remove' => [
            'staff',
            'support',
            'blocked',
            'test_group'
        ],
        'types-add' => [
            'hidden',
            'nomsg'
        ],
        'types-remove' => [
            'hidden',
            'nomsg'
        ]
    ],
    'support' => [
        'show-on-userpage'  => true,
        'show-in-log'       => true
    ],
    'blocked' => [
        'group-remove-self' => false
    ],
    'test_group' => [
        'show-on-userpage'  => false,
        'show-in-log'       => true,
        'group-remove-self' => true
    ]
];


/* User types */
$Wiki['types'] = [
    'hidden' => [
        'show-on-userpage'  => false,
        'show-in-log'       => false,
        'type-remove-self'  => false
    ],
    'nomsg' => [
        'show-on-userpage'  => false,
        'type-remove-self'  => true
    ]
];


/* Page protection levels */
/* Strongest first */
$Wiki['select-groups']['protection'] = [
    0 => [
        'staff'
    ]
];


/* Namespaces */
$Wiki['namespace'] = array_merge($Wiki['namespace'], [
    'inwikilink' => [
        'autoPrefix'        => 'w',
        'prefix'            => 'w',
        'page' => [
            'comments'      => false,
            'customtitle'   => false
        ],
        'groups'            => ['staff']
    ],
    'blog' => [
        'autoPrefix'        => 'Blog',
        'prefix'            => 'blog'
    ],
    'system' => [
        'autoPrefix'        => 'System',
        'prefix' => [
            'sys',
            'system'
        ],
        'page' => [
            'comments'      => false,
            'customtitle'   => false
        ],
        'groups' => [
            'staff'
        ]
    ],
    'template' => [
        'autoPrefix'        => 'Template',
        'prefix'            => 'template',
        'page' => [
            'comments'      => false,
            'customtitle'   => false
        ],
        'groups' => [
            'staff',
            'helper',
            'admin'
        ]
    ],
    'user' => [
        'autoPrefix'        => 'User',
        'prefix' => [
            'user',
            'benutzer'
        ]
    ],
    'hilfe' => [
        'autoPrefix'        => 'Hilfe',
        'prefix'            => 'hilfe'
    ]
]);
