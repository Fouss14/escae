<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/WLSM_Database.php';

class WLSM_Setting {
	public static function save_general_settings() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		if ( ! wp_verify_nonce( $_POST['save-general-settings'], 'save-general-settings' ) ) {
			die();
		}

		$active_session = isset( $_POST['active_session'] ) ? absint( $_POST['active_session'] ) : 0;
		$date_format    = isset( $_POST['date_format'] ) ? sanitize_text_field( $_POST['date_format'] ) : '';
		$currency       = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
		$gdpr_enable    = isset( $_POST['gdpr_enable'] ) ? (bool) ( $_POST['gdpr_enable'] ) : false;

		$sessions         = WLSM_M_Session::fetch_sessions();
		$currency_symbols = WLSM_Helper::currency_symbols();
		$date_formats     = WLSM_Helper::date_formats();

		$session_exist = WLSM_M_Session::get_session( $active_session );
		if ( ! $session_exist ) {
			$active_session = '';
		}

		if ( ! in_array( $date_format, array_keys( $date_formats ) ) ) {
			$date_format = WLSM_Config::get_default_date_format();
		}

		if ( ! in_array( $currency, array_keys( $currency_symbols ) ) ) {
			$currency = WLSM_Config::get_default_currency();
		}

		update_option( 'wlsm_current_session', $active_session );
		update_option( 'wlsm_date_format', $date_format );
		update_option( 'wlsm_currency', $currency );
		update_option( 'wlsm_gdpr_enable', $gdpr_enable );

		$message = esc_html__( 'General settings saved.', 'school-management-system' );

		wp_send_json_success( array( 'message' => $message ) );
	}

	public static function reset_plugin() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		if ( ! wp_verify_nonce( $_POST['reset-plugin'], 'reset-plugin' ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$wpdb->query( 'BEGIN;' );

			// Drop all tables and delete options.
			WLSM_Database::remove_data();

			// Run activation function.
			WLSM_Database::activation();

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}

			$wpdb->query( 'COMMIT;' );

			$message = esc_html__( 'Plugin has been reset to its initial state.', 'school-management-system' );
			wp_send_json_success( array( 'message' => $message, 'reload' => true ) );

		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}

		wp_send_json_error( esc_html__( 'Unable to reset the plugin.', 'school-management-system' ) );
	}

	public static function save_uninstall_settings() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		if ( ! wp_verify_nonce( $_POST['save-uninstall-settings'], 'save-uninstall-settings' ) ) {
			die();
		}

		$delete_on_uninstall = isset( $_POST['delete_on_uninstall'] ) ? (bool) ( $_POST['delete_on_uninstall'] ) : 0;

		update_option( 'wlsm_delete_on_uninstall', $delete_on_uninstall );

		$message = esc_html__( 'Uninstall settings saved.', 'school-management-system' );

		wp_send_json_success( array( 'message' => $message ) );
	}
}
