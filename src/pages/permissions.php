<?php

class Page extends PageBase {
	public $Styles	= [ '/css/page.css' ];
	public $Scripts	= [  ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-permissions', 1 );
			default:
				return '';
		}
	}

	public function __construct() {
		if (p( 'control-edit' ))
			$this->Scripts = [ '/js/control.js' ];
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		if (p( 'control-view' )) {
			if (!p( 'control-edit' )) {
				echo '<div style="margin: 0 0 20px;" >';
				msg( 'control-info' );
				echo '</div>';
			}
		?>
			<style type="text/css" >
				td:not( .edit-mode ) .permissions_Edit_Group,
				td.edit-mode .permissions_Text,
				.permissions_Group_List.hidden {
					display: none;
				}
				input[type="submit"] {
					margin: 20px 0 10px;
				}

				.permissions_Range_Select {
					display: block;
				}
				div.radio {
					display: inline-block;
				}
				.radio-label {
					padding: 0 !important;
				}
				.radio-label div {
					display: none !important;
				}
				.radio-label .label-desc {
					margin: 5px 5px 5px 0;
					padding: 5px 10px;
					background: rgba(0, 0, 0, .4);
					border-radius: 12px;
					float: left;
					font-weight: bold;
					transition: 100ms;
				}
				.radio-label .label-desc:hover {
					background: rgba(0, 0, 0, .3);
				}
				.radio-label.checked .label-desc {
					background: #5c8f6e;
				}

				#control_add_Permission_Button {
					margin: 10px 20px;
				}
				#control_add_Permission_Button:before {
					width: 1em;
					margin-left: -5px;
					content: "+";
					font-size: 1.5em;
					font-weight: bold;
					float: left;
				}
				#control_add_Permission_Button span {
					display: block;
					float: right;
					/* background: #7db986; */
				}
			</style>
			<table id="permissions_Table" >
				<thead>
					<tr>
						<td><?php msg('control-permission'); ?></td>
						<td><?php msg('control-diff'); ?></td>
						<td><?php msg('control-groups'); ?></td>
						<td><?php msg('control-users'); ?></td>
					</tr>
				</thead>
		<?php
			$Permissions = $dbc->query( "SELECT permission, groups, users FROM permissions ORDER BY permission" );
			$Permissions = $Permissions->fetchAll();

			foreach ($Permissions as $Permission) {
				if (is_null($Permission['groups'])) {
					$Permission['groups'] = '-';
				}
	
				if (is_null($Permission['users'])) {
					$Permission['users'] = '';
				}

				?>
				<tr>
					<td><?php echo $Permission['permission']; ?></td>
					<td class="rowStatus" ></td>
					<td>
						<?php
						if (p( 'control-edit-groups' )) {
						?>
						<div class="permissions_Edit_Group" >
							<form class="permissions_Form" method="post" >
								<div class="permissions_Range_Select" >
								<?php
									$this->__insertRadio( 'control_' . $Permission['permission'] . '_Status', [
										'control_' . $Permission['permission'] . '_' . 'groups'	=> [
											'checked'	=> true,
											'class'		=> 'control_groups',
											'label'		=> msg( 'control-radio-groups', 1 )
										],
										'control_' . $Permission['permission'] . '_' . 'users'	=> [
											'class'		=> 'control_users',
											'label'		=> msg( 'control-radio-users', 1 )
										],
										'control_' . $Permission['permission'] . '_' . 'every'	=> [
											'class'		=> 'control_every',
											'label'		=> msg( 'control-radio-every', 1 )
										],
										'control_' . $Permission['permission'] . '_' . 'nouser'	=> [
											'class'		=> 'control_nouser',
											'label'		=> msg( 'control-radio-nouser', 1 )
										]
									]);
								?>
								</div>
								<div class="permissions_Group_List" >
								<?php
									$checkBoxes = array();
									foreach ($Wiki['groups'] as $Groupname => $Group) {
										$checkBoxes['control_' . $Permission['permission'] . '_' . $Groupname] = [
											'checked'	=> (stristr( $Permission['groups'], $Groupname )) ? true : false,
											'class'		=> 'control_checkbox_' . $Groupname,
											'label'		=> $Group['msg']
										];
									}
									$checkBoxes['control_' . $Permission['permission'] . '_own'] = [
										'checked'	=> false,
										'class'		=> 'control_checkbox_own',
										'label'		=> msg( 'control-checkbox-own', 1 )
									];

									$this->__insertCheckbox( $checkBoxes );
								?>
								</div>
								<input type="submit" />
							</form>
						</div>
						<?php
						}
						?>
						<div class="permissions_Text" ><?php
						$Groups = explode( ',', $Permission['groups'] );
						foreach ($Groups as $i => $val) {
							if (!array_key_exists($val, $Wiki['groups']) && $val != '*' && $val != 'users' && $val != '-')
								unset($Groups[$i]);
						}
						$i = 0;
						foreach( $Groups as $Group ) {
							$i++;
							if ($i < count( $Groups ))
								$separator = ', ';
							else
								$separator = '';
							
							echo '<span class="userright-' . $Group . '" >' . msg( 'group-' . $Group, 1 ) . '</span>' . $separator;
						}
						?></div>
					</td>
					<td><?php
					$Profiles = explode( ',', $Permission['users'] ?? '' );
					$i = 0;
					foreach( $Profiles as $Profile ) {
						$i++;
						if ($i < count( $Profiles ))
							$separator = ', ';
						else
							$separator = '';
						?><a href="<?php echo fl( 'user', ['?' => $Profile] ); ?>" ><?php echo $Profile; ?></a><?php echo $separator;
					}
					?></td>
				</tr>
				<?php
			}
			if (p( 'control-edit' )) {
		?>
				<tr>
					<td colspan="4" style="padding: 0 20px;" >
						<button id="control_add_Permission_Button" ><span>Add a new permission</span></button>
					</td>
				</tr>
		<?php
			}
		?>
</table>
		<?php
		} else {
			msg('control-err-permission');
			msg('control-err-rights');
		}
	}
}
?>