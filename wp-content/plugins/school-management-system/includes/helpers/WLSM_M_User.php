<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_User {
	public static function user_is_student( $user_id ) {
		global $wpdb;
		$student = $wpdb->get_row(
			$wpdb->prepare( 'SELECT sr.ID, sr.session_id, s.ID as school_id, cs.ID as class_school_id FROM ' . WLSM_STUDENT_RECORDS . ' as sr 
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id 
				JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
				WHERE sr.is_active = 1 AND sr.user_id = %d', $user_id )
		);
		return $student;
	}

	public static function get_username_text( $username ) {
		if ( $username ) {
			return stripcslashes( $username );
		}
		return '';
	}

	public static function get_email_text( $email ) {
		if ( $email ) {
			return $email;
		}
		return '';
	}
}
