<?php

class Page extends PageBase {
	public $Styles	= [ '/css/site.css' ];
	public $Scripts	= [  ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg('pt-ninfo', 1);
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
		<table style="width: 100%;" >
			<thead>
				<tr>
					<td><?php msg('ninfo-label-name'); ?></td>
					<td><?php msg('ninfo-label-linkdefault'); ?></td>
					<td><?php msg('ninfo-label-prefixes'); ?></td>
					<td><?php msg('ninfo-label-pagesettings'); ?></td>
					<td><?php msg('ninfo-label-grouprestrictions'); ?></td>
				</tr>
			</thead>
			<tbody>
				<?php
				$Groups = $Wiki['namespace'];

				foreach ($Groups as $Namespace => $Features) {
					echo '<tr>';
					echo '<td>' . $Namespace . '</td>';
					echo '<td>' . $Features['autoPrefix'] . '</td>';
					if (is_array($Features['prefix']))
						echo '<td><ul><li>' . implode('</li><li>', $Features['prefix']) . '</li></ul></td>';
					elseIf (is_string($Features['prefix']))
						echo '<td>' . $Features['prefix'] . '</td>';
					if (key_exists('page', $Features)) {
						echo '<td><ul>';
						foreach ($Features['page'] as $Setting => $Val) {
							if ($Setting == 'comments' && $Val == false)
								echo '<li>' . msg('ninfo-page-comments-false', 1) . '</li>';
							if ($Setting == 'customtitle' && $Val == false)
								echo '<li>' . msg('ninfo-page-customtitle-false', 1) . '</li>';
						}
						echo '</ul></td>';
					} else echo '<td></td>';
					if (key_exists('groups', $Features)) {
						echo '<td><ul>';
						foreach ($Features['groups'] as $Group)
							echo '<li><span class="userright-' . $Group . '" >' . msg('group-' . $Group, 1) . '</span></li>';
						echo '</ul></td>';
					} else echo '<td></td>';
					echo '</tr>';
				}
				?>
			</tbody>
		</table>
		<?php
	}
}
?>