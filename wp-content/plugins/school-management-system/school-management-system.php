<?php
/*
 * Plugin Name: The School Management - Education & Learning Management
 * Plugin URI: https://wordpress.org/plugins/school-management-system/
 * Description: The School Management System is a WordPress plugin to manage school related entities such as classes, sections, students, ID cards, teachers, staff, fees, invoices, noticeboard and much more. Its completly solutions for school mangament.
 * Version: 3.8
 * Author: Weblizar
 * Author URI: https://weblizar.com
 * Text Domain: school-management
*/

defined( 'ABSPATH' ) || die();

if ( ! defined( 'WLSM_PLUGIN_URL' ) ) {
	define( 'WLSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WLSM_PLUGIN_DIR_PATH' ) ) {
	define( 'WLSM_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

final class WLSM_School_Management {

	private static $instance = null;

	private function __construct() {
		$this->initialize_hooks();
		$this->setup_database();
	}

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function initialize_hooks() {
		if ( is_admin() ) {
			require_once WLSM_PLUGIN_DIR_PATH . 'admin/admin.php';
		}
		require_once WLSM_PLUGIN_DIR_PATH . 'public/public.php';
	}

	private function setup_database() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/WLSM_Database.php';
		register_activation_hook( __FILE__, array( 'WLSM_Database', 'activation' ) );
		register_uninstall_hook( __FILE__, array( 'WLSM_Database', 'uninstall' ) );
	}
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'add_plugin_page_settings_link' );
function add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' . admin_url( 'admin.php?page=sm-settings' ) . '">' . __( 'Settings' ) . '</a>';
	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'add_plugin_page_pro_link' );
function add_plugin_page_pro_link( $links ) {
	$links[] = '<a href="' . ( 'https://weblizar.com/plugins/school-management/' ) . '" style="color:red;"> ' . __( 'Get Pro' ) . '</a>';
	return $links;
}

WLSM_School_Management::get_instance();
