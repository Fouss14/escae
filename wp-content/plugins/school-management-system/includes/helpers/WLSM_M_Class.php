<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_Class {
	public static function get_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_CLASSES );
	}

	public static function fetch_query() {
		$query = 'SELECT c.ID, c.label FROM ' . WLSM_CLASSES . ' as c';
		return $query;
	}

	public static function fetch_query_group_by() {
		$group_by = 'GROUP BY c.ID';
		return $group_by;
	}

	public static function fetch_query_count() {
		$query = 'SELECT COUNT(c.ID) FROM ' . WLSM_CLASSES . ' as c';
		return $query;
	}

	public static function get_class( $id ) {
		global $wpdb;
		$class = $wpdb->get_row( $wpdb->prepare( 'SELECT c.ID FROM ' . WLSM_CLASSES . ' as c WHERE c.ID = %d', $id ) );
		return $class;
	}

	public static function fetch_class( $id ) {
		global $wpdb;
		$class = $wpdb->get_row( $wpdb->prepare( 'SELECT c.ID, c.label FROM ' . WLSM_CLASSES . ' as c WHERE c.ID = %d', $id ) );
		return $class;
	}

	public static function get_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}
}
