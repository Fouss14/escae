<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Payment.php';

class WLSM_M_Invoice {
	private static $paid           = 'paid';
	private static $unpaid         = 'unpaid';
	private static $partially_paid = 'partially_paid';

	public static function get_status() {
		return array(
			self::$paid           => esc_html__( 'Paid', 'school-management-system' ),
			self::$unpaid         => esc_html__( 'Unpaid', 'school-management-system' ),
			self::$partially_paid => esc_html__( 'Partially Paid', 'school-management-system' ),
		);
	}

	public static function get_due_amount( $data ) {
		$due = $data['total'] - $data['discount'];
		return WLSM_Config::sanitize_money( $due );
	}

	public static function get_invoice_number( $school_id ) {
		global $wpdb;

		$last_invoice_count = $wpdb->get_var(
			$wpdb->prepare( 'SELECT last_invoice_count FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $school_id )
		);

		$new_invoice_count = absint( $last_invoice_count ) + 1;

		$data = array(
			'last_invoice_count' => $new_invoice_count,
		);

		// Invoice number formatting.
		$invoice_number = str_pad( $new_invoice_count, 5 , '0', STR_PAD_LEFT );

		$success = $wpdb->update( WLSM_SCHOOLS, $data, array( 'ID' => $school_id ) );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			throw new Exception( $buffer );
		}

		if ( false === $success ) {
			throw new Exception( $wpdb->last_error );
		}

		return $invoice_number;
	}

	public static function get_receipt_number( $school_id ) {
		global $wpdb;

		$last_payment_count = $wpdb->get_var(
			$wpdb->prepare( 'SELECT last_payment_count FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $school_id )
		);

		$new_payment_count = absint( $last_payment_count ) + 1;

		$data = array(
			'last_payment_count' => $new_payment_count,
		);

		// Receipt number formatting.
		$payment_number = str_pad( $new_payment_count, 6 , '0', STR_PAD_LEFT );

		$success = $wpdb->update( WLSM_SCHOOLS, $data, array( 'ID' => $school_id ) );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			throw new Exception( $buffer );
		}

		if ( false === $success ) {
			throw new Exception( $wpdb->last_error );
		}

		return $payment_number;
	}

	public static function get_status_key( $payable, $paid ) {
		$due = $payable - $paid;
		if ( $due <= 0 ) {
			return self::get_paid_key();
		} else if ( $due == $payable ) {
			return self::get_unpaid_key();
		} else {
			return self::get_partially_paid_key();
		}
	}

	public static function get_status_text( $status ) {
		if ( array_key_exists( $status, self::get_status() ) ) {
			$status_text = self::get_status()[ $status ];
			if ( self::$paid == $status ) {
				$status_text = '<span class="text-success wlsm-font-bold">' . esc_html( $status_text ) . '</span>';
			} else if ( self::$unpaid == $status ) {
				$status_text = '<span class="text-danger wlsm-font-bold">' . esc_html( $status_text ) . '</span>';
			} else {
				$status_text = '<span class="text-primary wlsm-font-bold">' . esc_html( $status_text ) . '</span>';
			}

			return $status_text;
		}

		return '';
	}

	public static function get_paid_key() {
		return self::$paid;
	}

	public static function get_unpaid_key() {
		return self::$unpaid;
	}

	public static function get_partially_paid_key() {
		return self::$partially_paid;
	}

	public static function get_paid_text() {
		return self::get_status()[ self::$paid ];
	}

	public static function get_unpaid_text() {
		return self::get_status()[ self::$unpaid ];
	}

	public static function get_partially_paid_text() {
		return self::get_status()[ self::$partially_paid ];
	}

	public static function collect_payment_methods() {
		return array(
			'cash'          => esc_html__( 'Cash', 'school-management-system' ),
			'cheque'        => esc_html__( 'Cheque', 'school-management-system' ),
			'Card'          => esc_html__( 'Card', 'school-management-system' ),
			'bank-transfer' => esc_html__( 'Bank Transfer', 'school-management-system' ),
			'demand-draft'  => esc_html__( 'Demand Draft', 'school-management-system' ),
		);
	}

	public static function get_payment_method_text( $key ) {
		$all_payment_methods = array(
			'cash'          => esc_html__( 'Cash', 'school-management-system' ),
			'cheque'        => esc_html__( 'Cheque', 'school-management-system' ),
			'Card'          => esc_html__( 'Card', 'school-management-system' ),
			'bank-transfer' => esc_html__( 'Bank Transfer', 'school-management-system' ),
			'demand-draft'  => esc_html__( 'Demand Draft', 'school-management-system' ),
			'stripe'        => esc_html__( 'Stripe', 'school-management-system' ),
		);

		if ( array_key_exists( $key, $all_payment_methods ) ) {
			return $all_payment_methods[ $key ];
		}

		return '';
	}

	public static function get_receipt_number_text( $receipt_number ) {
		if ( $receipt_number ) {
			return $receipt_number;
		}
		return '-';
	}

	public static function get_transaction_id_text( $transaction_id ) {
		if ( $transaction_id ) {
			return $transaction_id;
		}
		return '-';
	}
}
