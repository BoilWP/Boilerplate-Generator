<?php
error_reporting(E_ALL ^ E_NOTICE);
@ini_set( 'display_errors', 'On' );
@ini_set( 'error_reporting', E_ALL );

require_once dirname( __FILE__ ) . '/wp-stubs.php';

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

	$contents = str_replace( 'plugin_name', $your_plugin['slug'], $contents ); // Miscellaneous strings and identifiers
	$contents = str_replace( 'plugin-name', $your_plugin['slug'], $contents ); // Filename identifiers

	$contents = str_replace( 'PLUGIN_NAME', strtoupper( $slug ), $contents ); // Definition names.

	$contents = str_replace( 'Plugin_Name', implode( '_', array_map( 'ucfirst', explode( '-', $your_plugin['slug'] ) ) ), $contents ); // Classes, etc.

	$contents = str_replace( "'" . $prototype['name'] . "'", "'" . $your_plugin['slug'] . "'", $contents ); // Strings
	$contents = str_replace( "\\'" . $prototype['name'] . "\\'", "\\'" . $your_plugin['slug'] . "\\'", $contents ); // Strings
	$contents = str_replace( '"' . $prototype['name'] . '"', '"' . $your_plugin['slug'] . '"', $contents ); // Strings
	$contents = str_replace( '\"' . $prototype['name'] . '\"', '\"' . $your_plugin['slug'] . '\"', $contents ); // Strings

	$contents = preg_replace( '/.*@todo.*/', '', $contents ); // Remove @todo statements
	$contents = preg_replace( '/(.*@author).*/', sprintf( '\\1 %s', $your_plugin['author'] ), $contents ); // Change package authorship

	return $contents;
}

function _init() {
	if ( ! isset( $_REQUEST['wp_plugin_boilerplate_generate'], $_REQUEST['wp_plugin_boilerplate_name'] ) )
		return;

	if ( empty( $_REQUEST['wp_plugin_boilerplate_name'] ) )
		die( 'Please enter a plugin name. Please go back and try again.' );

	// Default values should a field be left empty.
	$your_plugin = array(
		'name'        => 'Plugin Name',
		'slug'        => 'plugin-name',
		'uri'         => 'http://www.sebastiendumont.com',
		'author'      => 'Sebastien Dumont',
		'author_uri'  => 'http://www.sebastiendumont.com',
		'description' => 'Description',
	);

	$your_plugin['name']  = trim( $_REQUEST['wp_plugin_boilerplate_name'] );

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

	$zip = new ZipArchive;
	$zip_filename = sprintf( '/tmp/wp-plugin-boilerplate-%s.zip', md5( print_r( $your_plugin, true ) ) );
	$zip->open( $zip_filename, ZipArchive::CREATE && ZipArchive::OVERWRITE );

	$prototypes_dir = dirname( __FILE__ ) . '/prototypes/';
	$prototypes_map = array(
		'wordpress-plugin' => array(
			'id' => 'wordpress-plugin',
			'upstream' => 'https://github.com/seb86/WordPress-Plugin-Boilerplate.git',
			'checkout' => 'a52b6613109a2b8b773cb599c829d3775a82d6f1',
			'name' => 'wordpress-plugin-boilerplate',
			'fullname' => 'WordPress Plugin Boilerplate',
		),
		// ... add the other types here
	);

	if ( empty( $_REQUEST['boilerplate'] ) || empty( $prototypes_map[$_REQUEST['boilerplate']] ) ) {
		die( 'Invalid boilerplate type. Please go back and try again.' );
	}

	$prototype = $prototypes_map[$_REQUEST['boilerplate']];

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
		exec( sprintf( '%s pull origin master', escapeshellarg( $GIT_BIN ) ) );
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

_init();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Generate a WordPress plugin from a professionally well-written boilerplate of your choosing, giving you the proper structure before developing the core features of your plugin.">

	<title>Boilerplate Generator</title>

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/jumbotron-narrow.css" rel="stylesheet">
	<style type="text/css">
	.advanced-control,
	.github-branch{ display:none; }
	</style>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	</head>

	<body>

	<div class="container">
		<div class="header">
			<ul class="nav nav-pills pull-right">
				<li><a href="https://github.com/seb86/Boilerplate-Generator/tree/master" target="_blank">Source on GitHub</a></li>
			</ul>
			<h3 class="text-muted">Boilerplate Generator</h3>
		</div>

		<div class="jumbotron">
			<h1>Welcome</h1>
			<p class="lead">The generator will save you the time to replace all the strings within the boilerplate so that it will be ready for you to start coding the main feature of your plugin instantly.</p>
			<span>Simply fill in your variables and a plugin will be generated for you to start coding.</p>
		</div>

		<form role="form" method="post" action="just-generate.php">
			<input type="hidden" name="wp_plugin_boilerplate_generate" value="1" />

			<div class="form-group">
				<label for="which-boilerplate">What type of WordPress plugin are we developing?</label>
				<div class="radio">
					<label><input type="radio" name="boilerplate" value="wordpress-plugin" checked="checked"> WordPress Plugin</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="boilerplate" value="woo-extension" disabled="disabled"> WooCommerce Extension</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="boilerplate" value="woo-payment-gateway" disabled="disabled"> WooCommerce Payment Gateway</label>
				</div>
			</div>

			<div class="form-group plugin-size">
				<label for="plugin-size">Are you developing a standard plugin or small plugin?</label>
				<div class="radio">
					<label><input type="radio" name="plugin-size" value="standard" checked="checked"> Standard</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="plugin-size" value="small" disabled="disabled"> Small (e.g. Widgets, Shortcodes, Custom Post Type)</label>
				</div>
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-name">Plugin Name *</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_name" placeholder="Enter the name of the plugin">
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-description">Plugin Description *</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_description" placeholder="Enter a description of the plugin">
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-text-domain">Plugin URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_plugin_uri" placeholder="Enter the URI of the plugin">
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-slug">Plugin Slug</label>
				<input type="text" class="form-control" id="wp-plugin-boilerplate-slug" name="wp_plugin_boilerplate_slug" placeholder="Enter the plugin slug" />
				<span>Example 'my-plugin-is-awesome'</span>
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-author">Author *</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_author" placeholder="Enter the name of the author" />
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-author-uri">Author URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_author_uri" placeholder="Enter the URI of the author">
			</div>

			<div class="form-group">
				<label for="network">Network ?</label>
				<div class="radio">
					<label><input type="radio" name="network" value="false" checked="checked"> False</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="network" value="true"> True</label>
				</div>
			</div>

			<div class="form-group">
				<label for="network">Will your plugin be supporting GitHub updater?</label>
				<div class="radio">
					<label><input type="radio" name="support_github" value="no" checked="checked"> No</label>
				</div>
				<div class="radio">
					<label><input type="radio" name="support_github" value="true"> Yes</label>
				</div>
				<span><a href="https://github.com/afragen/github-updater" target="_blank">How do I support GitHub updater?</a></span>
			</div>

			<div class="form-group">
				<label for="wp-plugin-boilerplate-github-plugin-url">GitHub Plugin URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_github_plugin_uri" placeholder="Enter the GitHub repository this plugin will be stored at">
			</div>

			<div class="form-group">
				<a class="advanced-options" href="#">Advanced Options</a>
			</div>

			<div class="form-group advanced-control github-branch">
				<label for="wp-plugin-boilerplate-github_branch">GitHub Branch</label>
				<input type="radio" name="wp_plugin_boilerplate_github_branch" value="master" checked="checked"> Master
				<input type="radio" name="wp_plugin_boilerplate_github_branch" value="other"> Other
				<input type="text" class="form-control" name="wp_plugin_boilerplate_github_branch_other" style="width:200px;">
				<span>If other, specify the branch that the GitHub updater plugin will be checking.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-min-wp-version">Minimum WordPress Version Required</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_min_wp_version" placeholder="4.0">
				<span>Leave empty to use the latest stable version.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-memory-limit">Memory Limit</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_memory_limit" placeholder="320">
				<span>Set the amount of memory the plugin requires for it to function. Default is 320 (320 = 32MB)</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-market-uri">Plugin Market URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_market_uri" placeholder="http://">
				<span>Enter the url to where you are marketing this plugin.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-documentation-uri">Documentation URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_documentation_uri" placeholder="http://">
				<span>Enter the url to where the documentation for this plugin is located.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-wp-plugin-uri">WordPress Plugin URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_wp_plugin_uri" placeholder="https://wordpress.org/plugins/your-plugin-name">
				<span>Replace 'your-plugin-name' with the name of the plugin slug given for your wordpress repository.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-wp-plugin-support-uri">WordPress Support Plugin URI</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_wp_plugin_support_uri" placeholder="https://wordpress.org/support/plugin/your-plugin-name">
				<span>Replace 'your-plugin-name' with the name of the plugin slug given for your wordpress repository.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-transifex-project-name">Transifex Project Name</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_transifex_project_name">
				<span>Enter the name of your Transifex project. e.g. wordpress-plugin-boilerplate</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-transifex-resources-slug">Transifex Resources Slug</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_transifex_resources_slug">
				<span>Enter the name of your Transifex resources slug. e.g. wordpress-plugin-boilerplate</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-menu-name">Menu Name</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_menu_name" placeholder="My Plugin">
				<span>Enter the name of your plugin menu within the admin.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-title-name">Title Name</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_title_name" placeholder="My Plugin">
				<span>Enter the title of your plugin pages within the admin.</span>
			</div>

			<div class="form-group advanced-control">
				<label for="wp-plugin-boilerplate-manage-plugin">Level of control a user must have to control the plugin</label>
				<input type="text" class="form-control" name="wp_plugin_boilerplate_manage_plugin" placeholder="manage_options" value="manage_options">
				<span>Enter the user level that is required for the plugin to be controlled. <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">See Roles and Capabilities</a> for more info.</span>
			</div>

			<div class="form-group">
				<p class="text-center"><input class="btn btn-primary btn-lg" type="submit" name="wp_plugin_boilerplate_generate_submit" value="Generate Plugin" /></p>
			</div>
		</form>

		<div class="footer">
			<p>Project by <a href="http://www.sebastiendumont.com/" target="_blank">Sebastien Dumont</a> <span class="right"><?php echo date('Y'); ?></span></p>
		</div>

	</div> <!-- /container -->

	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
	jQuery(window).load(function(){
		var advanced = 'hide';
		var github_updater = 'hide';

		jQuery('a.advanced-options').on('click', function(){

			if( advanced == 'hide' ) {
				jQuery('.advanced-control').show();
				advanced = 'show';
			}
			else if( advanced == 'show' ) {
				jQuery('.advanced-control').hide();
				advanced = 'hide';
			}

			return false;
		});

		jQuery('input[type="radio"][name="support_github"]').on('click', function(){

			if( github_updater == 'hide' ) {
				jQuery('.github-branch').show();
				github_updater = 'show';
			}
			else if( github_updater == 'show' ) {
				jQuery('.github-branch').hide();
				github_updater = 'hide';
			}

		});

	});
	</script>
	</body>
</html>
