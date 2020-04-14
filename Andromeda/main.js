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

$( document ).ready(function() {
	// Navigation Panel
	$( '#h_btn_nav' ).click( function() {
		$( '#NavigationPanel' ).toggleClass( 'shown' );
	});
	$( '#h_btn_user, #UserPanel' ).click( function() {
		arClass(2, '#UserPanel', 'shown');
		arClass(2, '.symbol.open', 'rotate');
	});

	var ability_hover = false;
	$( '.navSelector' ).on( 'mouseenter', function() {
		ability_hover = true;
		console.log( 'Hover activated' );
	});
	$( '.navSelector' ).click( function() {
		if (ability_hover == false) {
			//$( this ).find( '.navSelector__list' ).slideToggle(200);
			$( this ).find( '.navSelector__list' ).toggle();
		}
	});

	if ($('#NavigationContent').width() > $('#search').width()) {
		$('#NavigationContent').css('margin-right', '-' + ($('#NavigationContent').width() - $('#search').width()) + 'px');
	}
});