/* COMMON FUNCTIONS */

function sh(showHide, idClass, name) {
	if (showHide == 1) {
		if (idClass == 1) {
			document.getElementById(name).style.display = 'block';
		} else {
			document.getElementsByClassName(name).style.display = 'block';
		}
	} else {
		if (idClass == 1) {
			document.getElementById(name).style.display = 'none';
		} else {
			document.getElementsByClassName(name).style.display = 'none';
		}
	}
}
function shToggle(id) {
	if (document.getElementById(id).style.display == 'none') {
		document.getElementById(id).style.display = 'block';
	} else {
		document.getElementById(id).style.display = 'none';
	}
}

function arClass(addRemove, idClass, ClassN) {
	if (addRemove == 2) {
		$(idClass).toggleClass(ClassN);
	} else if (addRemove == 1) {
		$(idClass).addClass(ClassN);
	} else if (addRemove == 0) {
		$(idClass).removeClass(ClassN);
	}
}
function toggleClass(obj, tClass, action = 2) {
	if (action == 2) {
		$(obj).toggleClass(tClass);
	} else if (action == 1) {
		$(obj).addClass(tClass);
	} else if (action == 0) {
		$(obj).removeClass(tClass);
	}
}


/* FUNCTIONS */

function toggleNavigationPanel() {
	//$('#NavigationPanel').toggle('slide', 'fast');
	var np = document.getElementById('NavigationPanel');
	if (np.style.display == 'none') {
		sh(1, 1, 'NavigationPanel');
	} else {
		sh(0, 1, 'NavigationPanel')
	}
}
//
var countNavPanelMsgClick = 0;
function toggleNavigationPanelMsg() {
	if (countNavPanelMsgClick == 0) {
		$('#NavigationPanel').addClass('showmsg');
		$('#NavigationPanel .navAlpha .inner').css('display', 'none');
		$('#NavigationPanel .navBeta .inner').css('display', 'block');
		countNavPanelMsgClick = 1;
	} else {
		$('#NavigationPanel').removeClass('showmsg');
		$('#NavigationPanel .navBeta .inner').css('display', 'none');
		$('#NavigationPanel .navAlpha .inner').css('display', 'block');
		countNavPanelMsgClick = 0;
	}
}
//
function headerSearch(addRemove) {
	if (addRemove == 1) {
		arClass(1, '.searchInput', 'activesearch');
		$('#userLinks').fadeOut('fast');
	} else if (addRemove == 0) {
		arClass(0, '.searchInput', 'activesearch');
		$('#userLinks').fadeIn('fast');
	}
}

function scrollListGetWidth() {
	var scrolllistwidth = 'calc(1px';
	$('.scrolllist').find('> *').each(function() {
		scrolllistwidth += ' + ' + $(this).css('width');
		scrolllistwidth += ' + ' + $(this).css('margin-left');
		scrolllistwidth += ' + ' + $(this).css('margin-right');
		scrolllistwidth += ' + ' + $(this).css('padding-left');
		scrolllistwidth += ' + ' + $(this).css('padding-right');
		scrolllistwidth += ' + ' + $(this).css('border-left-width');
		scrolllistwidth += ' + ' + $(this).css('border-right-width');
	});
	scrolllistwidth += ')';
	$('.scrolllist').css('width', scrolllistwidth);
}

$( document ).ready( function() {
	// User page nav
	$( '#userpageNav > ul > li' ).click( function() {
		$( '#userpageContent > div' ).hide();//css( 'display', 'none' );
		$( '#userpageContent > div#userpage_' + $( this ).attr( 'for' ) ).show();//css( 'display', 'none' );
	});

	scrollListGetWidth();
});
$( window ).resize( function() {
	scrollListGetWidth();
});

function closeNotif(element) {
	if(element == 0) {
		$('#globalNotifHeader').slideToggle(400);
	} else if(element == 1) {
		$('#globalNotifMain').fadeOut('slow');
	} else {
		$(element).fadeOut('slow');
	}
}
function closeNotifMain() {
	$('#globalNotifMain').fadeOut('slow');
	$('#localNotifMain').fadeOut('slow');
}

$(document).ready(function() {
	setTimeout(onloadWait, 5000);
	$('#newcomment').fadeIn('slow');
});
function onloadWait() {
	$('#mainmessage').fadeOut('slow');
}

function cTClicked() {
	$('#write_cTitle').addClass('clicked');
}
function cTOnblur() {
	$('#write_cTitle').removeClass('clicked').addClass('alreadyclicked');
	if (document.getElementById('write_cTitle').value == '') {
		$('#write_cTitle').removeClass('alreadyclicked');
	}
}
function cCClicked() {
	$('#write_cContent').addClass('clicked');
}
function cCOnblur() {
	if (b_cCOnblur != 1) {
		$('#write_cContent').removeClass('clicked');
	}
}
function commentTime(showHide, ctimeId) {
	if (showHide == 1) {
		document.getElementById(ctimeId).style.display = 'block';
	}
	if (showHide == 0) {
		document.getElementById(ctimeId).style.display = 'none';
	}
}
function activateCommentForm(showId, hideId, hideCe) {
	document.getElementById(hideId).style.display = 'none';
	document.getElementById(hideCe).style.display = 'none';
	$(showId).fadeIn('fast');
}
function deactivateCommentForm(ShowId, ShowCe, hideId) {
	$(ShowId).fadeIn('fast');
	$(ShowCe).fadeIn('fast');
	document.getElementById(hideId).style.display = 'none';
}
function activateReplyForm(crId, crLink) {
	document.getElementById(crLink).style.display = 'none';
	$(crId).slideToggle('fast');
}
function activateReplyEdit(editlink, dellink) {
	$(editlink).slideToggle('fast');
	$(dellink).slideToggle('fast');
}

/* Checkbox *
function check(id) {
	if ($('#' + id + '_label').hasClass('checked')) {
		$('#' + id + '_label').removeClass('checked').addClass('unchecked');
		$('#' + id + '_label').attr('data-checked', 'unchecked');
	} else {
		$('#' + id + '_label').removeClass('unchecked').addClass('checked');
		$('#' + id + '_label').attr('data-checked', 'checked');
	}
} */

$( document ).ready( function() {
	$( '.check-label, .radio-label' ).hover( function() {
		$( this ).toggleClass( 'hover' );
	});
	$( '.check-label' ).click( function() {
		if ($( this ).hasClass('checked')) {
			$( this ).removeClass('checked').addClass('unchecked');
			$( this ).attr('data-checked', 'unchecked');
		} else {
			$( this ).removeClass('unchecked').addClass('checked');
			$( this ).attr('data-checked', 'checked');
		}
	});

	$( '.radio-label' ).click( function() {
			var radioId		= $( '#' + $( this ).attr( 'for' ) );
			var radioName	= radioId.attr( 'name' );
			$( 'input[name="' + radioName + '"]' ).each( function() {
				var eachId	= $( this ).attr( 'id' );
				$( 'label[for="' + eachId + '"]' ).removeClass( 'checked' ).addClass( 'unchecked' ).attr( 'data-checked', 'unchecked' );
			});
			//$( 'label[name="' + $( this ).attr( 'name' ) + '"]' ).removeClass( 'checked' ).addClass( 'unchecked' ).attr( 'data-checked', 'unchecked' );
			$( this ).removeClass( 'unchecked' ).addClass( 'checked' ).attr( 'data-checked', 'checked' );
	});
});

/* Editor: AllowComments */
var editedPage = 0;
var allowComs = 0;
var disallowComs = 0;
function checkEditor() {
	if ($('.check-label').hasClass('checked')) {
		$('label.check-label').removeClass('checked').addClass('unchecked');
		$('label.check-label').attr('data-checked', 'unchecked');
		if (editedPage == 0 && disallowComs == 0) {
			$('textarea#editComment').html('Disallow comments');
			allowComs = 1;
		} else {
			if ($('textarea#editComment').html('Disallow comments')) {
				$('textarea#editComment').html('');
			}
		}
	} else {
		$('label.check-label').removeClass('unchecked').addClass('checked');
		$('label.check-label').attr('data-checked', 'checked');
		if (editedPage == 0 && allowComs == 0) {
			$('textarea#editComment').html('Allow comments');
			disallowComs = 1;
		} else {
			if ($('textarea#editComment').html('Allow comments')) {
				$('textarea#editComment').html('');
			}
		}
	}
}
function noticeUndo() {
	if ($('textarea#editComment').html('Allow comments') || $('textarea#editComment').html('Disllow comments')) {
		$('textarea#editComment').html('');
	}
	editedPage = 1;
}