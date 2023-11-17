$( document ).ready( function() {
	$( 'ul.fw li:not(.chain-sub-expandable)' ).click( function() {
		$( this ).toggleClass( 'chain-expanded' );
		$( this ).nextUntil( 'li:not(.chain-sub-expandable)' ).each( function(i) {
			if ($( this ).hasClass( 'chain-sub-expandable' )) {
				$( this ).delay( 80 * i ).toggleClass( 'expanded' ).slideToggle( 180 );
			}
		});
	});
});