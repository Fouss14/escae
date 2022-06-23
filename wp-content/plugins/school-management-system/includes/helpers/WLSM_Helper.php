<?php
defined( 'ABSPATH' ) || die();

class WLSM_Helper {
	public static function currency_symbols() {
		return array(
			'AED' => '&#1583;.&#1573;',
			'AFN' => '&#65;&#102;',
			'ALL' => '&#76;&#101;&#107;',
			'ANG' => '&#402;',
			'AOA' => '&#75;&#122;',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&#402;',
			'AZN' => '&#1084;&#1072;&#1085;',
			'BAM' => '&#75;&#77;',
			'BBD' => '&#36;',
			'BDT' => '&#2547;',
			'BGN' => '&#1083;&#1074;',
			'BHD' => '.&#1583;.&#1576;',
			'BIF' => '&#70;&#66;&#117;',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => '&#36;&#98;',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTN' => '&#78;&#117;&#46;',
			'BWP' => '&#80;',
			'BYR' => '&#112;&#46;',
			'BZD' => '&#66;&#90;&#36;',
			'CAD' => '&#36;',
			'CDF' => '&#70;&#67;',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&#165;',
			'COP' => '&#36;',
			'CRC' => '&#8353;',
			'CUP' => '&#8396;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => '&#70;&#100;&#106;',
			'DKK' => '&#107;&#114;',
			'DOP' => '&#82;&#68;&#36;',
			'DZD' => '&#1583;&#1580;',
			'EGP' => '&#163;',
			'ETB' => '&#66;&#114;',
			'EUR' => '&#8364;',
			'FJD' => '&#36;',
			'FKP' => '&#163;',
			'GBP' => '&#163;',
			'GEL' => '&#4314;',
			'GHS' => '&#162;',
			'GIP' => '&#163;',
			'GMD' => '&#68;',
			'GNF' => '&#70;&#71;',
			'GTQ' => '&#81;',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => '&#76;',
			'HRK' => '&#107;&#110;',
			'HTG' => '&#71;',
			'HUF' => '&#70;&#116;',
			'IDR' => '&#82;&#112;',
			'ILS' => '&#8362;',
			'INR' => '&#8377;',
			'IQD' => '&#1593;.&#1583;',
			'IRR' => '&#65020;',
			'ISK' => '&#107;&#114;',
			'JEP' => '&#163;',
			'JMD' => '&#74;&#36;',
			'JOD' => '&#74;&#68;',
			'JPY' => '&#165;',
			'KES' => '&#75;&#83;&#104;',
			'KGS' => '&#1083;&#1074;',
			'KHR' => '&#6107;',
			'KMF' => '&#67;&#70;',
			'KPW' => '&#8361;',
			'KRW' => '&#8361;',
			'KWD' => '&#1583;.&#1603;',
			'KYD' => '&#36;',
			'KZT' => '&#1083;&#1074;',
			'LAK' => '&#8365;',
			'LBP' => '&#163;',
			'LKR' => '&#8360;',
			'LRD' => '&#36;',
			'LSL' => '&#76;',
			'LTL' => '&#76;&#116;',
			'LVL' => '&#76;&#115;',
			'LYD' => '&#1604;.&#1583;',
			'MAD' => '&#1583;.&#1605;.',
			'MDL' => '&#76;',
			'MGA' => '&#65;&#114;',
			'MKD' => '&#1076;&#1077;&#1085;',
			'MMK' => '&#75;',
			'MNT' => '&#8366;',
			'MOP' => '&#77;&#79;&#80;&#36;',
			'MRO' => '&#85;&#77;',
			'MUR' => '&#8360;',
			'MVR' => '.&#1923;',
			'MWK' => '&#77;&#75;',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => '&#77;&#84;',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => '&#67;&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#65020;',
			'PAB' => '&#66;&#47;&#46;',
			'PEN' => '&#83;&#47;&#46;',
			'PGK' => '&#75;',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PYG' => '&#71;&#115;',
			'QAR' => '&#65020;',
			'RON' => '&#108;&#101;&#105;',
			'RSD' => '&#1044;&#1080;&#1085;&#46;',
			'RUB' => '&#1088;&#1091;&#1073;',
			'RWF' => '&#1585;.&#1587;',
			'SAR' => '&#65020;',
			'SBD' => '&#36;',
			'SCR' => '&#8360;',
			'SDG' => '&#163;',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&#163;',
			'SLL' => '&#76;&#101;',
			'SOS' => '&#83;',
			'SRD' => '&#36;',
			'STD' => '&#68;&#98;',
			'SVC' => '&#36;',
			'SYP' => '&#163;',
			'SZL' => '&#76;',
			'THB' => '&#3647;',
			'TJS' => '&#84;&#74;&#83;',
			'TMT' => '&#109;',
			'TND' => '&#1583;.&#1578;',
			'TOP' => '&#84;&#36;',
			'TRY' => '&#8356;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => '',
			'UAH' => '&#8372;',
			'UGX' => '&#85;&#83;&#104;',
			'USD' => '&#36;',
			'UYU' => '&#36;&#85;',
			'UZS' => '&#1083;&#1074;',
			'VEF' => '&#66;&#115;',
			'VND' => '&#8363;',
			'VUV' => '&#86;&#84;',
			'WST' => '&#87;&#83;&#36;',
			'XAF' => '&#70;&#67;&#70;&#65;',
			'XCD' => '&#36;',
			'XDR' => '',
			'XOF' => '',
			'XPF' => '&#70;',
			'YER' => '&#65020;',
			'ZAR' => '&#82;',
			'ZMK' => '&#90;&#75;',
			'ZWL' => '&#90;&#36;'
		);
	}

	public static function date_formats() {
		return array(
			'd-m-Y' => 'dd-mm-yyyy',
			'd/m/Y' => 'dd/mm/yyyy',
			'Y-m-d' => 'yyyy-mm-dd',
			'Y/m/d' => 'yyyy/mm/dd',
			'm-d-Y' => 'mm-dd-yyyy',
			'm/d/Y' => 'mm/dd/yyyy',
		);
	}

	public static function gender_list() {
		return array(
			'male'   => esc_html__( 'Male', 'school-management-system' ),
			'female' => esc_html__( 'Female', 'school-management-system' ),
		);
	}

	public static function blood_group_list() {
		return array(
			'O+'  => esc_html__( 'O+', 'school-management-system' ),
			'A+'  => esc_html__( 'A+', 'school-management-system' ),
			'B+'  => esc_html__( 'B+', 'school-management-system' ),
			'AB+' => esc_html__( 'AB+', 'school-management-system' ),
			'O-'  => esc_html__( 'O-', 'school-management-system' ),
			'A-'  => esc_html__( 'A-', 'school-management-system' ),
			'B-'  => esc_html__( 'B-', 'school-management-system' ),
			'AB-' => esc_html__( 'AB-', 'school-management-system' ),
		);
	}

	public static function search_field_list() {
		return array(
			'admission_number'  => esc_html__( 'Admission Number', 'school-management-system' ),
			'name'              => esc_html__( 'Name', 'school-management-system' ),
			'phone'             => esc_html__( 'Phone', 'school-management-system' ),
			'email'             => esc_html__( 'Email', 'school-management-system' ),
			'father_name'       => esc_html__( 'Father Name', 'school-management-system' ),
			'father_phone'      => esc_html__( 'Father Phone', 'school-management-system' ),
			'login_email'       => esc_html__( 'Login Email', 'school-management-system' ),
			'username'          => esc_html__( 'Login Username', 'school-management-system' ),
			'admission_date'    => esc_html__( 'Admission Date', 'school-management-system' ),
			'enrollment_number' => esc_html__( 'Enrollment Number', 'school-management-system' ),
		);
	}

	public static function invoice_search_field_list() {
		return array(
			'invoice_number'    => esc_html__( 'Invoice Number', 'school-management-system' ),
			'invoice_title'     => esc_html__( 'Invoice Title', 'school-management-system' ),
			'date_issued'       => esc_html__( 'Date Issued', 'school-management-system' ),
			'due_date'          => esc_html__( 'Due Date', 'school-management-system' ),
			'status'            => esc_html__( 'Status (Paid, Unpaid, Partially Paid)', 'school-management-system' ),
			'name'              => esc_html__( 'Student Name', 'school-management-system' ),
			'admission_number'  => esc_html__( 'Admission Number', 'school-management-system' ),
			'enrollment_number' => esc_html__( 'Enrollment Number', 'school-management-system' ),
			'phone'             => esc_html__( 'Phone', 'school-management-system' ),
			'email'             => esc_html__( 'Email', 'school-management-system' ),
			'father_name'       => esc_html__( 'Father Name', 'school-management-system' ),
			'father_phone'      => esc_html__( 'Father Phone', 'school-management-system' ),
		);
	}

	public static function subject_type_list() {
		return array(
			'theory'     => esc_html__( 'Theory', 'school-management-system' ),
			'practical'  => esc_html__( 'Practical', 'school-management-system' ),
			'subjective' => esc_html__( 'Subjective', 'school-management-system' ),
			'objective'  => esc_html__( 'Objective', 'school-management-system' ),
		);
	}

	public static function fee_period_list() {
		return array(
			'one-time' => esc_html__( 'One Time', 'school-management-system' ),
			'monthly'  => esc_html__( 'Monthly', 'school-management-system' ),
			'annually' => esc_html__( 'Annually', 'school-management-system' ),
		);
	}

	public static function get_image_mime() {
		return array( 'image/jpg', 'image/jpeg', 'image/png' );
	}

	public static function get_attachment_mime() {
		return array( 'image/jpg', 'image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-rar-compressed', 'application/octet-stream', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed', 'multipart/x-zip', 'video/x-flv', 'video/mp4', 'application/x-mpegURL', 'video/MP2T', 'video/3gpp', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv' );
	}
}
