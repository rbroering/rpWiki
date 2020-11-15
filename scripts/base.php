<?php

interface SkinInterface {

}

interface PageInterface {
	public function msg($str);
}

abstract class SkinBase implements SkinInterface {
	public $Extension = [];

	public function setup($data = []) {
		if (!empty($data)) {
			foreach ($data as $Key => $Val) {
				switch ($Key) {
					case 'content':
					$this->Content	= $Val;
					break;
					case 'load':
					$this->Load		= $Val;
					break;
					default:
					$this->Var		= $Val;
					break;
				}
			}
		}
	}

	protected function load() {
		global $Page;

		if (is_object($Page)) {
			if (method_exists($Page, 'insert')) {
				$Page->insert();
			}
		}
	}
}

abstract class PageBase implements PageInterface {
	private $Target = 0;
	protected $Extension = '';
	private $ExtensionFile = '';
	public $ExtensionScripts = [];

	abstract function msg($str);

	final public function info($str) {
		global $Wiki;
		global $System;

		if (is_string($str)) {
			switch ($str) {
				default:
					return false;
					break;
				case 'url':
					return $System['page'];
					break;
				case 'paramurl':
					return basename($_SERVER['REQUEST_URI']);
					break;
			}
		}
	}

	final public function al_current_url($text = '', $settings = []) {
		if (empty($text)) $text = msg('reload', 1);

		return al(msg("link-$text", 1), $this->info('paramurl'), [], $settings);
	}

	protected function redirect($Location, $fl = null) {
		global $Wiki;

		$UseFL = false;

		if (!empty($_GET[$Wiki['config']['urlparam']['redirect']]))
			$this->Target = $_GET[$Wiki['config']['urlparam']['redirect']];

		if (is_string($Location) && !empty($Location)) {
			$UseFL = true;

			if (substr($Location, 0, 8) === 'default:') {
				if (empty($this->Target))
					$this->Target = substr($Location, 8);
				else
					$UseFL = false;
			} else
				$this->Target = $Location;
		}

		if ($UseFL && is_array($fl))
			$this->Target = fl($this->Target, $fl);

		if (!empty($this->Target))
			header("Location: " . $this->Target);
	}

	protected function extension($Name, $Data = null) {
		global $Wiki;
		$ExtensionFile = $Wiki['dir']['extensions'] . $Name . '.php';

		if (file_exists($ExtensionFile)) {
			include_once $ExtensionFile;

			if (class_exists($Name)) {
				$ExtensionObject = new $Name($Data);
				if (!empty($ExtensionObject->Scripts))
					if (is_array($ExtensionObject->Scripts))
						foreach ($ExtensionObject->Scripts as $Val)
							$this->ExtensionScripts[] = $Val;
				if (method_exists($ExtensionObject, 'insert'))
					if (!empty($Data))
						$ExtensionObject->insert();
					else
						$ExtensionObject->insert();
				if (!empty($ExtensionObject->Data))
					$this->Extension = $ExtensionObject->Data;
				else
					unset($this->Extension);
			}
		}
	}

	protected function __insertCheckbox($fetch) {
		$UI_Inputs = new UiInputs();
		$UI_Inputs->setPrintMode(true);
		$UI_Inputs->checkboxList($fetch);
	}

	protected function __insertRadio($group, $fetch) {
		if (is_array($group) && is_string($fetch)) {
			$_fetch	= $fetch;
			$fetch	= $group;
			$group	= $_fetch;
		}

		foreach ($fetch as $id => $checkBox) {
			if (!empty($checkBox['group']))
				$checkBox['name'] = $checkBox['group'];

			if (!isset($checkBox['checked']) || empty($checkBox['checked']) || (!is_bool($checkBox['checked']) && !is_numeric($checkBox['checked'])))
				$checkBox['checked'] = false;
			if (!isset($checkBox['label']) || !is_string($checkBox['label']))
				$checkBox['label'] = $id;
			?>
			<div class="radio" >
				<input type="radio" name="<?php echo (!empty($checkBox['name'])) ? $checkBox['name'] : $group; ?>" id="<?php echo $id; ?>" class="radio-hidden<?php if (!empty($checkBox['class'])) echo ' ' . $checkBox['class']; ?>" value="<?php
				echo (empty($checkBox['value'])) ? $id : $checkBox['value']; ?>" <?php if ($checkBox['checked']) { ?>checked="checked" <?php } ?>/>
				<label for="<?php echo $id; ?>" id="<?php echo $id; ?>_label" class="radio-label <?php if (!$checkBox['checked']) echo 'un'; ?>checked" data-checked="<?php if (!$checkBox['checked']) echo 'un'; ?>checked" >
					<div></div>
					<span class="label-desc" >
						<?php echo $checkBox['label']; ?>
					</span>
				</label>
			</div><?php
		}
	}
}

class PageElementBase {
	
}