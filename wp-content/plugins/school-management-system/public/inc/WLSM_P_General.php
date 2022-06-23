<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

class WLSM_P_General {
	public static function get_school_classes() {
		if (!wp_verify_nonce($_POST['nonce'], 'get-school-classes')) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id  = isset($_POST['school_id']) ? absint($_POST['school_id']) : 0;
			$session_id = isset($_POST['session_id']) ? absint($_POST['session_id']) : 0;

			// Checks if school exists.
			$school = WLSM_M_School::get_active_school($school_id);

			if (!$school) {
				throw new Exception(esc_html__('School not found.', 'school-management-system'));
			}

			if ($session_id) {
				// Check if session exists.
				$session = WLSM_M_Session::get_session($session_id);

				if (!$session) {
					throw new Exception(esc_html__('Session not found.', 'school-management-system'));
				}
			}

			$classes = WLSM_M_Staff_General::fetch_school_classes($school_id);

			$classes = array_map(function ($class) {
				$class->label = WLSM_M_Class::get_label_text($class->label);
				return $class;
			}, $classes);

			wp_send_json($classes);
		} catch (Exception $exception) {
			$buffer = ob_get_clean();
			if (!empty($buffer)) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json(array());
		}
	}
}
