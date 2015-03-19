<?php
/**
 * Plugin Name:       Boilerplate Generator
 * Plugin URI:        https://github.com/BoilWP/Boilerplate-Generator
 * Description:       This helps developers generate a WordPress plugin based on one of BoilWP's boilerplates.
 * Version:           0.0.2
 * Author:            Sébastien Dumont, Gennady Kovshenin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       boilwp
 * Domain Path:       languages
 * Network:           false
 * GitHub Plugin URI: https://github.com/BoilWP/Boilerplate-Generator
 *
 * @package Boilerplate Generator
 */
if ( ! defined( 'ABSPATH' ) ) exit();

if ( ! class_exists( 'Boilerplate_Generator' ) ) {

/**
 * Main Boilerplate Generator Class
 *
 * @since 0.0.2
 */
final class Boilerplate_Generator {

	/**
	 * The single instance of the class
	 *
	 * @since  0.0.2
	 * @access protected
	 * @var    object
	 */
	protected static $_instance = null;

	/**
	 * Slug
	 *
	 * @since  0.0.2
	 * @access public
	 * @var    string
	 */
	public $plugin_slug = 'boilwp_generator';

	/**
	 * The Plugin Version.
	 *
	 * @since  0.0.2
	 * @access public
	 * @var    string
	 */
	public $version = "0.0.2";

	/**
	 * Main Boilerplate Generator Instance
	 *
	 * Ensures only one instance of Boilerplate Generator is loaded or can be loaded.
	 *
	 * @since  0.0.2
	 * @access public static
	 * @see    Boilerplate_Generator()
	 * @return Boilerplate Generator instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Boilerplate_Generator;
		}
		return self::$_instance;
	} // END instance()

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since  0.0.2
	 * @access public
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wordpress-plugin-boilerplate-light' ), $this->version );
	} // END __clone()

	/**
	 * Disable unserializing of the class
	 *
	 * @since  0.0.2
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wordpress-plugin-boilerplate-light' ), $this->version );
	} // END __wakeup()

	/**
	 * Constructor
	 *
	 * @since  0.0.2
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Hooks
		add_action( 'init', array( $this, 'init_boilerplate_generator' ), 0 );
	}

	/**
	 * Define Constants
	 *
	 * @since  0.0.2
	 * @access private
	 */
	private function define_constants() {
		if ( ! defined( 'BOILWP_SLUG' ) )        define( 'BOILWP_SLUG', $this->plugin_slug );

		if ( ! defined( 'BOILWP_VERSION' ) )     define( 'BOILWP_VERSION', $this->version );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		if ( ! defined( 'BOILWP_SCRIPT_MODE' ) ) define( 'BOILWP_SCRIPT_MODE', $suffix );
	}

	/**
	 * Runs when the plugin is initialized.
	 *
	 * @since  0.0.2
	 * @access public
	 */
	public function init_boilerplate_generator() {
		// Set up localisation
		$this->load_plugin_textdomain();

		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();

		// Include functions
		$this->include_functions();

		// Init action
		do_action( 'boilerplate_generator_init' );
	} // END init_boilerplate_generator()

	/**
	 * Include
	 *
	 * @since  0.0.2
	 * @access public
	 */
	public function include_functions() {
		if ( !is_admin() ) {
			include_once( $this->plugin_path() . '/includes/core-functions.php' );
			include_once( $this->plugin_path() . '/includes/boilerplate-generator-form.php' );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any
	 * following ones if the same translation is present.
	 *
	 * @since  0.0.2
	 * @access public
	 * @filter boilerplate_generator_languages_directory
	 * @filter plugin_locale
	 * @return void
	 */
	public function load_plugin_textdomain() {
		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( PLUGIN_NAME_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'boilerplate_generator_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale',  get_locale(), $this->text_domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->text_domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->text_domain . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/boilerplate-generator/ folder
			load_textdomain( $this->text_domain, $mofile_global );
		}
		else if ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/boilerplate-generator/languages/ folder
			load_textdomain( $this->text_domain, $mofile_local );
		}
		else {
			// Load the default language files
			load_plugin_textdomain( $this->text_domain, false, $lang_dir );
		}
	} // END load_plugin_textdomain()

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @since  0.0.2
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	} // END plugin_url()

	/**
	 * Get the plugin path.
	 *
	 * @since  0.0.2
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	} // END plugin_path()

	/**
	 * Registers and enqueues stylesheets and javascripts
	 * for the administration panel and the front of the site.
	 *
	 * @since  0.0.2
	 * @access private
	 * @filter boilerplate_generator_admin_params
	 * @filter boilerplate_generator_params
	 */
	private function register_scripts_and_styles() {
		if ( ! is_admin() ) {
			// Boilerplate Generator Stylesheet
			$this->load_file( BOILWP_SLUG . '_style', '/assets/css/boilerplate-generator' . BOILWP_SCRIPT_MODE . '.css' );

			// Boilerplate Generator Scripts
			$this->load_file( BOILWP_SLUG . '-bootstrap', '/assets/js/bootstrap' . BOILWP_SCRIPT_MODE . '.js', true, array( 'jquery' ), BOILWP_VERSION );
			$this->load_file( BOILWP_SLUG . '_script', '/assets/js/boilerplate-generator' . BOILWP_SCRIPT_MODE . '.js', true, array( 'jquery' ), BOILWP_VERSION );


			// Variables for JS scripts
			wp_localize_script( BOILWP_SLUG . '_script', 'boilerplate_generator_params', apply_filters( 'boilerplate_generator_params', array(
				'plugin_url' => $this->plugin_url(),
			) ) );
		} // end if/else
	} // END register_scripts_and_styles()

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @since  0.0.2
	 * @access private
	 * @param  string  $name       The ID to register with WordPress.
	 * @param  string  $file_path  The path to the actual file.
	 * @param  bool    $is_script  Optional, argument for if the incoming file_path is a JavaScript source file.
	 * @param  array   $support    Optional, for requiring other javascripts for the source file you are calling.
	 * @param  string  $version    Optional, can match the version of the plugin or version of the source file.
	 * @global string  $wp_version
	 */
	private function load_file( $name, $file_path, $is_script = false, $support = array(), $version = '' ) {
		global $wp_version;

		$url  = $this->plugin_url() . $file_path;
		$file = $this->plugin_path() . $file_path;

		if ( file_exists( $file ) ) {
			if ( $is_script ) {
				wp_register_script( $name, $url, $support, $version );
				wp_enqueue_script( $name );
			}
			else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // END load_file()

} // END Boilerplate_Generator()

} // END class_exists('Boilerplate_Generator')

/**
 * Returns the instance of Boilerplate_Generator to prevent the need to use globals.
 *
 * @since  0.0.2
 * @return Boilerplate Generator
 */
function Boilerplate_Generator() {
	return Boilerplate_Generator::instance();
}

// Global for backwards compatibility.
$GLOBALS['boilwp'] = Boilerplate_Generator();

?>