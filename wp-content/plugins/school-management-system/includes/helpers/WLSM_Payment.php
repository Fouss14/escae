<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

class WLSM_Payment {
	public static function currency_supports_stripe( $currency ) {
		if ( in_array( $currency, self::get_stripe_supported_currencies() ) ) {
			return true;
		}

		return false;
	}

	public static function get_stripe_supported_currencies() {
		return array( 'AFN', 'ALL', 'DZD', 'AOA', 'ARS', 'AMD', 'AWG', 'AUD', 'AZN', 'BSD', 'BDT', 'BBD', 'BZD', 'BMD', 'BOB', 'BAM', 'BWP', 'BRL', 'GBP', 'BND', 'BGN', 'BIF', 'KHR', 'CAD', 'CVE', 'KYD', 'XAF', 'XPF', 'CLP', 'CNY', 'COP', 'KMF', 'CDF', 'CRC', 'HRK', 'CZK', 'DKK', 'DJF', 'DOP', 'XCD', 'EGP', 'ETB', 'EUR', 'FKP', 'FJD', 'GMD', 'GEL', 'GIP', 'GTQ', 'GNF', 'GYD', 'HTG', 'HNL', 'HKD', 'HUF', 'ISK', 'INR', 'IDR', 'ILS', 'JMD', 'JPY', 'KZT', 'KES', 'KGS', 'LAK', 'LBP', 'LSL', 'LRD', 'MOP', 'MKD', 'MGA', 'MWK', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MDL', 'MNT', 'MAD', 'MZN', 'MMK', 'NAD', 'NPR', 'ANG', 'TWD', 'NZD', 'NIO', 'NGN', 'NOK', 'PKR', 'PAB', 'PGK', 'PYG', 'PEN', 'PHP', 'PLN', 'QAR', 'RON', 'RUB', 'RWF', 'STD', 'SHP', 'SVC', 'WST', 'SAR', 'RSD', 'SCR', 'SLL', 'SGD', 'SBD', 'SOS', 'ZAR', 'KRW', 'LKR', 'SRD', 'SZL', 'SEK', 'CHF', 'TJS', 'TZS', 'THB', 'TOP', 'TTD', 'TRY', 'UGX', 'UAH', 'AED', 'USD', 'UYU', 'UZS', 'VUV', 'VND', 'XOF', 'YER', 'ZMW' );
	}
}
