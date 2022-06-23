<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';

class WLSM_Config {
	public static function current_session() {
		$user_id = get_current_user_id();

		$session = NULL;

		$default_session_id = get_option( 'wlsm_current_session' );

		// Get current session from user meta.
		if ( $user_id && ( $current_session = get_user_meta( $user_id, 'wlsm_current_session', true ) ) ) {
			$session = WLSM_M_Session::fetch_session( $current_session );
		}

		// Get current session from setting.
		if ( ! $session ) {
			$current_session = $default_session_id;
			$current_session = absint( $current_session );
			if ( $current_session ) {
				$session = WLSM_M_Session::fetch_session( $current_session );
			}
		}

		if ( ! $session ) {
			$data = array(
				'ID'         => '',
				'label'      => '',
				'start_date' => '',
				'end_date'   => '',
			);
		} else {
			$data = array(
				'ID'         => $session->ID,
				'label'      => $session->label,
				'start_date' => $session->start_date,
				'end_date'   => $session->end_date,
			);
		}

		$data['default_session_id'] = $default_session_id;
		$data['sessions']           = WLSM_M_Session::fetch_sessions();

		return $data;
	}

	public static function currency() {
		$currency = get_option( 'wlsm_currency' );

		if ( isset( WLSM_Helper::currency_symbols()[ $currency ] ) ) {
			return $currency;
		}

		return self::get_default_currency();
	}

	public static function currency_symbol() {
		$currency = get_option( 'wlsm_currency' );

		if ( isset( WLSM_Helper::currency_symbols()[ $currency ] ) ) {
			return WLSM_Helper::currency_symbols()[ $currency ];
		}

		return WLSM_Helper::currency_symbols()[self::get_default_currency()];
	}

	public static function sanitize_money( $money ) {
		return (float) number_format( max( (float) $money, 0 ), 2, '.', '' );
	}

	public static function get_money_text( $money ) {
		$money = number_format( (float) $money, 2, '.', '' );
		if ( 0.00 == $money ) {
			return '-';
		}
		return self::currency_symbol() . number_format( (float) $money, 2, '.', '' );
	}

	public static function date_format() {
		$date_format = get_option( 'wlsm_date_format' );
		if ( ! $date_format ) {
			$date_format = self::get_default_date_format();
		}

		return $date_format;
	}

	public static function get_date_text( $date ) {
		if ( $date ) {
			return date_format( date_create( $date ), self::date_format() );
		}
		return '';
	}

	public static function get_time_text( $time ) {
		if ( $time ) {
			return date_format( date_create( $time ), self::get_default_time_format() );
		}
		return '';
	}

	public static function get_note_text( $note ) {
		if ( $note ) {
			return stripcslashes( $note );
		}
		return '-';
	}

	public static function limit_string( $content, $number_of_characters = 100 ) {
		if ( strlen( $content ) > $number_of_characters ) {
			$position = strpos( $content, ' ', $number_of_characters );
			$dots = '...';
			return substr( $content, 0 , $position ) . $dots; 
		}

		return $content;
	}

	public static function get_default_currency() {
		return 'USD';
	}

	public static function get_default_date_format() {
		return 'd-m-Y';
	}

	public static function get_default_time_format() {
		return 'h:i a';
	}
}
