<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Notify.php';

class WLSM_Schedule {
	public static function notify_for_student_admission( $school_id, $session_id, $student_id, $password ) {
		WLSM_Notify::notify_for_student_admission(
			array(
				'school_id'  => $school_id,
				'session_id' => $session_id,
				'student_id' => $student_id,
				'password'   => $password,
			)
		);
	}

	public static function notify_for_invoice_generated( $school_id, $session_id, $invoice_id ) {
		WLSM_Notify::notify_for_invoice_generated(
			array(
				'school_id'  => $school_id,
				'session_id' => $session_id,
				'invoice_id' => $invoice_id,
			)
		);
	}

	public static function notify_for_online_fee_submission( $school_id, $session_id, $payment_id ) {
		WLSM_Notify::notify_for_online_fee_submission(
			array(
				'school_id'  => $school_id,
				'session_id' => $session_id,
				'payment_id' => $payment_id,
			)
		);
	}

	public static function notify_for_offline_fee_submission( $school_id, $session_id, $payment_id ) {
		WLSM_Notify::notify_for_offline_fee_submission(
			array(
				'school_id'  => $school_id,
				'session_id' => $session_id,
				'payment_id' => $payment_id,
			)
		);
	}
}
