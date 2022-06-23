<?php
/**
 * Plugin Name:       Fast WP Speed
 * Plugin URI:        https://fastwpspeed.com/fastwpspeed
 * Description:       Fast PageSpeed Insights in your dashboard.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            fastwpspeed
 * Author URI:        https://fastwpspeed.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fastwpspeed
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/************************
 * Register a admin menu
 */
function my_admin_menu() {
	add_menu_page(
		__( 'Fast WP Speed', 'fastwpspeed' ),
		__( 'Fast WP Speed', 'fastwpspeed' ),
		'manage_options',
		'fastwpspeed-page',
		'fastwpspeed_admin_page_contents',
		'dashicons-dashboard',
		3
	);
}
add_action( 'admin_menu', 'my_admin_menu' );

// Enqueue on this page.
function load_custom_wp_admin_style( $hook ) {

	// Load only on ?page=fastwpspeed-page.
	if ( $hook !== 'toplevel_page_fastwpspeed-page' ) {
		return;
	}
	wp_enqueue_style( 'fwps-styles-bs', plugin_dir_url( __FILE__ ) . 'vendor/bootstrap/css/bootstrap.min.css' );
	wp_enqueue_style( 'fwps-styles-fw', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css' );
	wp_enqueue_style( 'fwps-styles-progressbar', plugin_dir_url( __FILE__ ) . 'asset/progressbar.css' );
	wp_enqueue_style( 'fwps-styles', plugin_dir_url( __FILE__ ) . 'css/style.css' );

	// Scripts.
	wp_enqueue_script( 'fwps-script-jquery', '//code.jquery.com/jquery-3.6.0.min.js' );
	wp_enqueue_script( 'fwps-script-bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js' );
	wp_enqueue_script( 'fwps-script-progressbar', plugin_dir_url( __FILE__ ) . 'asset/progress.js' );
	wp_enqueue_script( 'fwps-script-main', plugin_dir_url( __FILE__ ) . 'asset/script.js' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

// Display on this page.
function fastwpspeed_admin_page_contents() {

	require_once plugin_dir_path( __FILE__ ) . 'index.php';
}
