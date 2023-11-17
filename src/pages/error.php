<?php

class Page extends PageBase {

	public function msg( $str ) {
		switch ($str) {
			case 'pagetitle':
			case 'disptitle':
			return msg( 'pt-error', 1 );
			break;
			default:
			return '';
			break;
		}
	}

	public function insert() {
		global $IPDE;

		if (!empty( $IPDE['error'] )) {
			$requested = (!empty( $IPDE['error']['requested'] )) ? $IPDE['error']['requested'] : '';

			switch ($IPDE['error']['case']) {
				case 'not-found':
				msg('errorpage');
				break;
				default:
				msg( 'error' );
				break;
			}
		} else {
			msg( 'error' );
		}
	}

}