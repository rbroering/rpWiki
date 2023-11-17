$( document ).ready( function() {
	//if (user['group']['allrights']) {
		/* In case the table structure is changed, adapt the updated number of the column, starting with 1 from ltr */
		var colName		= 1;
		var colStatus	= 2;
		var colGroups	= 3;
		var colUsers	= 4;

		// Trigger form edits
		$( '#permissions_Table tr td' ).click( function() {
			var field	= $( this );
			var row		= $( this ).parent( 'tr' );
			var column	= field.index();

			if (field.hasClass( 'edit-mode' ) == false) {
				switch (column) {
					case colName - 1: // Click on permission name
						return false; // Do nothing
					break;
					case colGroups - 1: // Click on groups field
						$( this ).addClass( 'edit-mode' );
					break;
					case colUsers - 1: // Click on users field
						
					break;
				}
			} else {
				
			}
		});

		// Switch betweem status ranges in group edit
		$( '.permissions_Form input[type="radio"]' ).change( function() {
			if ($( this ).hasClass( 'control_groups' )) {
				$( this ).parents( 'td' ).find( '.permissions_Group_List' ).removeClass( 'hidden' );
			} else {
				$( this ).parents( 'td' ).find( '.permissions_Group_List' ).addClass( 'hidden' );
			}
		});

		// Submit permission group change
		$( 'input[type="submit"]' ).click( function() {
			var tr			= $( this ).parents( 'tr' );
			var td			= $( this ).parents( 'td' );
			var permission	= tr.find( 'td:nth-child(' + colName + ')' ).text();
			var radio		= td.find( '.permissions_Range_Select input[type="radio"]' ).attr( 'name' );
			var selected	= $( 'input[type="radio"][name="' + radio + '"]:checked' );
			var groups		= '';

			if (selected.hasClass( 'control_groups' )) {
				$( td ).find( '.permissions_Group_List input:checkbox:checked' ).each( function() {
					groups += $( this ).attr( 'class' ).replace( /(?:(?:[A-Za-z_-]+ )*)control_checkbox_([a-z_]+)\b/g, '$1' ) + ',';
				});
			}

			selected = selected.val().replace( /control_(?:[A-Za-z_-]+)_([a-z]+)\b/g, '$1' );

			$.ajax({
				method: 'post',
				url: 'senddata.php',
				data: 'data=control&permission=' + permission + '&range=' + selected + '&groups=' + groups,
				success: function( result ) {
					if (result != 0) {
						tr.find( 'td:nth-child(' + colStatus + ')' ).append( '*' );
						td.find( '.permissions_Text' ).text( result );
						td.removeClass( 'edit-mode' );
					} else {
						tr.find( 'td:nth-child(' + colStatus + ')' ).append( '/' );
						td.removeClass( 'edit-mode' );
					}
				}
			});

			return false;
		});

		$( '.rowStatus' ).click( function() {
			$( this ).parents( 'tr' ).find( 'td.edit-mode' ).removeClass( 'edit-mode' );
		});

		// Add new row / permission
		$( '#control_add_Permission_Button' ).click( function() {
			if ($( '#permissions_Table' ).has( '#control_new_Permission' ).length == false) {
				$( this ).parents( 'tr' ).before(
				'<tr id="control_new_Permission" ><td id="control_new_Name" contenteditable="true" ></td><td id="control_new_Status" ></td><td><i id="control_new_Submit" style="color: #3333CC; cursor: pointer;" >Submit</i></td><td></td>'
				);
				$( '#control_new_Name' ).focus();
				$( '#control_add_Permission_Button' ).css( 'opacity', '.4' ).css( 'cursor', 'default' );
			}
		});

		$( '#permissions_Table' ).on( 'click', '#control_new_Submit', function() {
			permission = $( '#control_new_Name' ).text();

			$.ajax({
				method: 'post',
				url: 'senddata.php',
				data: 'data=control&permission=' + permission + '&new',
				success: function( result ) {
					if (result == 'success') {
						$( '#control_new_Status' ).text( '+' );
						$( '#control_new_Submit' ).html( 'Reload to edit groups, users, etc.' ).css( 'color', 'inherit' ).css( 'cursor', 'default' );
						$( '#control_new_Name' ).removeAttr( 'contenteditable' );
						$( '#control_new_Permission *, #control_new_Permission' ).removeAttr( 'id' );
					} else {
						//alert( result );
					}
				}
			});
		});
	//}
});