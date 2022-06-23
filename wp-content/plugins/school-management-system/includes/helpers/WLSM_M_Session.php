<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

class WLSM_M_Session {
	public static function get_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_SESSIONS );
	}

	public static function fetch_query() {
		$query = 'SELECT ss.ID, ss.label, ss.start_date, ss.end_date FROM ' . WLSM_SESSIONS . ' as ss';
		return $query;
	}

	public static function fetch_query_group_by() {
		$group_by = 'GROUP BY ss.ID';
		return $group_by;
	}

	public static function fetch_query_count() {
		$query = 'SELECT COUNT(ss.ID) FROM ' . WLSM_SESSIONS . ' as ss';
		return $query;
	}

	public static function get_session( $id ) {
		global $wpdb;
		$session = $wpdb->get_row( $wpdb->prepare( 'SELECT ss.ID FROM ' . WLSM_SESSIONS . ' as ss WHERE ss.ID = %d', $id ) );
		return $session;
	}

	public static function get_session_label( $id ) {
		global $wpdb;
		$session = $wpdb->get_var( $wpdb->prepare( 'SELECT ss.label FROM ' . WLSM_SESSIONS . ' as ss WHERE ss.ID = %d', $id ) );
		return $session;
	}

	public static function fetch_session( $id ) {
		global $wpdb;
		$session = $wpdb->get_row( $wpdb->prepare( 'SELECT ss.ID, ss.label, ss.start_date, ss.end_date FROM ' . WLSM_SESSIONS . ' as ss WHERE ss.ID = %d', $id ) );
		return $session;
	}

	public static function fetch_sessions() {
		global $wpdb;
		$sessions = $wpdb->get_results( 'SELECT ss.ID, ss.label FROM ' . WLSM_SESSIONS . ' as ss ORDER BY ss.label ASC, ss.ID ASC' );
		return $sessions;
	}

	public static function get_next_sessions( $current_session_id ) {
		global $wpdb;
		$current_session = $wpdb->get_row( $wpdb->prepare( 'SELECT ss.end_date FROM ' . WLSM_SESSIONS . ' as ss 
			WHERE ID = %d', $current_session_id ) );

		$next_sessions = $wpdb->get_results( $wpdb->prepare( 'SELECT ss.ID, ss.label FROM ' . WLSM_SESSIONS . ' as ss 
			WHERE start_date > %s', $current_session->end_date ) );

		return $next_sessions;
	}

	public static function get_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}
}
