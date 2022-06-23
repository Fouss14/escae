<?php
defined( 'ABSPATH' ) || die();

$searchBy = $filter['search_by'];
$where    = '';

if ( 'search_by_class' === $searchBy ) {
	$class_id   = $filter['class_id'];
	$section_id = $filter['section_id'];

	if ( $class_id ) {
		$where .= ' AND cs.class_id = ' . absint( $class_id );
		if ( $section_id ) {
			$where .= ' AND se.ID = ' . absint( $section_id );
		}
	}

} else {
	$search_field   = $filter['search_field'];
	$search_keyword = $filter['search_keyword'];

	if ( $search_field ) {
		if ( 'invoice_number' == $search_field ) {
			$where .= ' AND i.invoice_number LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'invoice_title' == $search_field ) {
			$where .= ' AND i.label LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'status' == $search_field ) {
			$search_keyword = strtolower( $search_keyword );
			if ( preg_match( '/^paid$/', $search_keyword ) ) {
				$status = WLSM_M_Invoice::get_paid_key();
			} else if ( preg_match( '/^unpa(|i|id)$/', $search_keyword ) ) {
				$status = WLSM_M_Invoice::get_unpaid_key();
			} else if ( preg_match( '/^partially(| p| pa| pai| paid)$/', $search_keyword ) ) {
				$status = WLSM_M_Invoice::get_partially_paid_key();
			}

			if ( isset( $status ) ) {
				$where .= ' AND (i.status = "' . $status . '")';
			}
		} else if ( 'admission_number' == $search_field ) {
			$where .= ' AND sr.admission_number LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'enrollment_number' == $search_field ) {
			$where .= ' AND sr.enrollment_number LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'name' == $search_field ) {
			$where .= ' AND sr.name LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'phone' == $search_field ) {
			$where .= ' AND sr.phone LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'email' == $search_field ) {
			$where .= ' AND sr.email LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'father_name' == $search_field ) {
			$where .= ' AND sr.father_name LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'father_phone' == $search_field ) {
			$where .= ' AND sr.father_phone LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'date_issued' == $search_field ) {
			require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
			$search_keyword = DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $search_keyword ) );
			if ( ! empty( $search_keyword ) ) {
				$search_keyword = $search_keyword->format( 'Y-m-d' );
			}
			$where .= ' AND i.date_issued LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		} else if ( 'due_date' == $search_field ) {
			require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
			$search_keyword = DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $search_keyword ) );
			if ( ! empty( $search_keyword ) ) {
				$search_keyword = $search_keyword->format( 'Y-m-d' );
			}
			$where .= ' AND i.due_date LIKE "%' . sanitize_text_field( $search_keyword ) . '%"';
		}
	}
}
