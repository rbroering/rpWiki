<?php
class Page extends PageBase {
	public $Scripts = [ '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js' ];
	# public $Styles = [ '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' ];

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
				return msg( 'pt-requests', 1 );
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
<script type="text/javascript" >
	$( document ).ready(function() {
		$(document).ready(function() {
			$('#requestsTable').DataTable();
		});

		$( '.updateStatus' ).click(function() {
			var line	= $(this).parent('tr');
			var randID	= $(this).parent('form').find('#requestID').val();
			var status	= $(this).attr('data-action');
			var action	= $(this).attr('data-action');

			switch (status) {
				case 'accept':
				case 'decline':
					action = 'review';
					break;
				case 'remove':
					action = 'remove';
					break;
			}
			
			$.ajax({
				method: "POST",
				url: "senddata.php",
				data: { page_requests: true, action: action, randID: randID, status: status }
			}).done(function( result ) {
				if (result == 'success') {
					$('#request-' + randID).slideUp(200);
				}
			});
			return false;
		});
	});
</script>
<?php if (p('requests')) { ?>
<table id="requestsTable" >
	<thead>
		<tr>
			<td>Nr</td>
			<td>Time</td>
			<td>User</td>
			<td>Type</td>
			<td>Content</td>
			<td>Note</td>
			<td>Status</td>
			<td>Options</td>
		</tr>
	</thead>
	<?php
	$requests = $dbc->prepare("SELECT * FROM requests ORDER BY timestamp DESC LIMIT 100");
	$requests->execute();
	$requests = $requests->fetchAll();

		foreach ($requests as $key => $Request) {
			?>
			<tr id="request-<?php echo $Request['rid']; ?>">
				<td><?php echo $key + 1; ?></td>
				<td><?php echo timestamp($Request['timestamp']); ?></td>
				<td><a href="<?php echo fl('user', ['?' => $Request['username']]); ?>" ><?php echo $Request['username']; ?></a></td>
				<td><?php echo $Request['type']; ?></td>
				<td>
				<?php
					if ($Request['type'] == 'usericon') {
						$Usericon = $Wiki['dir']['usericons'] . $Request['filelocation'] . '/' . $Request['username'] . '.png';
						?><div class="placeholder-usericon" ><img src="<?php echo $Usericon; ?>" height="100px" width="100px" /></div><?php
					} else {
						echo $Request['content'];
					}
				?>
				</td>
				<td><?php echo $Request['note']; ?></td>
				<td><?php echo $Request['status']; ?></td>
				<td>
				<?php
					if ($Request['status'] == 'open') {
				?>
					<form method="post" >
						<input type="hidden" id="requestID" name="request" value="<?php echo $Request['rid']; ?>" /><!-- -->
						<button class="updateStatus accept" data-action="accept" >Accept</button><br />
						<button class="updateStatus decline top10" data-action="decline" >Decline</button>
					</form>
				<?php
					} else {
					?>
						<form method="post" >
							<input type="hidden" id="requestID" name="request" value="<?php echo $Request['rid']; ?>" /><!-- -->
							<button class="updateStatus remove" data-action="remove" >Remove</button>
						</form>
					<?php
					}
				?>
				</td>
			</tr>
			<?php
		}
	?>
</table>
				<?php
		} else
			msg('error-permission');
	}
}