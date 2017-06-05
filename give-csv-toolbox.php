<?php
/**
 * Plugin Name:     Give - CSV Toolbox
 * Plugin URI:      https://givewp.com/addons/give-csv/
 * Description:     Export data in CSV format.
 * Version:         1.0
 * Author:          WordImpress
 * Author URI:      https://wordimpress.com
 * Text Domain:     give-csv-toolbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'GIVE_CSV_TOOLBOX_VERSION' ) ) {
	define( 'GIVE_CSV_TOOLBOX_VERSION', '1.0' );
}

// Min. Give version.
if ( ! defined( 'GIVE_CSV_TOOLBOX_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_CSV_TOOLBOX_MIN_GIVE_VERSION', '1.8.4' );
}

// Plugin path.
if ( ! defined( 'GIVE_CSV_TOOLBOX_DIR' ) ) {
	define( 'GIVE_CSV_TOOLBOX_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin URL.
if ( ! defined( 'GIVE_CSV_TOOLBOX_URL' ) ) {
	define( 'GIVE_CSV_TOOLBOX_URL', plugin_dir_url( __FILE__ ) );
}

// Basename.
if ( ! defined( 'GIVE_CSV_TOOLBOX_BASENAME' ) ) {
	define( 'GIVE_CSV_TOOLBOX_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Main Give_CSV_Toolbox class.
 *
 * @since       1.0
 */
if ( ! class_exists( 'Give_CSV_Toolbox' ) ) {

	/**
	 * Class Give_CSV_Toolbox
	 */
	class Give_CSV_Toolbox {

		/**
		 * @var         Give_CSV_Toolbox $instance The one true Give_CSV_Toolbox.
		 *
		 * @since       1.0
		 */
		private static $instance;

		/**
		 * Get active instance.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      object self::$instance The one true Give_CSV_Toolbox
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Give_CSV_Toolbox();
				self::$instance->load_textdomain();
				self::$instance->hooks();
				self::$instance->includes();
			}

			return self::$instance;
		}


		/**
		 * Fire hooks.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function hooks() {

			// Load scripts and style.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		}

		/**
		 * Enqueue scripts.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @param $hook
		 */
		public function admin_enqueue_scripts( $hook ) {

			if ( isset( $_GET['tab'] ) && 'csv-toolbox' === $_GET['tab'] ) {

				wp_register_style( 'give-csv-css', GIVE_CSV_TOOLBOX_URL . 'assets/css/give-csv-toolbox.css' );
				wp_enqueue_style( 'give-csv-css' );

				wp_register_script( 'give-csv-js', GIVE_CSV_TOOLBOX_URL . 'assets/js/give-csv-toolbox.js', array( 'jquery' ) );
				wp_enqueue_script( 'give-csv-js' );

				$ajax_vars = array(
					'wp_debug' => WP_DEBUG,
				);

				wp_localize_script( 'give-csv-js', 'give_csv_toolbox_vars', $ajax_vars );

			}

		}

		/**
		 * Include necessary files.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function includes() {
			include GIVE_CSV_TOOLBOX_DIR . 'includes/give-csv-toolbox-settings.php';
			include GIVE_CSV_TOOLBOX_DIR . 'includes/give-csv-toolbox-functions.php';
		}

		/**
		 * Internationalization.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function load_textdomain() {

			// Set filter for language directory
			$lang_dir = GIVE_CSV_TOOLBOX_DIR . '/languages/';
			$lang_dir = apply_filters( 'give_csv_toolbox_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-csv-toolbox' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-csv-toolbox', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/give-csv-toolbox/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/give-csv-toolbox/ folder.
				load_textdomain( 'give-csv-toolbox', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/give-csv-toolbox/languages/ folder.
				load_textdomain( 'give-csv-toolbox', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'give-csv-toolbox', false, $lang_dir );
			}
		}

	}
}

/**
 * CSV Toolbox Load.
 *
 * @return object|bool Give_CSV_Toolbox
 */
function give_csv_toolbox_load() {

	if ( give_csv_toolbox_check_environment() ) {
		return Give_CSV_Toolbox::instance();
	}

	return false;
}

add_action( 'plugins_loaded', 'give_csv_toolbox_load', 1 );


/**
 * Check the environment before starting up.
 *
 * @since 1.0
 *
 * @return bool
 */
function give_csv_toolbox_check_environment() {

	// Check for if give plugin activate or not.
	$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? true : false;

	// Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( current_user_can( 'activate_plugins' ) && ! $is_give_active ) {
		add_action( 'admin_notices', 'give_csv_toolbox_activation_notice' );
		add_action( 'admin_init', 'give_csv_toolbox_deactivate_self' );

		return false;
	}

	// Check minimum Give version.
	if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, GIVE_CSV_TOOLBOX_MIN_GIVE_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'give_csv_toolbox_min_version_notice' );
		add_action( 'admin_init', 'give_csv_toolbox_deactivate_self' );

		return false;
	}

	return true;

}

/**
 * Deactivate self. Must be hooked with admin_init.
 *
 * Currently hooked via give_csv_toolbox_check_environment()
 */
function give_csv_toolbox_deactivate_self() {
	deactivate_plugins( GIVE_CSV_TOOLBOX_BASENAME );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}


/**
 * Notice for No Core Activation
 *
 * @since 1.0
 */
function give_csv_toolbox_activation_notice() {
	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> You must have the <a href="https://givewp.com/" target="_blank">Give</a> plugin installed and activated for the CSV Toolbox add-on to activate.', 'give-csv-toolbox' ) . '</p></div>';
}

/**
 * Notice for No Core Activation
 *
 * @since 1.0
 */
function give_csv_toolbox_min_version_notice() {
	echo '<div class="error"><p>' . sprintf( __( '<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> version %2$s+ for the CSV Toolbox add-on to activate.', 'give-csv-toolbox' ), 'https://givewp.com', GIVE_CSV_TOOLBOX_MIN_GIVE_VERSION ) . '</p></div>';
}

