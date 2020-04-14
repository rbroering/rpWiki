<?php
	header('Content-Type: application/javascript');
	require_once( '../../getdata.php' );
?>
$(document).ready(function() {

	$('#langSelect option[value="<?php echo $UserPref['lang']; ?>"]').attr('selected', 'selected').addClass('set');
	$('#skinSelect option[value="<?php echo $UserPref['skin']; ?>"]').attr('selected', 'selected').addClass('set');


	/*
		********** PASSWORD **********
	*/
	// INPUT
	$('#password input').keyup(function() {
		$('#password input').removeClass('required');
		$('#password .ajax-submit').removeAttr('disabled');
	});

	// AJAX
	$('#password').submit(function() {
		if($('#pwold').val() == '' || $('#pw').val() == '' || $('#pw2').val() == '' || $('#pw').val() != $('#pw2').val()) {
			if($('#pw').val() != $('#pw2').val()) {
				$('#password .notif span').html('<span class="err-notif" ><?php msg( 'error-passwordmatch' ); ?></span>');
			} else {
				$('#password .notif span').html('<span class="err-notif" ><?php msg( 'error-filltext-section' ); ?></span>');
			}
			if($('#password .notif').css('display', 'none')) { $('#password .notif').slideToggle('fast'); } else { $('#password .notif').css('display', 'block'); }
			$('#password .ajax-submit').attr('disabled', 'disabled');
			$('#password input').addClass('required');
			// $('#password input[value|=""]').addClass('required');
		} else {
			// $('#password input').attr('disabled', 'disabled');
			$.ajax({
				type: 'POST',
				url: 'senddata.php',
				data: 'pwold=' + $('#pwold').val() + '&pw=' + $('#pw').val() + '&pw2=' + $('#pw2').val(),
				success: function(msg) {
					$('#password .notif span').html(msg);
					if($('#password .notif').css('display', 'none')) { $('#password .notif').slideToggle('fast'); } else { $('#password .notif').css('display', 'block'); }
				}
			});
		}
		return false;
	});


	/*
		********** LANGUAGE **********
	*/
	$('#langSelect').change(function() {
		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'lang=' + $(this).val() + '&langName=' + $('#lang option:selected').html(),
			success: function(msg) {
				$('#lang .notif span').html(msg);
				if($('#lang .notif').css('display', 'none')) { $('#lang .notif').slideToggle('fast'); } else { $('#lang .notif').css('display', 'block'); }
			}
		});
		return false;
	});


	/*
		************ SKIN ************
	*/
	$('#skinSelect').change(function() {
		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'skin=' + $(this).val() + '&skinName=' + $('#skin option:selected').html(),
			success: function(msg) {
				$('#skin .notif span').html(msg);
				if($('#skin .notif').css('display', 'none')) { $('#skin .notif').slideToggle('fast'); } else { $('#skin .notif').css('display', 'block'); }
			}
		});
		return false;
	});


	/*
		************ COLOR THEME ************
	*/
	$('#colorthemeSelect').change(function() {
		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'theme=' + $(this).val(),
			success: function(msg) {
				$('#colortheme .notif span').html(msg);
				if($('#colortheme .notif').css('display', 'none')) { $('#colortheme .notif').slideToggle('fast'); } else { $('#colortheme .notif').css('display', 'block'); }
			}
		});
		return false;
	});


	/*
		************ BGFX ************
	*/
	$('#bgfxSelect').change(function() {
		$.ajax({
			type: 'POST',
			url: 'senddata.php',
			data: 'bgfx=' + $(this).val() + '&bgfxBool=' + $('#bgfx option:selected').html(),
			success: function(msg) {
				$('#bgfx .notif span').html(msg);
				if($('#bgfx .notif').css('display', 'none')) { $('#bgfx .notif').slideToggle('fast'); } else { $('#bgfx .notif').css('display', 'block'); }
			}
		});
		return false;
	});

});