<?php

class Page extends PageBase {
	public $Styles	= [ '/css/site.css' ];
	public $Scripts	= [  ];

	public function msg($str) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg('pt-finfo', 1);
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		?>
		<style type="text/css" >
			span.permission_name {
				font-size: 12px;
			}
			span.permission_name:before {
				content: "(";
			}
			span.permission_name:after {
				content: ")";
			}
		</style>
		<h2 class="sectiontitle" ><?php msg('finfo-active-groups'); ?></h2>
		<table style="width: 100%;" >
			<thead>
				<tr>
					<td><?php msg('finfo-group-name'); ?></td>
					<td><?php msg('finfo-group-technical'); ?></td>
					<td><?php msg('finfo-group-assigned-permissions'); ?></td>
				</tr>
			</thead>
			<tbody>
				<?php
				$Groups = array_merge( $Wiki['groups'], [
					'*' => ['show-on-userpage' => false],
					'users' => ['show-on-userpage' => false],
					'-' => ['show-on-userpage' => false],
					'own' => ['show-on-userpage' => false]
				] );
				
				foreach ($Groups as $Group => $Features) {
					echo '<tr>';
					echo '<td><span class="userright-' . $Group . '" >' . msg( 'group-' . $Group, 1 ) . '</span><br />' . al( 'Members', 'userlist', ['rights' => $Group] ) . '</td>';
					echo '<td>' . $Group . '</td>';
					echo '<td><ul>';

					$Permissions = $dbc->prepare( "SELECT permission FROM permissions WHERE groups LIKE :group ORDER BY permission" );
					$Permissions->execute([
						':group' => '%' . $Group . '%'
					]);
					$Permissions = $Permissions->fetchAll();
					foreach( $Permissions as $Permission ) {
						$Permission = $Permission['permission'];
						if (!empty( msg( 'pdesc-' . $Permission, 1 ) ))
							$Permission = msg( 'pdesc-' . $Permission, 1 ) . ' <span class="permission_name" >' . $Permission . '</span>';
						echo '<li>' . $Permission . '</li>';
					}

					if ($Features['show-on-userpage']) {
						echo '<li class="finfo-show-on-userpage" >' . msg('finfo-profile-tag', 1) . '</li>';
					}

					foreach ([
						'groups-add',
						// 'group-add-self',
						'groups-add-self',
						'groups-remove',
						// 'group-remove-self',
						'groups-remove-self',
						'types-add',
						'types-remove',
						'type-remove-self',
					] as $EditableGroups) {
						if (in_array($EditableGroups, $Features) && !empty($Features[$EditableGroups])) {
							echo '<li class="finfo-groups finfo-'.$EditableGroups.'" >' .
								msg("finfo-$EditableGroups",
									// !is_array($Features[$EditableGroups]) ? msg("group-$Group", 1) :
									implode(', ', array_map(fn ($Group) => msg("group-$Group", 1), $Features[$EditableGroups])), 1)
								. '</li>';
						}
					}

					echo '</ul></td>';
				}
				?>
			</tbody>
		</table>
		<?php
	}
}
?>