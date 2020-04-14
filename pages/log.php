<?php

class Page extends PageBase {
	public $Styles	= [ '/resources/log.css' ];
	public $Scripts	= [ '/resources/log.js' ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-log', 1 );
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
<!--<form id="lang" method="get" action="<?php echo fl( 'log' ); ?>" >
	<select id="select_sort" name="sort" class="big-select font-inherit font-size-unset top20" >
		<option value="oldest" >Oldest changes</option>
		<option value="latest" >Latest changes</option>
	</select><br />

	<input type="checkbox" name="tAll" id="tAll" class="check-hidden" checked="checked" /><label for="tAll" id="tAll_label" class="check-label checked" data-checked="checked" onclick="check('tAll')" ><div></div><span class="label-desc" >All types</span></label><br />
	<input type="checkbox" name="tCP" id="tCP" class="check-hidden" checked="checked" /><label for="tCP" id="tCP_label" class="check-label checked" data-checked="checked" onclick="check('tCP')" ><div></div><span class="label-desc" >Create a page</span></label><br />
	<input type="checkbox" name="tEP" id="tEP" class="check-hidden" checked="checked" /><label for="tEP" id="tEP_label" class="check-label checked" data-checked="checked" onclick="check('tEP')" ><div></div><span class="label-desc" >Edit a page</span></label><br />
	<input type="checkbox" name="tPP" id="tPP" class="check-hidden" checked="checked" /><label for="tPP" id="tPP_label" class="check-label checked" data-checked="checked" onclick="check('tPP')" ><div></div><span class="label-desc" >Protect a page</span></label><br />
	<input type="checkbox" name="tRP" id="tRP" class="check-hidden" checked="checked" /><label for="tRP" id="tRP_label" class="check-label checked" data-checked="checked" onclick="check('tRP')" ><div></div><span class="label-desc" >Rename a page</span></label><br />
	<input type="checkbox" name="tUS" id="tUS" class="check-hidden" checked="checked" /><label for="tUS" id="tUS_label" class="check-label checked" data-checked="checked" onclick="check('tUS')" ><div></div><span class="label-desc" >User sign up</span></label><br /><br />
	
	<input type="submit" class="big-submit input-submit" text="Submit" />
</form>

<!-- LIST -->
<div id="loglist" class="top0" >
<?php
	$Conditions = array();

	if (!empty( $_GET['types'] ))
		$Conditions['types'] = explode(',', $_GET['types'] );

	if (!empty( $_GET['sort'] ) && $_GET['sort'] == 'reverse')
		$Conditions['sort'] = 'reverse';

	$this->extension( 'log', $Conditions );
?>
</div>
<?php
	}
}
?>