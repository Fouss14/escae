<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_Admin {
	public static function get_admin( $id ) {
		global $wpdb;
		$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID, a.ID as admin_id, s.ID as school_id, u.ID as user_id FROM ' . WLSM_STAFF . ' as sf
			JOIN ' . WLSM_ADMINS . ' as a ON a.staff_id = sf.ID
			JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = sf.school_id
			LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
			WHERE sf.ID = %d AND sf.role = "admin"', $id ) );
		return $staff;
	}

	public static function fetch_admin( $id ) {
		global $wpdb;
		$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID, u.ID as user_id, u.user_login as username, u.user_email as email, a.name as name, s.ID as school_id, s.label as school_name FROM ' . WLSM_STAFF . ' as sf
			JOIN ' . WLSM_ADMINS . ' as a ON a.staff_id = sf.ID
			JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = sf.school_id
			LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
			WHERE sf.ID = %d AND sf.role = "admin"', $id ) );
		return $staff;
	}

	public static function user_in_school( $school_id, $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID FROM ' . WLSM_STAFF . ' as sf JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = sf.school_id WHERE s.ID = %d AND sf.user_id = %d', $school_id, $user_id ) );
	}

	public static function staff_in_school( $school_id, $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID, sf.role FROM ' . WLSM_STAFF . ' as sf JOIN ' . WLSM_ADMINS . ' as a ON a.staff_id = sf.ID WHERE sf.school_id = %d AND sf.user_id = %d', $school_id, $user_id ) );
	}

	public static function get_name_text( $name ) {
		if ( $name ) {
			return stripcslashes( $name );
		}
		return '';
	}

	public static function get_assigned_by_text( $assigned_by_manager ) {
		if ( $assigned_by_manager ) {
			return esc_html__('Super Admin', 'school-management-system' );
		} else {
			return esc_html__('School Admin', 'school-management-system' );
		}
	}
}
