<?php

class Page extends PageBase {
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

	public function insert() {
		global $GlobalVariables;
		extract($GlobalVariables);

		$HTML_Rights = new HTMLTags();

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
				$HTML_Rights->open('form_rights_selection', 'form', [
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

				// Groups
				$HTML_Rights->heading(msg('rights-section-groups', 1), 'sectiontitle', ['top0']);

				$HTML_Rights->open('div_rights_checkbox_list', 'div', ['class' => $HTML_Rights->class(['checkbox-list'])]);

				$GroupsAdd		= [];
				$GroupsRemove	= [];
				foreach ($Actor->listGroups() as $Group) {
					$GroupsAdd		= array_unique(array_merge($GroupsAdd, $Wiki['groups'][$Group]['groups-add']));
					$GroupsRemove	= array_unique(array_merge($GroupsRemove, $Wiki['groups'][$Group]['groups-remove']));
				}

				if (!isset($_GET['block'])) {
					$checkBoxes = array();
					foreach ($Wiki['groups'] as $Groupname => $Group) {
						if (
							(!$this->TargetUser->isInGroup($Groupname) && in_array($Groupname, $GroupsAdd)) ||
							($this->TargetUser->isInGroup($Groupname) && in_array($Groupname, $GroupsRemove))
						)
							$checkBoxes[$Groupname] = [
								'checked'	=> $this->TargetUser->isInGroup($Groupname),
								'label'		=> '<span>' . $Group['msg'] . '</span> <small>(' . $Groupname . ')</small>'
							];
						elseif (in_array($Groupname, $GroupsAdd) || in_array($Groupname, $GroupsRemove))
							$checkBoxes[$Groupname] = [
								'checked'	=> $this->TargetUser->isInGroup($Groupname),
								'disabled'	=> true,
								'label'		=> '<span>' . $Group['msg'] . '</span> <small>(' . $Groupname . ')</small>'
							];
					}
				} else {
					$checkBoxes['blocked'] = [
						'checked'	=> $this->TargetUser->isInGroup('blocked'),
						'label'		=> '<span>' . msg('group-blocked', 1) . '</span> <small>(blocked)</small>'
					];
				}

				$this->__insertCheckbox($checkBoxes);

				$HTML_Rights->close('div_rights_checkbox_list');

				// Types
				$HTML_Rights->heading(msg('rights-section-types', 1), 'sectiontitle', ['top30']);

				$HTML_Rights->close('form_rights_selection');
				/* rightsform */
			break;
		}
	}
}
?>