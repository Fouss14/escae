<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

class WLSM_Shortcode {
	public static function account($attr) {
		self::enqueue_assets();
		ob_start();
		return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/route.php';
	}

	public static function fees($attr) {
		self::enqueue_assets();
		wp_enqueue_script('stripe-checkout', '//checkout.stripe.com/checkout.js', array(), NULL, true);
		ob_start();
		return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/forms/fees.php';
	}

	public static function inquiry($attr) {
		self::enqueue_assets();
		ob_start();
		return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/forms/inquiry.php';
	}

	public static function noticeboard($attr) {
		self::enqueue_assets();
		ob_start();
		return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/noticeboard/index.php';
	}

	public static function enqueue_assets() {
		wp_enqueue_style('jquery-confirm', WLSM_PLUGIN_URL . 'assets/css/jquery-confirm.min.css');
		wp_enqueue_style('toastr', WLSM_PLUGIN_URL . 'assets/css/toastr.min.css');

		wp_enqueue_style('wlsm-print-preview', WLSM_PLUGIN_URL . 'assets/css/print/wlsm-preview.css', array(), '1.6', 'all');
		wp_enqueue_style('wlsm', WLSM_PLUGIN_URL . 'assets/css/wlsm.css', array(), '1.6', 'all');
		wp_enqueue_style('wlsm-dashboard', WLSM_PLUGIN_URL . 'assets/css/wlsm-dashboard.css', array(), '1.6', 'all');

		wp_enqueue_script('jquery-confirm', WLSM_PLUGIN_URL . 'assets/js/jquery-confirm.min.js', array('jquery'), true, true);
		wp_enqueue_script('toastr', WLSM_PLUGIN_URL . 'assets/js/toastr.min.js', array('jquery'), true, true);

		wp_enqueue_script('wlsm-public', WLSM_PLUGIN_URL . 'assets/js/wlsm.js', array('jquery', 'jquery-form'), '1.8', true);
		
		wp_localize_script('wlsm-public', 'wlsmdateformat', array(
			'wlsmDateFormat' => WLSM_Config::date_format()) );

		wp_localize_script('wlsm-public', 'wlsmajaxurl', array(
			'url' => admin_url('admin-ajax.php')) );
			
		wp_localize_script('wlsm-public', 'wlsmadminurl', array(
			'wlsmAdminURL' => admin_url()) );
	}
}
