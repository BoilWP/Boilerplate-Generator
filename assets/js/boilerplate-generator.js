jQuery( window ).load( function() {
	var advanced = 'hide';
	var github_updater = 'hide';
	var transifex = 'hide';

	// Hide GitHub options by default
	if ( github_updater == 'hide' ) {
		jQuery('.github-updater-fields').hide();
	}

	// Hide Transifex options by default
	if ( transifex == 'hide' ) {
		jQuery('.transifex-fields').hide();
	}

	// Plugin Slug
	jQuery( 'input[type="text"][name="wp_plugin_boilerplate_name"]' ).change( function() {
		if ( jQuery('input[type="text"][name="wp_plugin_boilerplate_slug"]').val().length === 0 ) {
			jQuery('input[type="text"][name="wp_plugin_boilerplate_slug"]').attr( 'value', jQuery(this).val().replace(/\s+/g, '-').toLowerCase() );
		}
	});

	// Advanced Options
	jQuery( 'a.advanced-options' ).on( 'click', function(){

		if( advanced == 'hide' ) {
			jQuery('.advanced-control').show();

			// If GitHub Updater is not being used then make sure the fields are still hidden.
			if ( github_updater == 'hide' ) {
				jQuery('.github-fields').hide();
			}
			
			// If Transifex is not being used then make sure the fields are still hidden.
			if ( transifex == 'hide' ) {
				jQuery('.transifex-fields').hide();
			}

			jQuery('input[type="hidden"][name="advanced_options"]').val('yes');
			advanced = 'show';
		}
		else if( advanced == 'show' ) {
			jQuery('.advanced-control').hide();
			jQuery('input[type="hidden"][name="advanced_options"]').val('no');
			advanced = 'hide';
		}

		return false;
	});

	// GitHub Options
	jQuery( 'input[type="radio"][name="wp_plugin_boilerplate_support_github"]' ).change( function(){

		if ( github_updater == 'hide' ) {
			jQuery('.github-updater-fields').show();
			github_updater = 'show';
		}
		else if ( github_updater == 'show' ) {
			jQuery('.github-updater-fields').hide();
			github_updater = 'hide';
		}

	});

	// Transifex Options
	jQuery( 'input[type="radio"][name="wp_plugin_boilerplate_using_transifex"]' ).change( function(){

		if ( transifex == 'hide' ) {
			jQuery('.transifex-fields').show();
			transifex = 'show';
		}
		else if ( transifex == 'show' ) {
			jQuery('.transifex-fields').hide();
			transifex = 'hide';
		}

	});

	// Plugin Name Placeholder Change
	jQuery( 'input[type="text"][name="wp_plugin_boilerplate_name"]' ).change( function() {
		if ( jQuery('input[type="text"][name="wp_plugin_boilerplate_menu_name"]').val().length === 0 ) {
			jQuery('input[type="text"][name="wp_plugin_boilerplate_menu_name"]').attr( 'placeholder', jQuery(this).val() );
		}
		if ( jQuery('input[type="text"][name="wp_plugin_boilerplate_title_name"]').val().length === 0 ) {
			jQuery('input[type="text"][name="wp_plugin_boilerplate_title_name"]').attr( 'placeholder', jQuery(this).val() );
		}
	});

});