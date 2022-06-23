<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';

class WLSM_M_Staff_Accountant {
	public static function get_invoices_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_INVOICES );
	}

	public static function fetch_invoices_query( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount - i.discount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount - i.discount) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function fetch_invoices_query_group_by() {
		$group_by = 'GROUP BY i.ID';
		return $group_by;
	}

	public static function fetch_invoices_query_count( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function get_invoice( $school_id, $session_id, $id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.status FROM ' . WLSM_INVOICES . ' as i 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		WHERE cs.school_id = %d AND ss.ID = %d AND i.ID = %d', $school_id, $session_id, $id ) );
		return $invoice;
	}

	public static function fetch_invoice( $school_id, $session_id, $id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.description as invoice_description, i.date_issued, i.due_date, i.amount, i.discount, (i.amount - i.discount) as payable, COALESCE(SUM(p.amount), 0) as paid, i.partial_payment, i.status, sr.ID as student_id, sr.name as student_name, sr.phone, sr.email, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.father_name, sr.father_phone, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
		WHERE cs.school_id = %d AND ss.ID = %d AND i.ID = %d', $school_id, $session_id, $id ) );
		return $invoice;
	}

	public static function get_invoice_payments( $invoice_id ) {
		global $wpdb;
		$payments = $wpdb->get_results( $wpdb->prepare( 'SELECT p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note FROM ' . WLSM_PAYMENTS . ' as p 
		WHERE p.invoice_id = %d ORDER BY p.ID DESC', $invoice_id ) );
		return $payments;
	}

	public static function fetch_invoice_payments_query( $school_id, $session_id, $invoice_id ) {
		$query = 'SELECT p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . ' AND p.invoice_id = ' . absint( $invoice_id );
		return $query;
	}

	public static function fetch_payments_query_group_by() {
		$group_by = 'GROUP BY p.ID';
		return $group_by;
	}

	public static function fetch_invoice_payments_query_count( $school_id, $session_id, $invoice_id ) {
		$query = 'SELECT COUNT(DISTINCT p.ID) FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . ' AND p.invoice_id = ' . absint( $invoice_id );
		return $query;
	}

	public static function get_invoice_payments_total( $invoice_id ) {
		global $wpdb;
		$total = $wpdb->get_var(
			$wpdb->prepare( 'SELECT COALESCE(SUM(p.amount), 0) as paid FROM ' . WLSM_PAYMENTS . ' as p 
				JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
				WHERE i.ID = %d GROUP BY i.ID', $invoice_id )
		);
		return $total;
	}

	public static function get_invoice_payment( $invoice_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID FROM ' . WLSM_PAYMENTS . ' as p 
		WHERE p.invoice_id = %d AND p.ID = %d', $invoice_id, $id ) );
		return $payment;
	}

	public static function get_invoice_by_id( $invoice_id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.amount, i.discount FROM ' . WLSM_INVOICES . ' as i WHERE i.ID = %d', $invoice_id ) );
		return $invoice;
	}

	public static function fetch_payments_query( $school_id, $session_id ) {
		$query = 'SELECT sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_payments_query_count( $school_id, $session_id ) {
		$query = 'SELECT COUNT(DISTINCT p.ID) FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function get_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID, p.invoice_id FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function get_payment_note( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID, p.note FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function calculate_payable_amount( $invoice ) {
		return $invoice->amount - $invoice->discount;
	}

	public static function refresh_invoice_status( $invoice_id ) {
		global $wpdb;

		ob_start();

		$invoice = self::get_invoice_by_id( $invoice_id );

		$paid    = self::get_invoice_payments_total( $invoice_id );

		$payable = self::calculate_payable_amount( $invoice );

		$invoice_status = WLSM_M_Invoice::get_status_key( $payable, $paid );

		$data = array(
			'status'     => $invoice_status,
			'updated_at' => date( 'Y-m-d H:i:s' ),
		);

		$success = $wpdb->update( WLSM_INVOICES, $data, array( 'ID' => $invoice_id ) );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			throw new Exception( $buffer );
		}

		if ( false === $success ) {
			throw new Exception( $wpdb->last_error );
		}

		return $invoice_status;
	}

	public static function get_student_pending_invoices( $student_id ) {
		global $wpdb;
		$invoices = $wpdb->get_results(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount - i.discount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount - i.discount) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i 
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
				WHERE sr.ID = %d AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID ORDER BY i.ID DESC', $student_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key() )
		);
		return $invoices;
	}

	public static function get_student_pending_invoice( $invoice_id ) {
		global $wpdb;
		$invoice = $wpdb->get_row(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount - i.discount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount - i.discount) - COALESCE(SUM(p.amount), 0)) as due, i.status, i.partial_payment, i.student_record_id as student_id, sr.name as student_name, sr.phone, sr.email, sr.address, sr.admission_number, sr.enrollment_number, sr.session_id, c.label as class_label, se.label as section_label, cs.school_id, u.user_email as login_email FROM ' . WLSM_INVOICES . ' as i 
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
				LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
				WHERE (i.status = "%s" OR i.status = "%s") AND i.ID = %d', WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key(), $invoice_id )
		);
		return $invoice;
	}

	public static function get_student_invoices( $student_id ) {
		global $wpdb;
		$invoices = $wpdb->get_results(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount - i.discount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount - i.discount) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i 
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
				WHERE sr.ID = %d GROUP BY i.ID ORDER BY i.ID DESC', $student_id )
		);
		return $invoices;
	}

	public static function get_student_payments( $student_id ) {
		global $wpdb;
		$payments = $wpdb->get_results(
			$wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p 
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
				WHERE sr.ID = %d GROUP BY p.ID ORDER BY p.ID DESC', $student_id )
		);
		return $payments;
	}

	public static function get_student_payment( $student_id, $payment_id ) {
		global $wpdb;
		$payment = $wpdb->get_row(
			$wpdb->prepare( 'SELECT sr.name as student_name, sr.roll_number, sr.admission_number, sr.enrollment_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p 
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
				WHERE sr.ID = %d AND p.ID = %d', $student_id, $payment_id )
		);
		return $payment;
	}

	public static function get_total_payments_received( $school_id, $session_id ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(p.amount), 0) as sum FROM ' . WLSM_PAYMENTS . ' as p 
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		WHERE p.school_id = %d AND ss.ID = %d', $school_id, $session_id ) );
	}

	public static function get_invoice_title_text( $invoice_title ) {
		if ( $invoice_title ) {
			return $invoice_title;
		}
		return '-';
	}

	public static function get_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}

	public static function get_category_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '-';
	}

	public static function get_partial_payments_allowed_text( $invoice_partial_payment ) {
		if ( $invoice_partial_payment ) {
			return esc_html__( 'Yes', 'school-management-system' );
		}
		return esc_html__( 'No', 'school-management-system' );
	}

	public static function get_fee_period_text( $period ) {
		if ( isset( WLSM_Helper::fee_period_list()[ $period ] ) ) {
			return WLSM_Helper::fee_period_list()[ $period ];
		}
		return '-';
	}
}
