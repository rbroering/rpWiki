<?php

class Page extends PageBase {
	public $Scripts		= ['/js/rights.js'];

	private $TargetUser = false;
	private $PresetUser	= false;
	private $Status		= "";

	public function msg($str) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg('pt-rights', 1);
		}

		return false;
	}

	public function __construct() {
		global $GlobalVariables;
		extract($GlobalVariables);

		$this->TargetUser = false;

		if (!empty($_GET[$Param['url']['user']])) {
			$this->PresetUser	= $_GET[$Param['url']['user']];
			$this->TargetUser	= $_GET[$Param['url']['user']];
		}

		if (!empty($_POST[$Param['post']['user']])) {
			$this->Status		= "selection";
			$this->TargetUser	= $_POST[$Param['post']['username']];
		}

		if (!empty($this->TargetUser)) {
			$UserData = new User();
			$UserData->setUserByName($this->TargetUser);
			$this->TargetUser	= $UserData;
		}
	}

	private function permission($Groupname, $action, $types = false) {
		global $GlobalImport;
		extract($GlobalImport);

		$GroupsAdd		= [];
		$GroupsRemove	= [];

		foreach ($Actor->listGroups() as $Group) {
			if (!$types) {
				$GroupsAdd		= array_unique(array_merge($GroupsAdd, $Wiki['groups'][$Group]['groups-add']));
				$GroupsRemove	= array_unique(array_merge($GroupsRemove, $Wiki['groups'][$Group]['groups-remove']));
			} else {
				$GroupsAdd		= array_unique(array_merge($GroupsAdd, $Wiki['groups'][$Group]['types-add']));
				$GroupsRemove	= array_unique(array_merge($GroupsRemove, $Wiki['groups'][$Group]['types-remove']));
			}
		}

		if (!$types) {
			if (array_key_exists('group-remove-self', $Wiki['groups'][$Groupname]))
			$self = $Wiki['groups'][$Groupname]['group-remove-self'];
		} else {
			if (array_key_exists('type-remove-self', $Wiki['types'][$Groupname]))
			$self = $Wiki['types'][$Groupname]['type-remove-self'];
		}

		$give	= (in_array($Groupname, $GroupsAdd));
		$take	= (in_array($Groupname, $GroupsRemove));
		$both	= ($give or $take);
		$xor	= ($give xor $take);

		$self	= (isset($self) && !$take) ? ($Actor->isUser($this->TargetUser) && $self) : false;

		$take	= ($take or $self);
		$both	= ($give or $take);

		switch($action) {
			default: return false;
			case 'give':
			case 'add':
				return $give;
			break;
			case 'take':
			case 'remove':
				return $take;
			break;
			case 'both':
				return $both;
			break;
			case 'xor':
				return $xor;
			break;
			case 'self':
				return $self;
			break;
		}
	}

	private function makeCheckboxes($types = false) {
		global $GlobalVariables;
		extract($GlobalVariables);

		$HTML_Rights = new HTMLTags();
		$HTML_Inputs = new UiInputs();
		$HTML_Rights->setPrintMode(true);
		$HTML_Inputs->setPrintMode(true);

		$key = ($types) ? 'types' : 'groups';

		$checkBoxes = array();

		foreach ($Wiki[$key] as $Groupname => $Group) {
			if ($Groupname == 'blocked' && !isset($_GET['block'])) continue;

			$HTML_Rights->open("page_rights_{$key}_list_tr_$Groupname", 'tr');
			$HTML_Rights->open("page_rights_{$key}_list_td_$Groupname", 'td');

			$Note = "";

			// Checkbox and group name
			if ($this->permission($Groupname, 'self', $types))	$Note = msg('rights-nb-remove-self-only', 1);
			if ($this->permission($Groupname, 'xor', $types))	$Note = msg('rights-nb-cannot-be-undone', 1);

			$checkBoxes[$key][$Groupname] = [
				'checked'	=> $this->TargetUser->isInGroup($Groupname),
				'label'		=> "<span>" . $Group['msg'] . "</span>"
			];

			if (
				($this->TargetUser->isInGroup($Groupname) || !$this->permission($Groupname, 'add', $types)) &&
				(!$this->TargetUser->isInGroup($Groupname) || !$this->permission($Groupname, 'remove', $types))
			) {
				$checkBoxes[$key][$Groupname]['disabled'] = true;
				$Note = msg('rights-nb-cannot-change', 1);
			}

			$HTML_Inputs->checkbox($Groupname, $checkBoxes[$key][$Groupname]);
			$HTML_Rights->close("page_rights_{$key}_list_td_$Groupname");

			// Technical group name
			$HTML_Rights->open("page_rights_{$key}_list_td_name_$Groupname", 'td');
			$HTML_Rights->tag('small', [], "($Groupname)");
			$HTML_Rights->close("page_rights_{$key}_list_td_name_$Groupname", 'td');

			// Notes
			$HTML_Rights->open("page_rights_{$key}_list_td_note_$Groupname", 'td');
			if (!empty($Note)) $HTML_Rights->tag('small', [], $Note);
			$HTML_Rights->close("page_rights_{$key}_list_td_note_$Groupname", 'td');

			$HTML_Rights->close("page_rights_{$key}_list_tr_$Groupname");
		}

		$HTML_Inputs->setPrintMode(false);
	}

	public function insert() {
		global $GlobalVariables;
		extract($GlobalVariables);

		$HTML_Rights = new HTMLTags();
		$HTML_Inputs = new UiInputs();
		$HTML_Inputs->setPrintMode(true);

		if (
			($this->TargetUser && !$this->TargetUser->exists())
		)
			return false;

		if (!p('editusergroups')) {
			msg('action-denied-editrights');
			return false;
		}

		$HTML_Rights->setAutoNl(true);
		$HTML_Rights->setAutoIndent(5, true);

		switch ($this->Status) {
			default:
				$HTML_Rights->setPrintMode(false);

				echo $HTML_Rights->tag('form', [
					'method'	=> 'post'
				],
					$HTML_Rights->tag('input', [
						'type'	=> 'hidden',
						'name'	=> 'editrights'
					]).
					$HTML_Rights->tag('input', [
						'type'			=> 'text',
						'name'			=> $Param['post']['username'],
						'class'			=> $HTML_Rights->class(['fi']),
						'placeholder'	=> msg('global-ph-username', 1),
						'autocomplete'	=> 'off',
						'value'			=> (!empty($this->PresetUser)) ? $this->PresetUser : ""
					]).
					$HTML_Rights->br('follows-input').
					$HTML_Rights->tag('input', [
						'type'	=> 'submit',
						'class'	=> $HTML_Rights->class(['top10']),
						'value'	=> msg('rights-btn-edit', 1)
					])
				);

				$HTML_Rights->setPrintMode(true);
			break;
			case "selection":
				$HTML_Rights->setPrintMode(true);

				/* RIGHTSFORM */
				$HTML_Rights->open('page_rights_form_selection', 'form', [
					'id'		=> 'rightsform',
					'method'	=> 'post'
				]);

				$HTML_Rights->tag('input', [
					'type'	=> 'hidden',
					'name'	=> 'submit'
				]);

				$HTML_Rights->tag('input', [
					'type'	=> 'hidden',
					'name'	=> $Param['post']['username'],
					'value'	=> $this->TargetUser->getName()
				]);

				// HEADING Groups
				$HTML_Rights->heading(msg('rights-section-groups', 1), 'sectiontitle', ['top0']);

				/* TABLE GROUPS */
				$HTML_Rights->open('page_rights_list_table', 'table', [
					'id'			=> 'table_groups',
					'class'			=> $HTML_Rights->class(['positioning-table', 'light-borders', 'thin-borders']),
					'cellspacing'	=> '0',
					'cellpadding'	=> '5',
					'border'		=> '0'
				]);

				$this->makeCheckboxes();

				$HTML_Inputs->setPrintMode(false);
				$HTML_Rights->close('page_rights_list_table');
				/* table groups */

				// HEADING Types
				$HTML_Rights->heading(msg('rights-section-types', 1), 'sectiontitle', ['top30']);

				/* TABLE TYPES */
				$HTML_Rights->open('page_rights_types_list_table', 'table', [
					'id'			=> 'table_types',
					'class'			=> $HTML_Rights->class(['positioning-table', 'light-borders', 'thin-borders']),
					'cellspacing'	=> '0',
					'cellpadding'	=> '5',
					'border'		=> '0'
				]);

				$HTML_Inputs->setPrintMode(true);

				$this->makeCheckboxes(true);

				$HTML_Rights->close('page_rights_types_list_table');
				/* table types */

				$HTML_Inputs->setPrintMode(false);

				// Notes
				$HTML_Rights->divClass('invisible-break');
				$HTML_Rights->tag('textarea', [
					'name'			=> 'reason',
					'class'			=> $HTML_Rights->class(['big-textarea', 'Areal', 'top50']),
					'placeholder'	=> msg('global-ph-reason', 1)
				]);
				$HTML_Rights->br('follows-textarea');

				// Submit
				$HTML_Rights->tag('input', [
					'type'	=> 'submit',
					'class'	=> $HTML_Rights->class(['big-submit', 'top10']),
					'value'	=> msg('rights-btn-submit', 1)
				]);

				$HTML_Rights->close('page_rights_form_selection');
				/* rightsform */

				$HTML_Rights->getErrors();
			break;
		}
	}
}
?>