<?php
	trigger_error( 'Using stubbed non-WP functions' );

	// This is a stub function while the generator is without WordPress
	// once the generator becomes a WordPress plugin this will not be needed
	// XXX: This function does not work as intended!
	if ( ! function_exists( 'sanitize_title_with_dashes ' ) ) {
		function sanitize_title_with_dashes( $title ) {
			$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
			$title = preg_replace('/\s+/', '-', $title);
			$title = preg_replace('|-+|', '-', $title);
			$title = trim($title, '-');
			return $title;
		};
	} else {
		trigger_error( 'Dead code' );
	}

	// This is a stub function while the generator is without WordPress
	// once the generator becomes a WordPress plugin this will not be needed
	// XXX: This function does not work as intended!
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $content ) {
			return htmlentities( $content );
		}
	} else {
		trigger_error( 'Dead code' );
	}

	// This is a stub function while the generator is without WordPress
	// once the generator becomes a WordPress plugin this will not be needed
	// XXX: This function does not work as intended!
	if ( ! function_exists( 'trailingslashit' ) ) {
		function trailingslashit( $string ) {
			return rtrim( $string, '/\\' ) . '/';
		}
	} else {
		trigger_error( 'Dead code' );
	}

	// This is a stub function while the generator is without WordPress
	// once the generator becomes a WordPress plugin this will not be needed
	// XXX: This function does not work as intended!
	if ( ! function_exists( 'esc_url_raw' ) ) {
		function esc_url_raw( $url ) {
			return $url;
		}
	} else {
		trigger_error( 'Dead code' );
	}
