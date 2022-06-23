<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Email.php';

class WLSM_Notify {
	public static function notify_for_student_admission( $data ) {
		$school_id = $data['school_id'];

		$settings_email_student_admission = WLSM_M_Setting::get_settings_email_student_admission( $school_id );
		$email_student_admission_enable   = $settings_email_student_admission['enable'];

		if ( $email_student_admission_enable ) {
			global $wpdb;
			$student = $wpdb->get_row(
				$wpdb->prepare( 'SELECT sr.name as student_name, sr.email, sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label, sr.roll_number, u.user_email as login_email, u.user_login as username, s.label as school_name FROM ' . WLSM_STUDENT_RECORDS . ' as sr 
					JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
					JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
					JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
					JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
					JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id 
					LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
					WHERE cs.school_id = %d AND ss.ID = %d AND sr.ID = %d', $school_id, $data['session_id'], $data['student_id'] )
			);

			if ( ! $student ) {
				return false;
			}

			$email_to = $student->email ? $student->email : $student->login_email;

			if ( ! $email_to ) {
				return false;
			}

			$for = 'student_admission';

			$name = stripcslashes( $student->student_name );

			$placeholders = array(
				'[STUDENT_NAME]'      => $name,
				'[CLASS]'             => stripcslashes( $student->class_label ),
				'[SECTION]'           => stripcslashes( $student->section_label ),
				'[ROLL_NUMBER]'       => $student->roll_number,
				'[ENROLLMENT_NUMBER]' => $student->enrollment_number,
				'[ADMISSION_NUMBER]'  => $student->admission_number,
				'[LOGIN_USERNAME]'    => $student->username,
				'[LOGIN_EMAIL]'       => $student->login_email,
				'[LOGIN_PASSWORD]'    => $data['password'],
				'[SCHOOL_NAME]'       => stripcslashes( $student->school_name ),
			);

			if ( $email_student_admission_enable && $email_to ) {
				// Student Admission Email template.
				$subject = $settings_email_student_admission['subject'];
				$body    = $settings_email_student_admission['body'];

				WLSM_Email::send_email( $data['school_id'], $email_to, $subject, $body, $name, $for, $placeholders );
			}
		}
	}

	public static function notify_for_invoice_generated( $data ) {
		$school_id = $data['school_id'];

		$settings_email_invoice_generated = WLSM_M_Setting::get_settings_email_invoice_generated( $school_id );
		$email_invoice_generated_enable   = $settings_email_invoice_generated['enable'];

		if ( $email_invoice_generated_enable ) {
			global $wpdb;
			$invoice = $wpdb->get_row(
				$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, (i.amount - i.discount) as payable, sr.name as student_name, sr.phone, sr.email, sr.admission_number, sr.enrollment_number, sr.roll_number, c.label as class_label, se.label as section_label, u.user_email as login_email, s.label as school_name FROM ' . WLSM_INVOICES . ' as i 
					JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
					JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
					JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
					JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
					JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
					JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id 
					LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
					LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
					WHERE cs.school_id = %d AND ss.ID = %d AND i.ID = %d', $school_id, $data['session_id'], $data['invoice_id'] )
			);

			if ( ! $invoice ) {
				return false;
			}

			$email_to = $invoice->email ? $invoice->email : $invoice->login_email;

			if ( ! $email_to ) {
				return false;
			}

			$for = 'invoice_generated';

			$name = stripcslashes( $invoice->student_name );

			$placeholders = array(
				'[INVOICE_TITLE]'       => $invoice->invoice_title,
				'[INVOICE_NUMBER]'      => $invoice->invoice_number,
				'[INVOICE_PAYABLE]'     => WLSM_Config::get_money_text( $invoice->payable ),
				'[INVOICE_DATE_ISSUED]' => WLSM_Config::get_date_text( $invoice->date_issued ),
				'[INVOICE_DUE_DATE]'    => WLSM_Config::get_date_text( $invoice->due_date ),
				'[STUDENT_NAME]'        => $name,
				'[CLASS]'               => stripcslashes( $invoice->class_label ),
				'[SECTION]'             => stripcslashes( $invoice->section_label ),
				'[ROLL_NUMBER]'         => $invoice->roll_number,
				'[ENROLLMENT_NUMBER]'   => $invoice->enrollment_number,
				'[ADMISSION_NUMBER]'    => $invoice->admission_number,
				'[SCHOOL_NAME]'         => stripcslashes( $invoice->school_name ),
			);

			if ( $email_invoice_generated_enable && $email_to ) {
				// Invoice Generated Email template.
				$subject = $settings_email_invoice_generated['subject'];
				$body    = $settings_email_invoice_generated['body'];

				WLSM_Email::send_email( $data['school_id'], $email_to, $subject, $body, $name, $for, $placeholders );
			}
		}
	}

	public static function notify_for_online_fee_submission( $data ) {
		$school_id = $data['school_id'];

		$settings_email_online_fee_submission = WLSM_M_Setting::get_settings_email_online_fee_submission( $school_id );
		$email_online_fee_submission_enable   = $settings_email_online_fee_submission['enable'];

		if ( $email_online_fee_submission_enable ) {
			global $wpdb;
			$payment = $wpdb->get_row(
				$wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.phone, sr.email, p.receipt_number, p.amount, p.payment_method, p.created_at, p.invoice_label, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label, u.user_email as login_email, s.label as school_name FROM ' . WLSM_PAYMENTS . ' as p 
					JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
					JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
					JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
					JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
					JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
					JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
					LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
					LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
					WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $data['session_id'], $data['payment_id'] )
			);

			if ( ! $payment ) {
				return false;
			}

			$email_to = $payment->email ? $payment->email : $payment->login_email;

			if ( ! $email_to ) {
				return false;
			}

			$for = 'online_fee_submission';

			$name = stripcslashes( $payment->student_name );

			$placeholders = array(
				'[INVOICE_TITLE]'       => $payment->invoice_title ? $payment->invoice_title : $payment->invoice_label,
				'[RECEIPT_NUMBER]'      => $payment->receipt_number,
				'[AMOUNT]'              => WLSM_Config::get_money_text( $payment->amount ),
				'[PAYMENT_METHOD]'      => WLSM_M_Invoice::get_payment_method_text( $payment->payment_method ),
				'[DATE]'                => WLSM_Config::get_date_text( $payment->created_at ),
				'[STUDENT_NAME]'        => $name,
				'[CLASS]'               => stripcslashes( $payment->class_label ),
				'[SECTION]'             => stripcslashes( $payment->section_label ),
				'[ROLL_NUMBER]'         => $payment->roll_number,
				'[ENROLLMENT_NUMBER]'   => $payment->enrollment_number,
				'[ADMISSION_NUMBER]'    => $payment->admission_number,
				'[SCHOOL_NAME]'         => stripcslashes( $payment->school_name ),
			);

			if ( $email_online_fee_submission_enable && $email_to ) {
				// Online Fee Submission Email template.
				$subject = $settings_email_online_fee_submission['subject'];
				$body    = $settings_email_online_fee_submission['body'];

				WLSM_Email::send_email( $data['school_id'], $email_to, $subject, $body, $name, $for, $placeholders );
			}
		}
	}

	public static function notify_for_offline_fee_submission( $data ) {
		$school_id = $data['school_id'];

		$settings_email_offline_fee_submission = WLSM_M_Setting::get_settings_email_offline_fee_submission( $school_id );
		$email_offline_fee_submission_enable   = $settings_email_offline_fee_submission['enable'];

		if ( $email_offline_fee_submission_enable ) {
			global $wpdb;
			$payment = $wpdb->get_row(
				$wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.phone, sr.email, p.receipt_number, p.amount, p.payment_method, p.created_at, p.invoice_label, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label, u.user_email as login_email, s.label as school_name FROM ' . WLSM_PAYMENTS . ' as p 
					JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
					JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
					JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
					JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
					JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
					JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
					LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
					LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
					WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $data['session_id'], $data['payment_id'] )
			);

			if ( ! $payment ) {
				return false;
			}

			$email_to = $payment->email ? $payment->email : $payment->login_email;

			if ( ! $email_to ) {
				return false;
			}

			$for = 'offline_fee_submission';

			$name = stripcslashes( $payment->student_name );

			$placeholders = array(
				'[INVOICE_TITLE]'       => $payment->invoice_title ? $payment->invoice_title : $payment->invoice_label,
				'[RECEIPT_NUMBER]'      => $payment->receipt_number,
				'[AMOUNT]'              => WLSM_Config::get_money_text( $payment->amount ),
				'[PAYMENT_METHOD]'      => WLSM_M_Invoice::get_payment_method_text( $payment->payment_method ),
				'[DATE]'                => WLSM_Config::get_date_text( $payment->created_at ),
				'[STUDENT_NAME]'        => $name,
				'[CLASS]'               => stripcslashes( $payment->class_label ),
				'[SECTION]'             => stripcslashes( $payment->section_label ),
				'[ROLL_NUMBER]'         => $payment->roll_number,
				'[ENROLLMENT_NUMBER]'   => $payment->enrollment_number,
				'[ADMISSION_NUMBER]'    => $payment->admission_number,
				'[SCHOOL_NAME]'         => stripcslashes( $payment->school_name ),
			);

			if ( $email_offline_fee_submission_enable && $email_to ) {
				// Offline Fee Submission Email template.
				$subject = $settings_email_offline_fee_submission['subject'];
				$body    = $settings_email_offline_fee_submission['body'];

				WLSM_Email::send_email( $data['school_id'], $email_to, $subject, $body, $name, $for, $placeholders );
			}
		}
	}
}
