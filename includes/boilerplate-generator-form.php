<?php

function boilerplate_generator_shortcode() {
	if ( ! isset( $_REQUEST['wp_plugin_boilerplate_generate'], $_REQUEST['wp_plugin_boilerplate_name'] ) ) {
		return boilerplate_generator_form();
	}

	if ( empty( $_REQUEST['wp_plugin_boilerplate_name'] ) )
		die( 'Please enter a plugin name. Please go back and try again.' );

	// Default values should a field be left empty.
	$your_plugin = array(
		'name'           => 'Plugin Name',
		'slug'           => 'plugin-name',
		'uri'            => 'http://boilwp.com',
		'author'         => 'Sébastien Dumont',
		'author_uri'     => 'http://www.sebastiendumont.com',
		'description'    => __( 'Description of the plugin goes here.', 'boilwp' ),
		'min_wp_version' => '4.0',
		'memory_limit'   => '320',
		'menu_name'      => 'Plugin Name',
		'title_name'     => 'Plugin Name',
		'manage_plugin'  => 'manage_options',
	);

	// Plugin Name
	$your_plugin['name'] = trim( $_REQUEST['wp_plugin_boilerplate_name'] );

	// Plugin Slug
	if ( ! empty( $_REQUEST['wp_plugin_boilerplate_slug'] ) ) {
		$your_plugin['slug'] = sanitize_title_with_dashes( $_REQUEST['wp_plugin_boilerplate_slug'] );
	} else {
		$your_plugin['slug']  = sanitize_title_with_dashes( $your_plugin['name'] );
	}

	// Let's check if the slug can be a valid function name.
	if ( ! preg_match( '/^[a-z_]\w+$/i', str_replace( '-', '_', $your_plugin['slug'] ) ) ) {
		die( 'Plugin slug could not be used to generate valid function names. Please go back and try again.' );
	}

	if ( ! empty( $_REQUEST['wp_plugin_boilerplate_description'] ) ) {
		$your_plugin['description'] = trim( $_REQUEST['wp_plugin_boilerplate_description'] );
	}

	if ( ! empty( $_REQUEST['wp_plugin_boilerplate_plugin_uri'] ) ) {
		$your_plugin['uri'] = trim( $_REQUEST['wp_plugin_boilerplate_plugin_uri'] );
	}

	if ( ! empty( $_REQUEST['wp_plugin_boilerplate_author'] ) ) {
		$your_plugin['author'] = trim( $_REQUEST['wp_plugin_boilerplate_author'] );
	}

	if ( ! empty( $_REQUEST['wp_plugin_boilerplate_author_uri'] ) ) {
		$your_plugin['author_uri'] = trim( $_REQUEST['wp_plugin_boilerplate_author_uri'] );
	}

	// Network
	$your_plugin['network'] = trim( $_REQUEST['network'] );

	// Advanced Options
	$advanced_options = trim( $_REQUEST['advanced_options'] );
	if ( $advanced_options == 'yes' ) {

		$your_plugin['github_plugin_uri'] = trim( $_REQUEST['wp_plugin_boilerplate_github_plugin_uri'] ); // GitHub Repository URI

		$support_github_updater = trim( $_REQUEST['wp_plugin_boilerplate_support_github'] ); // Support GitHub Updater
		$your_plugin['support_github_updater'] = $support_github_updater;

		// Is this plugin supporting the GitHub updater?
		if ( $your_plugin['support_github_updater'] == 'yes' ) {
			$github_branch = trim( $_REQUEST['wp_plugin_boilerplate_github_branch'] ); // GitHub Branch
			if ( $github_branch == 'other' ) {
				$your_plugin['github_branch'] = trim( $_REQUEST['wp_plugin_boilerplate_github_branch_other'] ); // GitHub Branch ( if Other )
			} else { 
				$your_plugin['github_branch'] = $github_branch; // GitHub Branch
			}
		} // END if Supporting GitHub Updater

		$wporg = trim( $_REQUEST['wp_plugin_boilerplate_on_wp_org'] ); // WordPress.org

		// Is this plugin using Transifex?
		if ( $your_plugin['wp_plugin_boilerplate_using_transifex'] == 'yes' ) {
			$your_plugin['transifex_project_name'] = trim( $_REQUEST['wp_plugin_boilerplate_transifex_project_name'] );
			$your_plugin['transifex_resource_slug'] = trim( $_REQUEST['wp_plugin_boilerplate_transifex_resources_slug'] );
		}

		$your_plugin['min_wp_version'] = trim( $_REQUEST['wp_plugin_boilerplate_min_wp_version'] );
		$your_plugin['memory_limit']   = trim( $_REQUEST['wp_plugin_boilerplate_memory_limit'] );

		if ( !empty( $_REQUEST['wp_plugin_boilerplate_market_uri'] ) ) {
			$your_plugin['market_uri'] = trim( $_REQUEST['wp_plugin_boilerplate_market_uri'] );
		}

		if ( !empty( $_REQUEST['wp_plugin_boilerplate_documentation_uri'] ) ) {
			$your_plugin['documentation_uri'] = trim( $_REQUEST['wp_plugin_boilerplate_documentation_uri'] );
		}

		// If menu name was left empty then we will go with the Plugin Name.
		if ( empty( $_REQUEST['wp_plugin_boilerplate_menu_name'] ) ) {
			$your_plugin['menu_name'] = $your_plugin['name'];
		}

		// If title name was left empty then we will go with the Plugin Name.
		if ( empty( $_REQUEST['wp_plugin_boilerplate_title_name'] ) ) {
			$your_plugin['menu_name'] = $your_plugin['name'];
		}

		// If user level was not left empty then we overrider default setting.
		if ( !empty( $_REQUEST['wp_plugin_boilerplate_manage_plugin'] ) ) {
			$your_plugin['manage_plugin'] = trim( $_REQUEST['wp_plugin_boilerplate_manage_plugin'] );
		}

	} // END if advanced options

	$zip = new ZipArchive;
	$zip_filename = sprintf( '/tmp/wp-plugin-boilerplate-%s.zip', md5( print_r( $your_plugin, true ) ) );
	$zip->open( $zip_filename, ZipArchive::CREATE && ZipArchive::OVERWRITE );

	$prototypes_dir = dirname( dirname( __FILE__ ) ) . '/prototypes/';
	$prototypes_map = array(
		'standard' => array(
			'wordpress-plugin' => array(
				'id' => 'wordpress-plugin',
				'upstream' => 'https://github.com/BoilWP/WordPress-Plugin-Boilerplate.git',
				'checkout' => BOILWP_WP_PLUGIN_CHECKOUT,
				'name' => 'wordpress-plugin-boilerplate',
				'fullname' => 'WordPress Plugin Boilerplate',
			),
			/*'woocommerce-extension' => array(
				'id' => 'woocommerce-extension',
				'upstream' => 'https://github.com/BoilWP/WooCommerce-Extension-Boilerplate.git',
				'checkout' => BOILWP_WOO_EXTENSION_CHECKOUT,
				'name' => 'woocommerce-extension-boilerplate',
				'fullname' => 'WooCommerce Extension Boilerplate',
			),*/
			'woocommerce-payment-gateway' => array(
				'id' => 'woocommerce-payment-gateway',
				'upstream' => 'https://github.com/BoilWP/WooCommerce-Payment-Gateway-Boilerplate.git',
				'checkout' => BOILWP_WOO_PAYMENT_GATEWAY_CHECKOUT,
				'name' => 'woocommerce-payment-gateway-boilerplate',
				'fullname' => 'WooCommerce Payment Gateway Boilerplate',
			),
		),
		'small' => array(
			'wordpress-plugin' => array(
				'id' => 'wordpress-plugin-light',
				'upstream' => 'https://github.com/BoilWP/WordPress-Plugin-Boilerplate-Light.git',
				'checkout' => BOILWP_WP_PLUGIN_LIGHT_CHECKOUT,
				'name' => 'wordpress-plugin-boilerplate-light',
				'fullname' => 'WordPress Plugin Boilerplate Light',
			),
			'woocommerce-extension' => array(
				'id' => 'woocommerce-extension',
				'upstream' => 'https://github.com/BoilWP/WooCommerce-Extension-Boilerplate-Light.git',
				'checkout' => BOILWP_WOO_EXTENSION_LIGHT_CHECKOUT,
				'name' => 'woocommerce-extension-boilerplate-light',
				'fullname' => 'WooCommerce Extension Boilerplate Light',
			),
		),
	);

	if ( empty( $_REQUEST['plugin-size'] ) ) {
		die( __( 'Invalid plugin size. Please go back and try again.', 'boilwp' ) );
	}

	if ( empty( $_REQUEST['boilerplate'] ) ) {
		die( __( 'Invalid boilerplate type. Please go back and try again.', 'boilwp' ) );
	}

	$plugin_size = trim( $_REQUEST['plugin-size'] );
	$boilerplate = trim( $_REQUEST['boilerplate'] );

	$prototype = $prototypes_map[$plugin_size][$boilerplate];

	// Update or download the boilerplate
	$prototype_dir = $prototypes_dir . $prototype['id'] . '/';

	if ( ! file_exists( $prototype_dir . '.git' ) ) {
		// Let's clone it in
		exec( sprintf( "git clone %s %s", escapeshellarg( $prototype['upstream'] ), escapeshellarg( $prototype_dir ) ), $output, $return );
	}

	$GIT_BIN = sprintf( 'GIT_DIR=%s.git GIT_WORK_TREE=%s git', escapeshellarg( $prototype_dir ), escapeshellarg( $prototype_dir ) );

	// Checkout the needed hash, might need a pull if not exists
	exec( sprintf( '%s reset --hard %s', $GIT_BIN, escapeshellarg( $prototype['checkout'] ) ), $output, $return );

	if ( $return ) {
		exec( sprintf( '%s fetch --all', escapeshellarg( $GIT_BIN ) ) );
	}
	exec( sprintf( '%s reset --hard %s', $GIT_BIN, escapeshellarg( $prototype['checkout'] ) ), $output, $return );

	if ( $return ) {
		die( 'Could not retrieve the necessary tree from ' . esc_html( $prototype['upstream'] ) );
	}

	$prototype_plugindir = $prototype_dir . $prototype['name'];

	$iterator = new RecursiveDirectoryIterator( $prototype_plugindir );

	foreach ( new RecursiveIteratorIterator( $iterator ) as $filename ) {
		$local_filename = str_replace( trailingslashit( $prototype_plugindir ), '', $filename );
		if ( in_array( basename( $local_filename ), array( '.', '..' ) ) )
			continue; // Skip updir traversals

		// File content replacements
		$contents = file_get_contents( $filename );
		$contents = do_replacements( $contents, $local_filename, $your_plugin, $prototype );

		// Filename replacements, assuming that the prototype plugin is called the same as its directory
		$local_filename = str_replace( $prototype['name'], $your_plugin['slug'], $local_filename );
		$local_filename = str_replace( 'plugin-name', $your_plugin['slug'], $local_filename );

		$zip->addFromString( trailingslashit( $your_plugin['slug'] ) . $local_filename, $contents );
	}

	$zip->close();

	header( 'Content-type: application/zip' );
	header( sprintf( 'Content-Disposition: attachment; filename="%s.zip"', $your_plugin['slug'] ) );
	readfile( $zip_filename );
	unlink( $zip_filename );
	die();
}

add_shortcode( 'boilerplate-generator', 'boilerplate_generator_shortcode' );
if ( isset( $_REQUEST['wp_plugin_boilerplate_generate'], $_REQUEST['wp_plugin_boilerplate_name'] ) ) {
	/**
	* We suppress all output, since we're going to be setting headers after generation
	* and trigger the generator manually. This is a draft alpha solution and has to be redesigned.
	*/
	boilerplate_generator_shortcode();
}

function boilerplate_generator_form() {
	?>
	<form class="boilewp-generator" role="form" method="post">
		<input type="hidden" name="wp_plugin_boilerplate_generate" value="1" />
		<input type="hidden" name="advanced_options" value="no" />

		<div class="form-group">
			<label for="which-boilerplate"><?php _e( 'What type of WordPress plugin do you wish to develop?', 'boilwp' ); ?></label>
			<div class="radio">
				<label><input type="radio" name="boilerplate" value="wordpress-plugin" checked="checked"> <?php _e( 'Custom WordPress Plugin', 'boilwp' ); ?></label>
			</div>
			<div class="radio">
				<label><input type="radio" name="boilerplate" value="woocommerce-extension"<?php if ( !isset( $_GET['disabled'] ) ) { echo ' disabled="disabled"'; } ?>> <?php _e( 'WooCommerce Extension', 'boilwp' ); ?></label>
			</div>
			<div class="radio">
				<label><input type="radio" name="boilerplate" value="woocommerce-payment-gateway"<?php if ( !isset( $_GET['disabled'] ) ) { echo ' disabled="disabled"'; } ?>> <?php _e( 'WooCommerce Payment Gateway', 'boilwp' ); ?></label>
			</div>
		</div>

		<div class="form-group plugin-size">
			<label for="plugin-size"><?php _e( 'What is the size of your plugin development?', 'boilwp' ); ?></label>
			<div class="radio">
				<label><input type="radio" name="plugin-size" value="standard" checked="checked"> <?php _e( 'Standard (e.g. WooCommerce, Easy Digital Downloads)', 'boilwp' ); ?></label>
			</div>
			<div class="radio">
				<label><input type="radio" name="plugin-size" value="small"> <?php _e( 'Small (e.g. Widgets, Shortcodes, Custom Post Type)', 'boilwp' ); ?></label>
			</div>
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-name"><?php _e( 'Plugin Name', 'boilwp' ); ?> *</label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_name" placeholder="<?php _e( 'Enter the name of the plugin', 'boilwp' ); ?>">
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-description"><?php _e( 'Plugin Description', 'boilwp' ); ?> *</label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_description" placeholder="<?php _e( 'Enter a description of the plugin', 'boilwp' ); ?>">
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-text-domain"><?php _e( 'Plugin URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_plugin_uri" placeholder="<?php _e( 'Enter the URI of the plugin', 'boilwp' ); ?>">
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-slug"><?php _e( 'Plugin Slug', 'boilwp' ); ?></label>
			<input type="text" class="form-control" id="wp-plugin-boilerplate-slug" name="wp_plugin_boilerplate_slug" placeholder="<?php _e( 'Enter the plugin slug', 'boilwp' ); ?>" />
			<span><?php _e( 'Example "my-plugin-is-awesome"', 'boilwp' ); ?></span>
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-author"><?php _e( 'Author', 'boilwp' ); ?> *</label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_author" placeholder="<?php _e( 'Enter the name of the author', 'boilwp' ); ?>" />
		</div>

		<div class="form-group">
			<label for="wp-plugin-boilerplate-author-uri"><?php _e( 'Author URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_author_uri" placeholder="<?php _e( 'Enter the URI of the author', 'boilwp' ); ?>">
		</div>

		<div class="form-group">
			<label for="network"><?php _e( 'Network?', 'boilwp' ); ?></label>
			<input type="radio" name="network" value="false" checked="checked"> <?php _e( 'False', 'boilwp' ); ?>
			<input type="radio" name="network" value="true"> <?php _e( 'True', 'boilwp' ); ?>
		</div>

		<div class="form-group">
			<a class="advanced-options btn" href="#"><?php _e( 'View Advanced Options', 'boilwp' ); ?></a>
		</div>

		<div class="form-group advanced-control github-fields">
			<label for="wp-plugin-boilerplate-github-plugin-url"><?php _e( 'GitHub Plugin URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_github_plugin_uri" placeholder="<?php _e( 'Enter the GitHub repository this plugin will be stored at', 'boilwp' ); ?>">
		</div>

		<div class="form-group advanced-control github-fields">
			<label for="network"><?php _e( 'Will this plugin be supporting the GitHub updater?', 'boilwp' ); ?></label>
			<div class="radio">
				<input type="radio" name="wp_plugin_boilerplate_support_github" value="no" checked="checked"> <?php _e( 'No', 'boilwp' ); ?>
				<input type="radio" name="wp_plugin_boilerplate_support_github" value="yes"> <?php _e( 'Yes', 'boilwp' ); ?>
			</div>
			<span><a href="https://github.com/afragen/github-updater" target="_blank"><?php _e( 'How do I support the GitHub updater?', 'boilwp' ); ?></a></span>
		</div>

		<div class="form-group advanced-control github-fields github-updater-fields">
			<label for="wp-plugin-boilerplate-github_branch"><?php _e( 'GitHub Branch', 'boilwp' ); ?></label>
			<input type="radio" name="wp_plugin_boilerplate_github_branch" value="master" checked="checked"> <?php _e( 'Master', 'boilwp' ); ?>
			<input type="radio" name="wp_plugin_boilerplate_github_branch" value="other"> <?php _e( 'Other', 'boilwp' ); ?>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_github_branch_other" style="width:200px;">
			<span><?php _e( 'If other, specify the branch that the GitHub updater plugin will be checking.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-on-wp-org"><?php _e( 'Will this plugin be published on WordPress.org?', 'boilwp' ); ?></label>
			<input type="radio" name="wp_plugin_boilerplate_on_wp_org" value="yes" checked="checked"> <?php _e( 'Yes', 'boilwp' ); ?>
			<input type="radio" name="wp_plugin_boilerplate_on_wp_org" value="no"> <?php _e( 'No', 'boilwp' ); ?>
		</div>

		<div class="form-group advanced-control wp-boilerplate-only">
			<label for="wp-plugin-boilerplate-using-transifex"><?php _e( 'Will you be using Transifex?', 'boilwp' ); ?></label>
			<input type="radio" name="wp_plugin_boilerplate_using_transifex" value="yes"> <?php _e( 'Yes', 'boilwp' ); ?>
			<input type="radio" name="wp_plugin_boilerplate_using_transifex" value="no" checked="checked"> <?php _e( 'No', 'boilwp' ); ?>
		</div>

		<div class="form-group advanced-control transifex-fields wp-boilerplate-only">
			<label for="wp-plugin-boilerplate-transifex-project-name"><?php _e( 'Transifex Project Name', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_transifex_project_name">
			<span><?php _e( 'Enter the name of your Transifex project. e.g. wordpress-plugin-boilerplate', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control transifex-fields wp-boilerplate-only">
			<label for="wp-plugin-boilerplate-transifex-resources-slug"><?php _e( 'Transifex Resources Slug', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_transifex_resources_slug">
			<span><?php _e( 'Enter the name of your Transifex resources slug. e.g. wordpress-plugin-boilerplate', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-min-wp-version"><?php _e( 'Minimum WordPress Version Required', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_min_wp_version" placeholder="4.0">
			<span><?php _e( 'Leave empty to use the latest stable version.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control standard-only">
			<label for="wp-plugin-boilerplate-memory-limit"><?php _e( 'Memory Limit', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_memory_limit" placeholder="320">
			<span><?php _e( 'Set the amount of memory the plugin requires for it to function. Default is 320 (320 = 32MB)', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-market-uri"><?php _e( 'Plugin Market URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_market_uri" placeholder="http://">
			<span><?php _e( 'Enter the url to where you are marketing this plugin.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-documentation-uri"><?php _e( 'Documentation URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_documentation_uri" placeholder="http://">
			<span><?php _e( 'Enter the url to where the documentation for this plugin is located.', 'boilwp' ); ?></span>
		</div>

		<!--div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-wp-plugin-uri"><?php _e( 'WordPress Plugin URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_wp_plugin_uri" placeholder="https://wordpress.org/plugins/your-plugin-name">
			<span><?php _e( 'Replace "your-plugin-name" with the name of the plugin slug given for your wordpress repository.', 'boilwp' ); ?></span>
		</div-->

		<!--div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-wp-plugin-support-uri"><?php _e( 'WordPress Support Plugin URI', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_wp_plugin_support_uri" placeholder="https://wordpress.org/support/plugin/plugin-name">
			<span><?php _e( 'Replace "plugin-name" with the name of the plugin slug given for your wordpress repository.', 'boilwp' ); ?></span>
		</div-->

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-menu-name"><?php _e( 'Menu Name', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_menu_name" placeholder="<?php _e( 'Plugin Name', 'boilwp' ); ?>">
			<span><?php _e( 'Enter the name of your plugin menu within the admin. If left empty, by default it will be the name of the plugin above.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-title-name"><?php _e( 'Title Name', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_title_name" placeholder="<?php _e( 'Plugin Name', 'boilwp' ); ?>">
			<span><?php _e( 'Enter the title of your plugin pages within the admin. If left empty, by default it will be the name of the plugin above.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group advanced-control">
			<label for="wp-plugin-boilerplate-manage-plugin"><?php _e( 'What level must the user have to control the plugin?', 'boilwp' ); ?></label>
			<input type="text" class="form-control" name="wp_plugin_boilerplate_manage_plugin" placeholder="manage_options" value="manage_options">
			<span><?php _e( 'Enter the user level that is required for the plugin to be controlled.', 'boilwp' ); ?> <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank"><?php _e( 'See Roles and Capabilities</a> for more info.', 'boilwp' ); ?></span>
		</div>

		<div class="form-group">
			<p class="text-center"><input class="btn btn-primary btn-lg" type="submit" name="wp_plugin_boilerplate_generate_submit" value="<?php _e( 'Generate Plugin', 'boilwp' ); ?>" /></p>
		</div>
	</form>
	<?php
}

?>