<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_User.php';

class WLSM_P_Print {
	public static function student_print_id_card() {
		$user_id = get_current_user_id();

		if ( ! wp_verify_nonce( $_POST[ 'st-print-id-card-' . $user_id ], 'st-print-id-card-' . $user_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$student = WLSM_M_User::user_is_student( $user_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
			}

			$student_id = $student->ID;
			$school_id  = $student->school_id;
			$session_id = $student->session_id;

			// Checks if student exists.
			$student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		ob_start();
		$from_front = true;
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/id_card.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function student_print_payment() {
		$payment_id = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'st-print-invoice-payment-' . $payment_id ], 'st-print-invoice-payment-' . $payment_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$user_id = get_current_user_id();

			$student = WLSM_M_User::user_is_student( $user_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
			}

			$student_id = $student->ID;
			$school_id  = $student->school_id;
			$session_id = $student->session_id;

			// Checks if payment exists.
			$payment = WLSM_M_Staff_Accountant::get_student_payment( $student_id, $payment_id );

			if ( ! $payment ) {
				throw new Exception( esc_html__( 'Payment not found.', 'school-management-system' ) );
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		ob_start();
		$from_front = true;
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/payment.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
