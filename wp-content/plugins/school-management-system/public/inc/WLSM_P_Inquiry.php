<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

class WLSM_P_Inquiry {
	public static function submit_inquiry() {
		if (!wp_verify_nonce($_POST['wlsm-submit-inquiry'], 'wlsm-submit-inquiry')) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$gdpr_enable = get_option('wlsm_gdpr_enable');

			// Inquiry.
			$name      = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
			$last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
			$phone     = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
			$email     = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
			$message   = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
			$class_id  = isset($_POST['class_id']) ? absint($_POST['class_id']) : 0;
			$school_id = isset($_POST['school_id']) ? absint($_POST['school_id']) : 0;

			// Start validation.
			$errors = array();

			if (empty($name)) {
				$errors['name'] = esc_html__('Please specify name.', 'school-management-system');
			}
			if (strlen($name) > 60) {
				$errors['name'] = esc_html__('Maximum length cannot exceed 60 characters.', 'school-management-system');
			}

			if (empty($phone)) {
				$errors['phone'] = esc_html__('Please provide your phone number.', 'school-management-system');
			} else if (strlen($phone) > 40) {
				$errors['phone'] = esc_html__('Maximum length cannot exceed 40 characters.', 'school-management-system');
			}

			if (!empty($email)) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$errors['email'] = esc_html__('Please provide a valid email.', 'school-management-system');
				} else if (strlen($email) > 60) {
					$errors['email'] = esc_html__('Maximum length cannot exceed 60 characters.', 'school-management-system');
				}
			}

			if (empty($message)) {
				$errors['message'] = esc_html__('Please write your message.', 'school-management-system');
			}

			if (empty($school_id)) {
				$errors['school_id'] = esc_html__('Please select a school.', 'school-management-system');
			} else {
				if (empty($class_id)) {
					$errors['class_id'] = esc_html__('Please select a class.', 'school-management-system');
				} else {
					$class_school = WLSM_M_Staff_Class::get_class($school_id, $class_id);
					if (!$class_school) {
						$errors['class_id'] = esc_html__('Class not found.', 'school-management-system');
					} else {
						$class_school_id = $class_school->ID;
					}
				}
			}

			if ($gdpr_enable) {
				$gdpr = isset($_POST['gdpr']) ? (bool) ($_POST['gdpr']) : false;
				if (!$gdpr) {
					$errors['gdpr'] = esc_html__('Please check for GDPR consent.', 'school-management-system');
				}
			}
		} catch (Exception $exception) {
			$buffer = ob_get_clean();
			if (!empty($buffer)) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error($response);
		}

		if (count($errors) < 1) {
			try {
				$wpdb->query('BEGIN;');

				// Inquiry data.
				$data = array(
					'name'            => $name,
					'last_name'       => $last_name,
					'phone'           => $phone,
					'email'           => $email,
					'message'         => $message,
					'class_school_id' => $class_school_id,
					'school_id'       => $school_id,
				);

				$success = $wpdb->insert(WLSM_INQUIRIES, $data);

				$buffer = ob_get_clean();
				if (!empty($buffer)) {
					throw new Exception($buffer);
				}

				if (false === $success) {
					throw new Exception($wpdb->last_error);
				}

				$message = esc_html__('Your inquiry has been submitted successfully.', 'school-management-system');
				$reset   = true;

				$wpdb->query('COMMIT;');

				wp_send_json_success(array('message' => $message, 'reset' => $reset));
			} catch (Exception $exception) {
				$wpdb->query('ROLLBACK;');
				wp_send_json_error($exception->getMessage());
			}
		}
		wp_send_json_error($errors);
	}
}
