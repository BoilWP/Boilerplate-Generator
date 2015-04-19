<?php
/**
 * Runs when looping through files contents, does the replacements fun stuff.
 */
function do_replacements( $contents, $filename, $your_plugin, $prototype ) {

	// Replace only text files, skip png's and other stuff.
	$valid_extensions = array( 'php', 'css', 'scss', 'js', 'txt' );
	$valid_extensions_regex = implode( '|', $valid_extensions );
	if ( ! preg_match( "/\.({$valid_extensions_regex})$/", $filename ) )
		return $contents;

	// Special treatment for the main plugin file, assuming that the plugindir matches the plugin name
	if ( $filename == ( $prototype['name'] . '.php' ) ) {
		$plugin_headers = array(
			'Plugin Name'       => $your_plugin['name'],
			'Plugin URI'        => esc_url_raw( $your_plugin['uri'] ),
			'Description'       => $your_plugin['description'],
			'Version'           => '1.0.0', // Reset the plugin version.
			'Author'            => $your_plugin['author'],
			'Author URI'        => esc_url_raw( $your_plugin['author_uri'] ),
			'Text Domain'       => $your_plugin['slug'],
			'Network'           => $your_plugin['network'],
			'GitHub Plugin URI' => $your_plugin['github_uri'],
		);

		foreach ( $plugin_headers as $key => $value ) {
			$contents = preg_replace( '/(' . preg_quote( $key ) . ':)\s?(.+)/', '\\1 ' . $value, $contents );
		}

		$contents = preg_replace( '/(public $web_url =).*/', "\\1 '" . $your_plugin['uri'] . "';", $contents ); // Plugin slug
	} else if ( $filename == 'license.txt' ) {
		$contents = preg_replace( '/Copyright (\d\d\d\d) by the contributors/', 'Copyright ' . date( 'Y' ) . ' by ' . $your_plugin['author'], $contents );
	}

	// Function names can not contain hyphens.
	$slug = str_replace( '-', '_', $your_plugin['slug'] );

	$contents = str_replace( $prototype['fullname'], $your_plugin['name'], $contents ); // Generic names in licenses, etc.

	switch( $prototype['name'] ) {
		case 'wordpress-plugin-boilerplate' :
		case 'wordpress-plugin-boilerplate-light' :
			$contents = str_replace( 'plugin_name_', $slug . '_', $contents ); // Function names.
			$contents = str_replace( '_plugin_name', '_' . $slug, $contents ); // Function names.

			$contents = preg_replace( '/(.*)\$GLOBALS\[.plugin_name.\](.*)/', '\\1$GLOBALS["' . $slug . '"]\\2', $contents ); // Global array indices.
			$contents = preg_replace( '/(.*)\$plugin_name(.*)/', '\\1$' . $slug . '\\2', $contents ); // Global variable names.

			$contents = str_replace( 'plugin_name', $your_plugin['slug'], $contents ); // Miscellaneous strings and identifiers.
			$contents = str_replace( 'plugin-name', $your_plugin['slug'], $contents ); // Filename identifiers.

			$contents = str_replace( 'PLUGIN_NAME', strtoupper( $slug ), $contents ); // Definition names.

			$contents = str_replace( 'Plugin_Name', implode( '_', array_map( 'ucfirst', explode( '-', $your_plugin['slug'] ) ) ), $contents ); // Classes, etc.
		break;

		case 'woocommerce-payment-gateway-boilerplate' :
			$contents = str_replace( 'woocommerce_payment_gateway_boilerplate_', $slug . '_', $contents ); // Function names.
			$contents = str_replace( '_woocommerce_payment_gateway_boilerplate', '_' . $slug, $contents ); // Function names.

			$contents = preg_replace( '/(.*)\$GLOBALS\[.woocommerce_payment_gateway_boilerplate.\](.*)/', '\\1$GLOBALS["' . $slug . '"]\\2', $contents ); // Global array indices.
			$contents = preg_replace( '/(.*)\$woocommerce_payment_gateway_boilerplate(.*)/', '\\1$' . $slug . '\\2', $contents ); // Global variable names.

			$contents = str_replace( 'woocommerce_payment_gateway_boilerplate', $your_plugin['slug'], $contents ); // Miscellaneous strings and identifiers.
			$contents = str_replace( 'woocommerce-payment-gateway-boilerplate', $your_plugin['slug'], $contents ); // Filename identifiers.

			$contents = str_replace( 'WC_Gateway_Name', implode( '_', array_map( 'ucfirst', explode( '-', $your_plugin['slug'] ) ) ), $contents ); // Classes, etc.
		break;

		case 'woocommerce-extension-boilerplate' :
		case 'woocommerce-extension-boilerplate-light' :
			$contents = str_replace( 'wc_extend_plugin_name_', $slug . '_', $contents ); // Function names.
			$contents = str_replace( '_wc_extend_plugin_name', '_' . $slug, $contents ); // Function names.

			$contents = preg_replace( '/(.*)\$GLOBALS\[.wc_extend_plugin_name.\](.*)/', '\\1$GLOBALS["' . $slug . '"]\\2', $contents ); // Global array indices.
			$contents = preg_replace( '/(.*)\$wc_extend_plugin_name(.*)/', '\\1$' . $slug . '\\2', $contents ); // Global variable names.

			$contents = str_replace( 'wc_extend_plugin_name', $your_plugin['slug'], $contents ); // Miscellaneous strings and identifiers.
			$contents = str_replace( 'wc-extension-plugin-name', $your_plugin['slug'], $contents ); // Filename identifiers.
			$contents = str_replace( 'woocommerce-extension-plugin-name', $your_plugin['slug'], $contents ); // Filename identifiers.

			$contents = str_replace( 'WC_EXTEND_', strtoupper( $slug ), $contents ); // Definition names.
			$contents = str_replace( 'WC_EXTEND_PLUGIN_NAME', strtoupper( $slug ), $contents ); // Definition names.

			$contents = str_replace( 'WC_Extend_Plugin_Name', implode( '_', array_map( 'ucfirst', explode( '-', $your_plugin['slug'] ) ) ), $contents ); // Classes, etc.
		break;
	} // END switch()

	// General String Replacing
	$contents = str_replace( "'" . $prototype['name'] . "'", "'" . $your_plugin['slug'] . "'", $contents ); // Strings
	$contents = str_replace( "\\'" . $prototype['name'] . "\\'", "\\'" . $your_plugin['slug'] . "\\'", $contents ); // Strings
	$contents = str_replace( '"' . $prototype['name'] . '"', '"' . $your_plugin['slug'] . '"', $contents ); // Strings
	$contents = str_replace( '\"' . $prototype['name'] . '\"', '\"' . $your_plugin['slug'] . '\"', $contents ); // Strings

	$contents = preg_replace( '/.*@todo.*/', '', $contents ); // Remove @todo statements
	$contents = preg_replace( '/(.*@author).*/', sprintf( '\\1 %s', $your_plugin['author'] ), $contents ); // Change package authorship

	// Cleanup double empty lines
	//$contents = str_replace( "\r\n\r\n", "\r\n", $contents );
	//$contents = str_replace( "\n\n", "\n", $contents );

	return $contents;
}

?>