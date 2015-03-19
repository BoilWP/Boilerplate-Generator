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
			'Plugin Name' => $your_plugin['name'],
			'Plugin URI'  => esc_url_raw( $your_plugin['uri'] ),
			'Author'      => $your_plugin['author'],
			'Author URI'  => esc_url_raw( $your_plugin['author_uri'] ),
			'Description' => $your_plugin['description'],
			'Text Domain' => $your_plugin['slug'],
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

	$contents = str_replace( 'plugin_name_', $slug . '_', $contents ); // Function names.
	$contents = str_replace( '_plugin_name', '_' . $slug, $contents ); // Function names.

	$contents = preg_replace( '/(.*)\$GLOBALS\[.plugin_name.\](.*)/', '\\1$GLOBALS["' . $slug . '"]\\2', $contents ); // Global array indices.
	$contents = preg_replace( '/(.*)\$plugin_name(.*)/', '\\1$' . $slug . '\\2', $contents ); // Global variable names.

	$contents = str_replace( 'plugin_name', $your_plugin['slug'], $contents ); // Miscellaneous strings and identifiers.
	$contents = str_replace( 'plugin-name', $your_plugin['slug'], $contents ); // Filename identifiers.

	$contents = str_replace( 'PLUGIN_NAME', strtoupper( $slug ), $contents ); // Definition names.

	$contents = str_replace( 'Boilerplate_Generator', implode( '_', array_map( 'ucfirst', explode( '-', $your_plugin['slug'] ) ) ), $contents ); // Classes, etc.

	$contents = str_replace( "'" . $prototype['name'] . "'", "'" . $your_plugin['slug'] . "'", $contents ); // Strings
	$contents = str_replace( "\\'" . $prototype['name'] . "\\'", "\\'" . $your_plugin['slug'] . "\\'", $contents ); // Strings
	$contents = str_replace( '"' . $prototype['name'] . '"', '"' . $your_plugin['slug'] . '"', $contents ); // Strings
	$contents = str_replace( '\"' . $prototype['name'] . '\"', '\"' . $your_plugin['slug'] . '\"', $contents ); // Strings

	$contents = preg_replace( '/.*@todo.*/', '', $contents ); // Remove @todo statements
	$contents = preg_replace( '/(.*@author).*/', sprintf( '\\1 %s', $your_plugin['author'] ), $contents ); // Change package authorship

	// Cleanup double empty lines
	$contents = str_replace( "\r\n\r\n", "\r\n", $contents );
	$contents = str_replace( "\n\n", "\n", $contents );

	return $contents;
}

?>