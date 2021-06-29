<?php

class log extends PageElementBase {
	private $AllEntries	= [];
	private $Extension	= [];

	public function __construct( $Data ) {
		global $GlobalImport;
		extract( $GlobalImport );

		if (is_string( $Data ))
			$this->Extension['page'] = $Data;
		elseIf (is_array( $Data ))
			$this->Extension['page'] = $Data;

		/* DEFAULT SETTINGS */
		$this->Extension['ChainSub']			= true;
		$this->Extension['ChainSubExpandable']	= true;
		$this->Extension['ShowVersionLink']		= false;
		$this->Extension['ShowVersionEditLink']	= false;

		if (empty( $Data['limit'] ) || !is_numeric( $Data['limit'] ))
			$Data['limit'] = 100; # Default value

		if (array_key_exists( 'sub-chains-expandable', $Data ) && $Data['sub-chains-expandable'] == false)
			$this->Extension['ChainSubExpandable'] = false;

		if (array_key_exists( 'sub-chains', $Data ) && $Data['sub-chains'] == false) {
			$this->Extension['ChainSub']			= false;
			$this->Extension['ChainSubExpandable']	= false;
		}

		if (!empty( $Data['show-version-link'] ) && $Data['show-version-link'])
			$this->Extension['ShowVersionLink'] 	= true;

		if (!empty( $Data['show-version-edit-link'] ) && $Data['show-version-edit-link'])
			$this->Extension['ShowVersionEditLink'] = true;

		/* GET PARAMETER VALUES */
		$AE_Q_Conditions	= [];
		$AE_Q_Parameters	= [];
		$AE_Q_DESC			= (!empty( $Data['sort'] ) && $Data['sort'] == 'reverse') ? null : 'DESC';
		$AE_Q_LIMIT			= preg_replace( '/[^0-9]/', '', $Data['limit'] );

		/* Pages parameter */
		if (!empty( $Data['pages'] ) && (is_array( $Data['pages'] ) || is_string( $Data['pages'] ))) {
			if (is_string( $Data['pages'] ))
				$Data['pages'] = [$Data['pages']];

			if (count( $Data['pages'] ) === 1) {
				$this->Extension['ChainSub']			= false;
				$this->Extension['ChainSubExpandable']	= false;

				/* Count versions */
				if (array_key_exists( 'count-versions', $Data ) && $Data['count-versions']) {
					$Count = $dbc->prepare( 'SELECT * FROM log WHERE page = :page' );
					$Count->execute([
						':page' => $Data['pages'][0]
					]);
					$this->Extension['count-versions'] = $Count->rowCount();
				}
			}

			$AE_Q_Pages_In	= '';
			foreach ($Data['pages'] as $i => $Page) {
				$key					 = ':page' . $i;
				$AE_Q_Pages_In			.= $key . ',';
				$AE_Q_Pages_Params[$key] = $Page;
			}
			$AE_Q_Pages_In = rtrim( $AE_Q_Pages_In, ',' );

			$AE_Q_Conditions[]			= 'page IN (' . $AE_Q_Pages_In . ')';
			$AE_Q_Parameters = array_merge( $AE_Q_Parameters, $AE_Q_Pages_Params );
		}

		/* Users parameter */
		if (!empty( $Data['users'] ) && (is_array( $Data['users'] ) || is_string( $Data['users'] ))) {
			if (is_string( $Data['users'] ))
				$Data['users'] = [$Data['users']];

			$AE_Q_Users_In	= '';
			foreach ($Data['users'] as $i => $User) {
				$key					 = ':user' . $i;
				$AE_Q_Users_In			.= $key . ',';
				$AE_Q_Users_Params[$key] = $User;
			}
			$AE_Q_Users_In = rtrim( $AE_Q_Users_In, ',' );

			$AE_Q_Conditions[]			= 'username IN (' . $AE_Q_Users_In . ')';
			$AE_Q_Parameters = array_merge( $AE_Q_Parameters, $AE_Q_Users_Params );
		}

		/* Types parameter */
		if (!empty( $Data['types'] ) && (is_array( $Data['types'] ) || is_string( $Data['types'] ))) {
			if (is_string( $Data['types'] ))
				$Data['types'] = [$Data['types']];

			$AE_Q_Types_In	= '';
			foreach ($Data['types'] as $i => $Type) {
				$key = ':type' . $i;
				$AE_Q_Types_In		.= $key . ',';
				$AE_Q_Types_Params[$key] = $Type;
			}
			$AE_Q_Types_In = rtrim( $AE_Q_Types_In, ',' );

			$AE_Q_Conditions[]			= 'type IN (' . $AE_Q_Types_In . ')';
			$AE_Q_Parameters = array_merge( $AE_Q_Parameters, $AE_Q_Types_Params );
		}

		/* MAKE QUERY */
		$AE_Q = 'SELECT * FROM log';
		if ($AE_Q_Conditions)
			$AE_Q .= ' WHERE ' . implode( ' AND ', $AE_Q_Conditions );
		$AE_Q .= ' ORDER BY timestamp';
		if ($AE_Q_DESC)
			$AE_Q .= ' DESC';
		if ($AE_Q_LIMIT)
			$AE_Q .= ' LIMIT ' . $AE_Q_LIMIT;

		/* FIND LOG ENTRIES */
		$this->AllEntries = $dbc->prepare( $AE_Q );
		$this->AllEntries->execute( $AE_Q_Parameters );
		$this->AllEntries = $this->AllEntries->fetchAll();
	}

	public function insert() {
		global $GlobalImport;
		extract( $GlobalImport );
		
		$PageActions = [
			'edit', 'editpage',
			'create', 'createpage',
			'protect',
			'unprotect',
			'hide',
			'unhide',
			'allowcomments',
			'rename'
		];

		if (!p( 'log-view' ))
			msg( 'error-permission' );
		else {
			if (!empty( $this->Extension['count-versions'] )) {
				?>
				<div class="count-versions" >
					<?php msg( 'log-count-versions', 0, $this->Extension['count-versions'] ); ?>
				</div>
				<?php
			}
	?>
	<ul class="fw" >
	<?php
			$SecondItem		= false;
			$IndexByPage	= [];
			if (empty( $this->AllEntries )) {
				msg( 'log-empty' );
				return false;
			}

			foreach ($this->AllEntries as $i => $Entry)
				$IndexByPage[$Entry['page']][] = $i;

			$Min = '';

			$FirstIndicateHiddenSub = false;
			foreach ($this->AllEntries as $i => $Entry) {
				$Data = (!empty($Entry['data'])) ? json_decode($Entry['data'], true) : [];
				if (!array_key_exists('flags', $Data)) $Data['flags'] = [];

				if ($Entry['type'] == 'rights') {
					$Page = $dbc->prepare( 'SELECT * FROM user WHERE rid = :rid LIMIT 1' );
					$Page->execute([
						':rid' => $Entry['page']
					]);
					$Page = $Page->fetch();
					if (!empty( $Page ))
						$Page['url'] = $Page['username'];
				} else {
					$Page = $dbc->prepare( 'SELECT * FROM pages WHERE rid = :rid LIMIT 1' );
					$Page->execute([
						':rid' => $Entry['page']
					]);
					$Page = $Page->fetch();
				}

				$IndexGroup	= $i - 1;
				$CountRest	= 0;

				if (!empty( $Page['url'] )) {

					$First = false;
						if (!empty($Entry['page'])) {
						foreach( $IndexByPage[$Entry['page']] as $Indexes ) {
							if ($Indexes >= $i) {
								if ($Min != $Entry['page']) {
									$First	= true;
									$Min	= $Entry['page'];
								}
								if ($Indexes == $IndexGroup + 1) {
									$IndexGroup++;
									$CountRest++;
								} else {
									$Max = $Indexes;
									break;
								}
								$IndexGroup = $Indexes;
							}
						}
						$CountRest--;
					}

					if (!$this->Extension['ChainSub'] || empty($Entry['page']))
						$First = true;

					$this->Namespace = [
						'Blog'		=> false,
						'Help'		=> false,
						'System'	=> false,
						'User'		=> false
					];

					// GET THE NAMESPACE OF THE PAGE IN LOG
					$Entry['page-noprefix'] = $Page['url'];
					foreach ($Wiki['namespace'] as $Namespace => $Features) {
						$Prefixes = array();
						if (is_string( $Features['prefix'] ))
							array_push( $Prefixes, $Features['prefix'] );
						else
							$Prefixes = $Features['prefix'];

						foreach ($Prefixes as $Prefix) {
							if (strtolower( substr( $Page['url'], 0, strlen( $Prefix ) + 1 ) ) == strtolower( $Prefix ) . ':') {
								foreach (array_keys( $this->Namespace ) as $DefinedNamespace) {
									if ($Namespace == strtolower( $DefinedNamespace )) {
										$Entry['page-noprefix'] = substr( $Page['url'], strlen( $Prefix ) + 1, strlen( $Page['url'] ) - strlen( $Prefix ) - 1 );
										$this->Namespace[$DefinedNamespace] = true;
									}
								}
							}
						}
					}

					if (empty( $Page['disptitle'] ))
						$Page['disptitle'] = $Entry['page-noprefix'];

					if ($this->Namespace['User']) {
						if ($Entry['page-noprefix'] === $Entry['username'])
							$PageNamespace = msg( 'log-pagetype-user-own', 1 );
						else {
							$genitive = (substr($Entry['page-noprefix'], -1) == 's') ? 1 : 0;
							$PageNamespace = msg( 'log-pagetype-user', 1, [0 => $genitive, 1 => al( $Entry['page-noprefix'], 'user', ['?' => $Entry['page-noprefix']] )] );
						}
					} elseIf ($this->Namespace['Blog'])
						$PageNamespace = msg( 'log-pagetype-blog', 1, al( $Page['disptitle'], 'page', ['?' => $Page['url']] ) );
					else
						$PageNamespace = msg( 'log-pagetype-page', 1, al( $Page['disptitle'], 'page', ['?' => $Page['url']] ) );

					$LogDiff = [
						'len'	=> 0,
						'pnn'	=> '0',
						'bold'	=> '',
						'show'	=> false
					];

					if (in_array($Entry['type'], ['editpage', 'createpage']))
						$LogDiff['show'] = true;

					if ($LogDiff['show']) {
						// $Entry['old'] = $dbc->prepare('SELECT LENGTH(new) AS verlength FROM log WHERE page = :page AND id < :id ORDER BY id DESC LIMIT 1');
						$Entry['old'] = $dbc->prepare('SELECT new FROM log WHERE page = :page AND id < :id AND (type = :type OR type = :type_alt) ORDER BY id DESC LIMIT 1');
						$Entry['old']->execute([
							':page'		=> $Entry['page'],
							':id'		=> $Entry['id'],
							':type'		=> $Entry['type'],
							':type_alt'	=> ($Entry['type'] == 'editpage') ? 'create' : ''
						]);
						$Entry['old'] = $Entry['old']->fetch();
						$Entry['old'] = ($Entry['old']) ? $Entry['old']['new'] : null;
						// $Entry['old'] = (empty($Entry['old'])) ? 0 : ['verlength'];
						$LogDiff['len'] = strlen($Entry['new']) - strlen($Entry['old']);
						if ($LogDiff['len'] === 0)
							$LogDiff['pnn'] = '0';
						elseIf ($LogDiff['len'] > 0)
							$LogDiff['pnn'] = '+';
						elseIf ($LogDiff['len'] < 0)
							$LogDiff['pnn'] = '-';
						else
							$LogDiff['pnn'] = false;


							if ($LogDiff['len'] === 0)
							$LogDiff['ppn'] = 'gray';
						elseIf ($LogDiff['len'] > 0)
							$LogDiff['ppn'] = 'green';
						elseIf ($LogDiff['len'] < 0)
							$LogDiff['ppn'] = 'darkred';
						else
							$LogDiff['ppn'] = false;
						#$LogDiff['pnn'] = ($LogDiff['len'] > 0) ? '+' : ($LogDiff['len' === 0]) ? '0' : '-'; // positive, null, negative

						if (abs($LogDiff['len']) > 500)
							$LogDiff['bold'] = '; font-weight: bold';
						else
							$LogDiff['bold'] = '';
					}

					$LogMsg								= '';
					$LogOptions['ShowVersionLink']		= false;
					$LogOptions['ShowVersionEditLink']	= false;

					switch ($Entry['type']) {
						default:
							$LogMsg = $Entry['type'];
							break;
						case 'edit':
						case 'editpage':
							$LogOptions['ShowVersionLink']		= true;
							$LogOptions['ShowVersionEditLink']	= true;

							$LogMsg = msg( 'log-editpage', 1, [$Entry['username'], $PageNamespace] );
							$LogDiff['show'] = true;
							break;
						case 'create':
						case 'createpage':
							$LogOptions['ShowVersionLink']		= true;
							$LogOptions['ShowVersionEditLink']	= true;

							$LogMsg = msg( 'log-createpage', 1, [$Entry['username'], $PageNamespace] );
							$LogDiff['show'] = true;
							break;
						case 'protect':
						case 'unprotect':
							/*$Entry['new'] = explode(',', $Entry['new']);
							foreach ($Entry['new'] as $i => $val) $Entry['new'][$i] = msg('group-' . $val, 1);
							$Entry['new'] = implode(', ', $Entry['new']);*/
							$Entry['new'] = protectlayer($Entry['new']);
							$Entry['new'] = listglue($Entry['new'], ['groups']);
							$LogMsg = (empty( $Entry['new'] )) ? msg( 'log-unprotect', 1, [$Entry['username'], $PageNamespace] ) : msg( 'log-protect', 1, [$Entry['username'], $PageNamespace, $Entry['new']] );
							break;
						case 'rename':
							$LogMsg = msg( 'log-rename', 1, [$Entry['username'], $Entry['old'], $Entry['new']] );
							break;
						case 'hide':
						case 'unhide':
							$Entry['new'] = protectlayer($Entry['new']);
							$Entry['new'] = listglue($Entry['new'], ['groups']);
							$LogMsg = (empty( $Entry['new'] )) ? msg( 'log-unhide', 1, [$Entry['username'], $PageNamespace] ) : msg( 'log-hide', 1, [$Entry['username'], $PageNamespace, $Entry['new']] );
							break;
						case 'usersignup':
							$LogMsg = msg('log-usersignup', 1, $Entry['username']);
							break;
						case 'rights':
							$diff[1] = json_decode($Entry['old'], true)['groups'];
							$diff[2] = json_decode($Entry['new'], true)['groups'];
							$diff[3] = json_decode($Entry['old'], true)['types'];
							$diff[4] = json_decode($Entry['new'], true)['types'];

							foreach ($diff as $version => $groups) {
								if ($groups) {
									foreach ($groups as $i => $val) {
										if (!array_key_exists($val, $Wiki['groups']))
											$diff[$version][$i] = msg( 'deprecated-group' , 1, $val );
										else
											$diff[$version][$i] = msg( 'group-' . $val, 1 );
									}
								} else
									$diff[$version][0] = "";
							}

							$change[1] = array();
							foreach (array_diff($diff[1], $diff[2]) as $val) {
								if (!empty($val))
									array_push($change[1], ' -' . $val);
							}
							$change[1] = implode(', ', $change[1]);
							$change[2] = array();
							foreach (array_diff($diff[2], $diff[1]) as $val) {
								array_push($change[2], ' +' . $val);
							}
							$change[2] = implode(', ', $change[2]);
							$change[3] = array();
							foreach (array_diff($diff[3], $diff[4]) as $val) {
								array_push($change[3], ' -' . $val);
							}
							$change[3] = implode(', ', $change[3]);
							$change[4] = implode(', ', array_diff($diff[4], $diff[3]));

							foreach ($change as $i => $val) {
								if (empty( $val ))
									$change[$i] = 0;
							}

							$LogMsg = msg( 'log-rights', 1, [$Entry['username'], al( $Page['url'], 'user', ['?' => $Page['url']] ), $change[1], $change[2], $change[3], $change[4]] );
							$Min = false;
							$CountRest = 0;
							break;
					}

					$Types	= array_filter( explode( ',', ur( '*', $Entry['username'], true ) ) );
					$Hide	= false;

					foreach ($Types as $Type) {
						if (array_key_exists( $Type, $Wiki['types'] )) {
							if (array_key_exists( 'show-in-log', $Wiki['types'][$Type] ) && $Wiki['types'][$Type]['show-in-log'] === false)
								$Hide = true;
						}
					}

					// Page hidden
					if (in_array($Entry['type'], $PageActions) && !empty($Entry['page'])) {
						$Properties = $dbc->prepare("SELECT properties FROM pages WHERE rid = :rid LIMIT 1");
						$Properties->execute([
							':rid' => $Entry['page']
						]);
						$Properties = $Properties->fetch()['properties'];
						$Properties = json_decode($Properties, true);
						if (!is_array($Properties)) $Properties = [];

						if (array_key_exists('hide_options', $Properties)) {
							if (in_array('hide_in_log', $Properties['hide_options'])) {
								$Data['flags'][] = 'hidden';
								$Data['flags'][] = 'hideoption_hide-in-log';
							}
						}
					}

					#if (!p( 'log-view' ))
					#	$Hide = true;
					$Entry['hidden'] = false;
					/*if (($Entry['notice'] === 'hide' || substr( $Entry['notice'], -5 ) === '|hide') && !p( 'log-view-hidden' ))
						$Hide = true;
					elseIf (($Entry['notice'] === 'hide' || substr( $Entry['notice'], -5 ) === '|hide') && p( 'log-view-hidden' ))
						$Entry['hidden'] = true;*/
					if (in_array('hidden', $Data['flags']) && !p('log-view-hidden')) {
						$Hide = true;
					} elseif (in_array('hidden', $Data['flags']) && p('log-view-hidden')) {
						$Entry['hidden'] = true;
						#$FirstIndicateHiddenSub = true;
					}

					if (empty( $LogMsg ))
						$Hide = true;

					if ($Hide)
						$Min = false;

					if (!$Hide) {
		?>
			<li class="log-entry log-type-<?php
			$classes = $Entry['type'] . ' ';
			if (!$First)
				$classes .= 'chain-sub ';
			if ($this->Extension['ChainSubExpandable'] && !$First)
				$classes .= 'chain-sub-expandable ';
			if ($Entry['hidden'])
			$classes .= 'hidden ';
			if (in_array('hideoption_hide-in-log', $Data['flags']))
			$classes .= 'hidden-by-hideoption ';
			if ($First && $FirstIndicateHiddenSub)
				$classes .= 'contains-hidden-sub ';
			if ($SecondItem)
				$classes .= 'second-item ';
			echo rtrim( $classes );
			?>" >
				<span class="time" ><?php timestamp( $Entry['timestamp'] ); ?></span><span class="time-sep" >:</span>
				<span class="desc" ><?php echo $LogMsg; if (!empty( $LogDiff['show'] )) echo ' <span style="color: ' . $LogDiff['ppn'] . $LogDiff['bold'] . ';" >(' . $LogDiff['len'] . ')</span>'; ?></span>
				<?php
					if ($this->Extension['ShowVersionLink'] && $LogOptions['ShowVersionLink']) {
						?>
						<span class="version-link" ><?php echo al( msg( 'log-version-link', 1 ), 'page', ['?' => $Page['url'], 'ver' => $Entry['rid']] ); ?></span>
						<?php
					}
					if ($this->Extension['ShowVersionEditLink'] && $LogOptions['ShowVersionEditLink']) {
						?>
						<span class="version-edit-link" ><?php echo al( msg( 'log-version-edit-link', 1 ), 'editor', ['?' => $Page['url'], 'ver' => $Entry['rid']] ); ?></span>
						<?php
					}
				?>
				<?php
					if ($this->Extension['ChainSubExpandable'] && $CountRest > 0) {
						?>
						<span class="rest" ><?php msg( 'log-chain-hidden-counter', (string) $CountRest ); ?><div></div></span>
						<?php
					}
					if (!empty( $Entry['notice'] )) {
						?>
						<ul class="note" >
							<li class="note-text" >
								<?php echo $Entry['notice']; ?>
							</li>
						</ul>
						<?php
					}
				?>
			</li>
		<?php
						if ($First) {
							$SecondItem = ($SecondItem) ? false : true;
							$FirstIndicateHiddenSub = false;
						}
					}
				}
			}
	?>
	</ul>
	<?php
		}
	}
}