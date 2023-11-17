<?php

class Page extends PageBase {
    private $Status = 0;
    private $Errors = [];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
			    return msg( 'pt-login', 1 );
			break;
			default:
			    return '';
			break;
		}
	}

	public function __construct() {
		global $GlobalVariables;
        extract($GlobalVariables);

        $this->Status = ($Actor->isLoggedIn()) ? 3 : 0;

		if (isset($_POST['send'])) {
			$System['username'] = $_POST['username'];
            $System['password'] = $_POST['password'];

            $ReturnValue = $Actor->logIn($System['username'], $System['password']);

            if ($ReturnValue['success'] && isset($_SESSION['user'])) {
                $this->Status = 1;
                $this->redirect('default:user', ['?' => $System['username']]);
            } else {
                $this->Status = 2;
                $this->Errors = $ReturnValue['errors'];
            }
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract($GlobalVariables);

		?>
		<style type="text/css" >
			.headerlink.loginlink, #userLinks .loggedout .separator {
				display: none;
			}
		</style>
		<?php
		if ($this->Status === 3) {
			msg('already_loggedin', $Actor->getName());
			echo '<br /><br /><br />';
		}
		?>
		<?php
		if ($this->Status === 0 || $this->Status === 3) {
			?>
			<form method="post" <?php if (!empty($_GET[$Wiki['config']['urlparam']['redirect']])) {
			    echo 'action="' . fl('login', [$Wiki['config']['urlparam']['redirect'] => $_GET[$Wiki['config']['urlparam']['redirect']]]) . '" ';
			} ?>>
				<input type="hidden" name="send" /><!-- -->
				<input type="text" name="username" class="big-input input-login fi" maxlength="30" placeholder="<?php msg('placeholder-username') ?>" autocomplete="off" /><br />
				<input type="password" name="password" class="big-input input-login top10 fi" maxlength="40" placeholder="<?php msg('placeholder-password') ?>" autocomplete="off" /><br />
				<input type="submit" class="big-submit submit-login top10" value="<?php msg('btn-login') ?>" />
			</form>
			<br /><br />
			<?php
			if ($this->Status === 3) {
				msg('login-register-another-link');
			} elseIf ($this->Status == 0) {
				msg('login-register-link');
			}
		} elseIf ($this->Status === 1) {
			msg('login-process');
		} elseIf ($this->Status === 2) {
			msg('login-madeamistake', $this->al_current_url('try-again'));
		}
	}
}