<?php
/*
Plugin Name: BP API
Plugin URI: https://github.com/modemlooper/bp-api
Description: json API for BuddyPress. This plugin creates json api endpoints for https://github.com/WP-API
Author: modemlooper, djpaul
Version: 0.1
Author URI: https://github.com/modemlooper/bp-api
*/


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BuddyPress_API' ) ) :

	class BuddyPress_API {

		/**
		 * Main BuddyPress API Instance.
		 *
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if ( null === $instance ) {
				$instance = new BuddyPress_API;
				$instance->constants();
				$instance->actions();
			}

			// Always return the instance
			return $instance;

		}


		/**
		 * A dummy constructor to prevent BuddyPress API from being loaded more than once.
		 *
		 */
		private function __construct() { /* Do nothing here */ }


		/**
		 * Bootstrap constants.
		 *
		 */
		private function constants() {

			// define api endpint prefix
			if ( ! defined( 'BP_API_SLUG' ) ) {
				define( 'BP_API_SLUG', 'bp' );
			}

			// Define a constant that can be checked to see if the component is installed or not.
			if ( ! defined( 'BP_API_IS_INSTALLED' ) ) {
				define( 'BP_API_IS_INSTALLED', 1 );
			}

			// Define a constant that will hold the current version number of the component
			// This can be useful if you need to run update scripts or do compatibility checks in the future
			if ( ! defined( 'BP_API_VERSION' ) ) {
				define( 'BP_API_VERSION', '0.1' );
			}

			// Define a constant that we can use to construct file paths and url
			if ( ! defined( 'BP_API_PLUGIN_DIR' ) ) {
				define( 'BP_API_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			}

			if ( ! defined( 'BP_API_PLUGIN_URL' ) ) {
				$plugin_url = plugin_dir_url( __FILE__ );

				// If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
				if ( is_ssl() )
					$plugin_url = str_replace( 'http://', 'https://', $plugin_url );

				define( 'BP_API_PLUGIN_URL', $plugin_url );
			}

		}


		/**
		 * actions.
		 *
		 */
		private function actions() {

			register_activation_hook( __FILE__, array( $this, 'bp_api_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'bp_api_deactivate' ) );

			// is BuddyPress plugin active? If not, throw a notice and deactivate
			if ( ! in_array( 'buddypress/bp-loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'all_admin_notices', array( $this, 'bp_api_buddypress_required' ) );
				return;
			}

			// is JSON API plugin active? If not, throw a notice and deactivate
			if ( ! in_array( 'json-rest-api/plugin.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'all_admin_notices', array( $this, 'bp_api_wp_api_required' ) );
				return;
			}

			add_action( 'bp_include', array( $this, 'bp_api_init' ) );
		}


		/**
		 * bp_api_init function.
		 *
		 * @access public
		 * @return void
		 */
		public function bp_api_init() {

			// requires BP 2.0 or greater.
			if ( version_compare( BP_VERSION, '2.0', '>' ) ) {
				include_once( dirname( __FILE__ ) . '/endpoints/bp-api-core.php' );
				include_once( dirname( __FILE__ ) . '/endpoints/bp-api-activity.php' );
				include_once( dirname( __FILE__ ) . '/endpoints/bp-api-xprofile.php' );
			}

			add_action( 'wp_json_server_before_serve', array( $this, 'create_bp_endpoints' ), 0 );
		}


		/**
		 * bp_api_activate function.
		 *
		 * @access public
		 * @return void
		 */
		public function bp_api_activate() {
		}


		/**
		 * bp_api_deactivate function.
		 *
		 * @access public
		 * @return void
		 */
		public function bp_api_deactivate() {
		}


		/**
		 * bp_api_buddypress_required function.
		 *
		 * @access public
		 * @return void
		 */
		public function bp_api_buddypress_required() {
			echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the BuddyPress plugin to be installed/activated. %1$s has been deactivated.', 'appbuddy' ), 'BuddyPress API' ) .'</p></div>';
			deactivate_plugins( plugin_basename( __FILE__ ), true );
		}


		/**
		 * bp_api_wp_api_required function.
		 *
		 * @access public
		 * @return void
		 */
		public function bp_api_wp_api_required() {
			echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the WP API plugin to be installed/activated. %1$s has been deactivated.', 'appbuddy' ), 'BuddyPress API' ) .'</p></div>';
			deactivate_plugins( plugin_basename( __FILE__ ), true );
		}


		public function create_bp_endpoints() {

			/*
			* BP Core
			*/
			$bp_api_core = new BP_API_Core;
			register_json_route( BP_API_SLUG, '/*', array(
				'methods'         => 'GET',
				'callback'        => array( $bp_api_core, 'get_info' ),
			) );

			/*
			* BP Activity
			*/
			if ( bp_is_active( 'activity' ) ) {
				$bp_api_activity = new BP_API_Activity;
				register_json_route( BP_API_SLUG, '/activity', array(
					'methods'         => 'GET',
					'callback'        => array( $bp_api_activity, 'get_items' ),
				) );
				register_json_route( BP_API_SLUG, '/activity/(?P<id>\d+)', array(
					'methods'         => 'GET',
					'callback'        => array( $bp_api_activity, 'get_item' ),
				) );
			}

			/*
			* BP xProfile
			*/
			if ( bp_is_active( 'xprofile' ) ) {
				$bp_api_xprofile = new BP_API_xProfile;
				register_json_route( BP_API_SLUG, '/xprofile', array(
					'methods'         => 'GET',
					'callback'        => array( $bp_api_xprofile, 'get_items' ),
				) );
				register_json_route( BP_API_SLUG, '/xprofile/(?P<id>\d+)', array(
					'methods'         => 'GET',
					'callback'        => array( $bp_api_xprofile, 'get_item' ),
				) );
			}


		}



	}

endif;

function bp_api() {
	return BuddyPress_API::instance();
}
bp_api();