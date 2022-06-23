<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

class WLSM_Email {
	public static function email_carriers() {
		return array(
			'wp_mail' => esc_html__( 'WP Mail', 'school-management-system' ),
			'smtp'    => esc_html__( 'SMTP', 'school-management-system' ),
		);
	}

	public static function send_email( $school_id, $to, $subject, $body, $name = '', $email_for = '', $placeholders = array() ) {
		if ( ! empty( $email_for ) && count( $placeholders ) ) {
			if ( 'student_admission' === $email_for ) {
				$available_placeholders = array_keys( self::student_admission_placeholders() );
			} else if ( 'invoice_generated' === $email_for ) {
				$available_placeholders = array_keys( self::invoice_generated_placeholders() );
			} else if ( 'online_fee_submission' === $email_for ) {
				$available_placeholders = array_keys( self::online_fee_submission_placeholders() );
			} else if ( 'offline_fee_submission' === $email_for ) {
				$available_placeholders = array_keys( self::offline_fee_submission_placeholders() );
			}

			if ( isset( $available_placeholders ) ) {
				foreach ( $placeholders as $key => $value ) {
					if ( in_array( $key, $available_placeholders ) ) {
						$subject = str_replace( $key, $value, $subject );
						$body    = str_replace( $key, $value, $body );
					}
				}
			}
		}

		$settings_email = WLSM_M_Setting::get_settings_email( $school_id );
		$email_carrier  = $settings_email['carrier'];

		if ( 'wp_mail' === $email_carrier ) {
			$wp_mail    = WLSM_M_Setting::get_settings_wp_mail( $school_id );
			$from_name  = $wp_mail['from_name'];
			$from_email = $wp_mail['from_email'];

			if ( is_array( $to ) ) {
				foreach ( $to as $key => $value ) {
					$to[ $key ]	= $name[ $key ] . ' <' . $value . '>';
				}
			} else {
				if ( ! empty( $name ) ) {
					$to = "$name <$to>";
				}
			}

			$headers = array();
			array_push( $headers, 'Content-Type: text/html; charset=UTF-8' );
			if ( ! empty( $from_name ) ) {
				array_push( $headers, "From: $from_name <$from_email>" );
			}

			$status = wp_mail( $to, html_entity_decode( $subject ), $body, $headers, array() );
			return $status;

		} elseif ( 'smtp' === $email_carrier ) {
			$smtp       = WLSM_M_Setting::get_settings_smtp( $school_id );
			$from_name  = $smtp['from_name'];
			$host       = $smtp['host'];
			$username   = $smtp['username'];
			$password   = $smtp['password'];
			$encryption = $smtp['encryption'];
			$port       = $smtp['port'];

			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

			$mail = new PHPMailer\PHPMailer\PHPMailer(true);

			try {
				$mail->CharSet  = 'UTF-8';
				$mail->Encoding = 'base64';

				if ( $host && $port ) {
					$mail->IsSMTP();
					$mail->Host = $host;
					if ( ! empty( $username ) && ! empty( $password ) ) {
						$mail->SMTPAuth = true;
						$mail->Password = $password;
					} else {
						$mail->SMTPAuth = false;
					}
					if ( ! empty( $encryption ) ) {
						$mail->SMTPSecure = $encryption;
					} else {
						$mail->SMTPSecure = NULL;
					}
					$mail->Port = $port;
				}

				$mail->Username = $username;

				$mail->setFrom( $mail->Username, $from_name );

				$mail->Subject = html_entity_decode( $subject );
				$mail->Body    = $body;

				$mail->IsHTML( true );

				if ( is_array( $to ) ) {
					foreach ( $to as $key => $value ) {
						$mail->AddAddress( $value, $name[ $key ] );
					}
				} else {
					$mail->AddAddress( $to, $name );
				}

				$status = $mail->Send();
				return $status;

			} catch( Exception $e ) {
			}

			return false;
		}
	}

	public static function student_admission_placeholders() {
		return array(
			'[STUDENT_NAME]'      => esc_html__( 'Student Name', 'school-management-system' ),
			'[CLASS]'             => esc_html__( 'Class', 'school-management-system' ),
			'[SECTION]'           => esc_html__( 'Section', 'school-management-system' ),
			'[ROLL_NUMBER]'       => esc_html__( 'Roll Number', 'school-management-system' ),
			'[ENROLLMENT_NUMBER]' => esc_html__( 'Enrollment Number', 'school-management-system' ),
			'[ADMISSION_NUMBER]'  => esc_html__( 'Admission Number', 'school-management-system' ),
			'[LOGIN_USERNAME]'    => esc_html__( 'Login Username', 'school-management-system' ),
			'[LOGIN_EMAIL]'       => esc_html__( 'Login Email Number', 'school-management-system' ),
			'[LOGIN_PASSWORD]'    => esc_html__( 'Login Password', 'school-management-system' ),
			'[SCHOOL_NAME]'       => esc_html__( 'School Name', 'school-management-system' ),
		);
	}

	public static function invoice_generated_placeholders() {
		return array(
			'[INVOICE_TITLE]'       => esc_html__( 'Invoice Title', 'school-management-system' ),
			'[INVOICE_NUMBER]'      => esc_html__( 'Invoice Number', 'school-management-system' ),
			'[INVOICE_PAYABLE]'     => esc_html__( 'Invoice Payable', 'school-management-system' ),
			'[INVOICE_DATE_ISSUED]' => esc_html__( 'Invoice Date Issued', 'school-management-system' ),
			'[INVOICE_DUE_DATE]'    => esc_html__( 'Invoice Due Date', 'school-management-system' ),
			'[STUDENT_NAME]'        => esc_html__( 'Student Name', 'school-management-system' ),
			'[CLASS]'               => esc_html__( 'Class', 'school-management-system' ),
			'[SECTION]'             => esc_html__( 'Section', 'school-management-system' ),
			'[ROLL_NUMBER]'         => esc_html__( 'Roll Number', 'school-management-system' ),
			'[ENROLLMENT_NUMBER]'   => esc_html__( 'Enrollment Number', 'school-management-system' ),
			'[ADMISSION_NUMBER]'    => esc_html__( 'Admission Number', 'school-management-system' ),
			'[SCHOOL_NAME]'         => esc_html__( 'School Name', 'school-management-system' ),
		);
	}

	public static function online_fee_submission_placeholders() {
		return array(
			'[INVOICE_TITLE]'     => esc_html__( 'Invoice Title', 'school-management-system' ),
			'[RECEIPT_NUMBER]'    => esc_html__( 'Receipt Number', 'school-management-system' ),
			'[AMOUNT]'            => esc_html__( 'AMOUNT', 'school-management-system' ),
			'[PAYMENT_METHOD]'    => esc_html__( 'Payment Method', 'school-management-system' ),
			'[DATE]'              => esc_html__( 'Date', 'school-management-system' ),
			'[STUDENT_NAME]'      => esc_html__( 'Student Name', 'school-management-system' ),
			'[CLASS]'             => esc_html__( 'Class', 'school-management-system' ),
			'[SECTION]'           => esc_html__( 'Section', 'school-management-system' ),
			'[ROLL_NUMBER]'       => esc_html__( 'Roll Number', 'school-management-system' ),
			'[ENROLLMENT_NUMBER]' => esc_html__( 'Enrollment Number', 'school-management-system' ),
			'[ADMISSION_NUMBER]'  => esc_html__( 'Admission Number', 'school-management-system' ),
			'[SCHOOL_NAME]'       => esc_html__( 'School Name', 'school-management-system' ),
		);
	}

	public static function offline_fee_submission_placeholders() {
		return array(
			'[INVOICE_TITLE]'     => esc_html__( 'Invoice Title', 'school-management-system' ),
			'[RECEIPT_NUMBER]'    => esc_html__( 'Receipt Number', 'school-management-system' ),
			'[AMOUNT]'            => esc_html__( 'AMOUNT', 'school-management-system' ),
			'[PAYMENT_METHOD]'    => esc_html__( 'Payment Method', 'school-management-system' ),
			'[DATE]'              => esc_html__( 'Date', 'school-management-system' ),
			'[STUDENT_NAME]'      => esc_html__( 'Student Name', 'school-management-system' ),
			'[CLASS]'             => esc_html__( 'Class', 'school-management-system' ),
			'[SECTION]'           => esc_html__( 'Section', 'school-management-system' ),
			'[ROLL_NUMBER]'       => esc_html__( 'Roll Number', 'school-management-system' ),
			'[ENROLLMENT_NUMBER]' => esc_html__( 'Enrollment Number', 'school-management-system' ),
			'[ADMISSION_NUMBER]'  => esc_html__( 'Admission Number', 'school-management-system' ),
			'[SCHOOL_NAME]'       => esc_html__( 'School Name', 'school-management-system' ),
		);
	}
}
