<?php

class Page extends PageBase {
	public $Styles	= [];
	public $Scripts	= [];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-spamfight', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	public function __construct() {
		
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (p('qsft')) {
        ?>
        <form method="post" >
            <input type="text" name="username" placeholder="<?php msg('placeholder-username'); ?>" />
            <h2 class="sectiontitle" ><?php echo 'Edits'; ?></h2>
            <?php
            $this->__insertRadio('rollback_edits', [
                'none' => [
                    'label'     => 'Do not revert any edits',
                    'checked'   => false
                ],
                'latest_only' => [
                    'label'     => 'Revert the latest edit only',
                    'checked'   => false
                ],
                'full_rollback' => [
                    'label'     => 'Undo all chain edits by user to version from another user',
                    'checked'   => true
                ]
            ]);
            ?>
            <h2 class="sectiontitle" ><?php echo 'Created pages'; ?></h2>
            <?php
            $this->__insertCheckbox([
                'delete_created_pages' => [
                    'label'     => 'Delete pages created by user',
                    'checked'   => true
                ]
            ]);
            ?>
            <h2 class="sectiontitle" ><?php echo 'Block user'; ?></h2>
            <?php
            $this->__insertCheckbox([
                'block_user' => [
                    'label'     => 'Block user',
                    'checked'   => true
                ]
            ]);
            ?>
            <input type="text" class="top40" name="description" value="Reverting vandalism (auto-edit)" placeholder="Note" /><br />
            <input type="submit" class="top20" value="<?php msg('btn-submit-confirm'); ?>" />
        </form>
        <?php
        } else
            msg('error');
	}
}
?>