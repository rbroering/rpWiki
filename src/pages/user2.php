<?php

class Page extends PageBase {
	public $Styles	= [ '/css/user.css', '/resources/comments.css', '/resources/log.css' ];
    public $Scripts	= [ '/resources/comments.js', '/resources/log.js' ];

    private $User;
    private $Error  = false;
	private $Messages;

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
				return 'User profile';
			break;
			case 'notification':
				if (!empty( $this->Messages['hidden'] ))
					return [[
						'class'	=> 'important',
						'msg'	=> $this->Messages['hidden']
					]];
				else
					return false;
			break;
			default:
				return '';
			break;
		}
	}

	public function __construct() {
		global $GlobalVariables;
        extract( $GlobalVariables );

        if (empty($_GET[$Param['url']['user']]) && !isset($_GET[$Param['url']['own-profile']])) {
            $this->Error = true;
            $this->Messages['error'] = msg('error-invalidurl', 1);
            return true;
        }

        $Username = (empty($_GET[$Param['url']['user']])) ? $Actor->getName() : $_GET[$Param['url']['user']];

        $TargetUser = new User();
        $TargetUser->setUserByName($Username);

        $this->User = $TargetUser;

        if (!$this->User->exists())
            return true;

        if (ur('hidden', $this->User->getName(), true)) {
            if (p('view-hidden-user', $this->User->getName())) {
                $Hideuser = $dbc->prepare("SELECT username FROM log WHERE page = :profilepage AND type = 'hideuser' ORDER BY id DESC LIMIT 1");
                $Hideuser->execute([
                    ':profilepage' => 'User:' . $this->User->getName()
                ]);
                $Hideuser = $Hideuser->fetch()['username'];

                $this->Messages['hidden'] = msg('up-hiddenby', 1, al($Hideuser, 'user', ['?' => $Hideuser]));
            } else {
                $this->Error    = true;
                $this->Messages = msg('up-hidden');
            }
        }
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

        if ($this->Error) {
            echo $this->Messages['error'];
            return true;
        }

        if (!$this->User->exists()) {
            msg('up-unknown');
            return true;
        }

        $Username = $this->User->getName();

        ?>
<div id="userpageUsericon" style="background: url('<?= $this->User->getIcon() ?>');" ></div>
                    <div id="userpageHeader" >
                        <div id="usernameHeader" >
                            <div id="usernameUserrights" class="scrolllist" >
                                <h1><?= $Username ?></h1>
                                <div id="userrights" >
                                <?php
                                foreach ($Wiki['list-groups'] as $Val) {
                                    if (ur( $Val, $Username ) && (
                                        !key_exists('show-on-userpage', $Wiki['groups'][$Val]) ||
                                        (
                                            key_exists('show-on-userpage', $Wiki['groups'][$Val]) &&
                                            $Wiki['groups'][$Val]['show-on-userpage']
                                        )
                                    ))
                                        echo "\t" . '<div class="userright-' . $Val . '" >'. msg( 'group-' . $Val, 1 ) .'</div>' . "\r\n\t\t\t\t\t\t\t\t";
                                }
                                if (isset( $Wiki['config']['dbusertag'] ) && $Username === $Wiki['config']['dbusertag'])
                                    echo "\t" . '<div class="userright-dbuser" >'. msg('group-dbuser', 1) .'</div>' . "\r\n\t\t\t\t\t\t\t\t";
                                ?>
</div>
                            </div>
                        </div>
                    </div>
        <?php
	}
}