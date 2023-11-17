<?php

class Page extends PageBase {
	private $Userlist;
	public $Styles = [ '/css/user.css' ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
				return msg( 'pt-userlist', 1 );
			break;
			default:
				return '';
			break;
		}
	}

	public function insert() {
		global $GlobalVariables;
		extract( $GlobalVariables );

		$Userlist['filter'] = '';

		#if (isset( $_GET[$Wiki['config']['urlparam']['user']] ) && !empty( $_GET[$Wiki['config']['urlparam']['user']] )) {
		if (param( 'user' )) {
			$System['filter']['username']	= $_GET[$Wiki['config']['urlparam']['user']];
			$Userlist['filter']				.= " WHERE username LIKE '%" . $System['filter']['username'] . "%'";
		}
		if (param( 'groups' )) {
			$System['filter']['groups'] = str_replace( ' ', '', $_GET[$Wiki['config']['urlparam']['groups']] );

			if(strlen( $Userlist['filter'] ) > 0) {
				$Userlist['filter'] .= " AND";
			} else {
				$Userlist['filter'] .= " WHERE";
			}

			$Userlist['filter'] .= ' rights LIKE \'%' . $System['filter']['groups'] . '%\'';
		}
		$System['query']	= 'SELECT * FROM user' . $Userlist['filter'] . ' ORDER BY id DESC LIMIT 50';
		$Userlist['data']	= $dbc->query( $System['query'] );
		$Userlist['data']	= $Userlist['data']->fetchAll();
		?>
<style type="text/css" >
						.user-hidden a { color: red !important; }
						.user-blocked a { text-decoration: line-through #444444; }
						input[type="submit"] { margin-right: 10px; }
					</style>
					<script type="text/javascript" >
						$( document ).ready( function() {
							$( '.userright-label' ).click( function() {
								$( '#userRights' ).val( $( this ).attr( 'attr-group' ) );
							});
							$( '#nofilterbtn' ).click( function() {
								$( '.filter-input' ).val( '' );
								$( '#filterform' ).submit();
							});
						});
					</script>
					<div class="pageHeader" >
						<div class="pageForm" >
							<form id="filterform" method="get" >
								<input type="text" name="<?php echo $Wiki['config']['urlparam']['user']; ?>" class="filter-input fi" placeholder="<?php msg('placeholder-username', 0) ?>" autocomplete="off" <?php echo (isset($System['filter']['username'])) ? 'value="' . $System['filter']['username'] . '" ' : null; ?>/><br />
								<input type="text" name="<?php echo $Wiki['config']['urlparam']['groups'] ?>" id="userRights" class="filter-input fi top10" placeholder="<?php msg('placeholder-userrights', 0) ?>" autocomplete="off" <?php echo (isset($System['filter']['groups'])) ? 'value="' . $System['filter']['groups'] . '" ' : null; ?>/><br />
								<input type="submit" class="top10" value="<?php msg('userlist-searchuser', 0) ?>" /><?php
								if (!empty( $Userlist['filter'] )) {
									?><input id="nofilterbtn" type="button" class="top10" value="<?php msg('userlist-clearfilter', 0) ?>" /><?php
								}

								if (p( 'view-hidden-user' )) {
									?>
									<style type="text/css" >
										.checkbox { display: inline-block; }
									</style>
									<script type="application/javascript" >
										$(document).ready(function() {	
											$('#show_hidden_label').click(function() { 
												$('.user-hidden').toggle();
											});
										});
									</script>
									<?php
									$this->__insertCheckbox([
										'show_hidden' => [
											'checked' => true,
											'label' => 'Show hidden'
										]
									]);
								}
								?>
							</form>
						</div>
					</div>
					<div class="pageList" >
						<?php
						foreach ($Userlist['data'] as $i => $Val) {
							$Userlist['classes'] = '';

							if (ur( 'hidden', $Val['username'], true ) && !p( 'view-hidden-user', $Val['username'] )) {;
							} else {
								if (ur( 'hidden', $Val['username'], true ) || ur( 'blocked', $Val['username'] )) {
									#$Userlist['classes'] = '';
									if (ur( 'hidden', $Val['username'], 1 )) {
										$Userlist['classes'] .= ' user-hidden';
									}
									if (ur( 'blocked' ) == 1) {
										$Userlist['classes'] .= ' user-blocked';
									}
									#$Userlist['classes'] = rtrim( $Userlist['classes'] );
									#$Userlist['classes'] .= '';
								}

								?>
<div class="list-row<?php echo $Userlist['classes']; ?>" >
							<a href="<?php echo fl( 'user', ['?' => $Val['username']] ); ?>" >
								<?php echo $Val['username']; ?>

							</a><?php
								if (!empty( ur( '*', $Val['username'] ) )) {
									$Userlist['user']['right'] = explode( ',', ur( '*', $Val['username'] ) );
										?>

								<div class="userlist-rights" >
									<?php
										foreach ($Userlist['user']['right'] as $Group) {
											if (in_array( $Group, $Wiki['list-groups'] ) && $Wiki['groups'][$Group]['show-on-userpage']) {
									?>
									<div class="userright-label userright-<?php echo $Group; ?>" attr-group="<?php echo strtolower( $Group ); ?>" style="display: inline-block; cursor: pointer;" >
										<?php msg( 'group-' . $Group ); ?>
									</div>
									<?php
											}
										}
									?>
								</div>
<?php
								}
								?>
						</div>
<?php
							}
						}
						if (count( $Userlist['data'] ) === 0) {
							msg('userlist-nouser', 0);
						}
						?>
					</div><?php
	}
}
?>