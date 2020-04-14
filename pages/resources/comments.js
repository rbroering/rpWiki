var commentsToggled = 0;

function commentsToggle() {
	$('#e__Comments_Write, #e__Comments_Timeline').slideToggle('fast');
	if (commentsToggled == 0) {
		$( '.commentsToggleOpen' ).removeClass( 'hide' );
		$( '.commentsToggleClose' ).addClass( 'hide' );
		commentsToggled = 1;
	} else if (commentsToggled == 1) {
		$( '.commentsToggleOpen' ).addClass( 'hide' );
		$( '.commentsToggleClose' ).removeClass( 'hide' );
		commentsToggled = 0;
	}
}

$( document ).ready( function() {
	var e__Comment_Page = $( '#e_Comments_Page' ).val();

// Writing new comments
	$( '#e__Comments_Submit' ).click( function() {
		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Comment&action=save&page=' + e__Comment_Page + '&pagetype=page&c_Title=' + $( '#e__Comments_Title' ).val() + '&c_Content=' + $( '#e__Comments_Content' ).val(),
			success: function( comment ) {
				$( '#e__Comments_Timeline' ).prepend( comment );
				$( '#e__Comments_Timeline .commentfield:nth-of-type(1)' ).slideDown( 200 );
			}
		});
		return false;
	});
// Editing comments
	$( '.e__Comments_Edit_Submit' ).click( function() {
		cForm		= $( this ).parent( '.e__Comments_Edit_Form' );
		cID			= cForm.attr( 'data-cID' );
		cTitle		= cForm.find( '.cTitle' ).val();
		cContent	= cForm.find( '.cContent' ).val();

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Comment&action=edit&page=' + e__Comment_Page + '&pagetype=page&id=' + cID + '&c_Title=' + cTitle + '&c_Content=' + cContent,
			success: function( comment ) {
				if (comment == 'success') {
					if (cTitle.length > 0) {
						if ($( '.commentfield[data-id="' + cID + '"] .commenttitle' ).length == 0) {
							$( '#cc' + cID ).find( '.commenttext' ).removeClass( 'notitle' );
							$( '#cc' + cID ).prepend(
								'<div class="commenttitle title bw" ></div>'
							);
						}
						$( '.commentfield[data-id="' + cID + '"] .commenttitle' ).html( cTitle );
					} else {
						if ($( '.commentfield[data-id="' + cID + '"] .commenttitle' ).length > 0) {
							$( '#cc' + cID ).find( '.commenttitle' ).remove();
							$( '#cc' + cID ).find( '.commenttext' ).addClass( 'notitle' );
						}
					}
					$( '.commentfield[data-id="' + cID + '"] .commenttext' ).html( cContent );
					deactivateCommentForm('#ceL' + cID, '#cc' + cID, 'ce' + cID);
				} else {
					// Error
					
				}
			}
		});

		return false;
	});
// Hiding comments
	$( '.e__Comment_Hide_Form .e__Comment_Hide_Button' ).click( function() {
		cForm		= $( this ).parent( '.e__Comment_Hide_Form' );
		cField		= $( this ).closest( '.commentfield' );
		cID			= cField.attr( 'data-id' );

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Comment&action=hide&page=' + e__Comment_Page + '&pagetype=page&id=' + cID,
			success: function( comment ) {
				if (comment == 'success') {
					if (cField.hasClass( 'hidden-comment' )) {
						cForm.find( '.e__Comment_Hide_Label' ).removeClass( 'hide' );
						cForm.find( '.e__Comment_Unhide_Label' ).addClass( 'hide' );
						cField.removeClass( 'hidden-comment' );
					} else {
						cForm.find( '.e__Comment_Hide_Label' ).addClass( 'hide' );
						cForm.find( '.e__Comment_Unhide_Label' ).removeClass( 'hide' );
						cField.addClass( 'hidden-comment' );
					}
				} else {
					// Error
					
				}
			}
		});

		return false;
	});
// Deleting comments
	$( '.e__Comment_Delete_Form .e__Comment_Delete_Button' ).click( function() {
		cForm		= $( this ).parent( '.e__Comment_Delete_Form' );
		cField		= $( this ).closest( '.commentfield' );
		cID			= cField.attr( 'data-id' );

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Comment&action=delete&page=' + e__Comment_Page + '&pagetype=page&id=' + cID,
			success: function( comment ) {
				if (comment == 'success') {
					cField.slideUp( 200 );
				} else {
					// Error
					
				}
			}
		});

		return false;
	});
// Writing new reply
	$( '.e__Replies_Submit' ).click( function() {
		var cContent	= $( this ).parent( 'form' ).find( '.e__Replies_Content' ).val();
		var cReplies	= $( this ).parents( '.replyfield' ).find( '.commentreplies' );
		var cID			= $( this ).parents( '.commentfield' ).attr( 'data-id' );

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Reply&action=save&page=' + e__Comment_Page + '&pagetype=page&cID='+ cID + '&c_Content=' + cContent,
			success: function( comment ) {
				cReplies.append( comment );
			}
		});

		return false;
	});
// Editing replies
	$( '.e__Reply_Edit_Submit' ).click( function() {
		cForm		= $( this ).parent( '.e__Reply_Edit_Form' );
		cID			= $( this ).parents( '.commentfield' ).attr( 'data-id' );
		rID			= $( this ).parents( '.reply' ).attr( 'data-id' );
		cContent	= cForm.find( '.cContent' ).val();

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Reply&action=edit&page=' + e__Comment_Page + '&pagetype=page&cID=' + cID + '&rID=' + rID + '&r_Content=' + cContent,
			success: function( comment ) {
				if (comment == 'success') {
					cForm.parents( '.reply' ).find( '.replycontent' ).html( cContent );
					deactivateCommentForm('#ceL' + rID, '#cc' + rID, 'ce' + rID);
				} else {
					// Error
					
				}
			}
		});

		return false;
	});
// Hiding reply
	$( '.e__Reply_Hide_Form .e__Reply_Hide_Button' ).click( function() {
		cForm		= $( this ).parent( '.e__Reply_Hide_Form' );
		cField		= $( this ).parents( '.commentfield' );
		rField		= $( this ).parents( '.reply' );
		cID			= cField.attr( 'data-id' );
		rID			= $( this ).parents( '.reply' ).attr( 'data-id' );

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Reply&action=hide&page=' + e__Comment_Page + '&pagetype=page&cID=' + cID + '&rID=' + rID,
			success: function( comment ) {
				if (comment == 'success') {
					if (rField.hasClass( 'hidden-comment' )) {
						rField.find( '.e__Comment_Hide_Label' ).removeClass( 'hide' );
						rField.find( '.e__Comment_Unhide_Label' ).addClass( 'hide' );
						rField.removeClass( 'hidden-comment' );
					} else {
						rField.find( '.e__Comment_Hide_Label' ).addClass( 'hide' );
						rField.find( '.e__Comment_Unhide_Label' ).removeClass( 'hide' );
						rField.addClass( 'hidden-comment' );
					}
				} else {
					// Error
				}
			}
		});

		return false;
	});
// Deleting replies
	$( '.e__Reply_Delete_Form .e__Reply_Delete_Button' ).click( function() {
		cForm		= $( this ).parent( '.e__Reply_Delete_Form' );
		cField		= $( this ).parents( '.commentfield' );
		rField		= $( this ).parents( '.reply' );
		cID			= cField.attr( 'data-id' );
		rID			= $( this ).parents( '.reply' ).attr( 'data-id' );

		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'category=e__Reply&action=delete&page=' + e__Comment_Page + '&pagetype=page&cID=' + cID + '&rID=' + rID,
			success: function( comment ) {
				if (comment == 'success') {
					rField.slideUp( 200 );
				} else {
					// Error
					
				}
			}
		});

		return false;
	});
});