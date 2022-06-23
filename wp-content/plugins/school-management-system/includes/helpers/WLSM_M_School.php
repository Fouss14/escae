<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_School {
	public static function get_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_SM );
	}

	public static function get_school( $id ) {
		global $wpdb;
		$school = $wpdb->get_row( $wpdb->prepare( 'SELECT s.ID FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $id ) );
		return $school;
	}

	public static function get_school_except( $id, $skip, $only_active = true ) {
		global $wpdb;

		if ( $only_active ) {
			$where = ' AND s.is_active = 1';
		} else {
			$where = '';
		}

		$school = $wpdb->get_row( $wpdb->prepare( 'SELECT s.ID, s.label FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d AND s.ID != %d' . $where, $id, $skip ) );
		return $school;
	}

	public static function fetch_school( $id ) {
		global $wpdb;
		$school = $wpdb->get_row( $wpdb->prepare( 'SELECT s.ID, s.label, s.phone, s.email, s.address, s.is_active FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $id ) );
		return $school;
	}

	public static function fetch_classes_query( $school_id ) {
		$query = 'SELECT c.ID, c.label FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id AND cs.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_classes_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT c.ID) FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id AND cs.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_class_school( $class_id, $school_id ) {
		global $wpdb;
		$class_school = $wpdb->get_row( $wpdb->prepare( 'SELECT cs.ID, c.label FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id WHERE c.ID = %d AND s.ID = %d', $class_id, $school_id ) );
		return $class_school;
	}

	public static function fetch_school_label( $id ) {
		global $wpdb;
		$school = $wpdb->get_row( $wpdb->prepare( 'SELECT s.ID, s.label FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $id ) );
		return $school;
	}

	public static function get_keyword_classes( $keyword = '' ) {
		global $wpdb;
		$classes = $wpdb->get_results( $wpdb->prepare( 'SELECT c.ID, c.label FROM ' . WLSM_CLASSES . ' as c WHERE c.label LIKE "%%%s%%"', $wpdb->esc_like( $keyword ) ) );
		return $classes;
	}

	public static function fetch_admins_query( $school_id ) {
		$query = 'SELECT sf.ID, a.name, a.assigned_by_manager, u.user_login as username, u.user_email as email FROM ' . WLSM_STAFF . ' as sf
		JOIN ' . WLSM_ADMINS . ' as a ON a.staff_id = sf.ID
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.school_id = ' . absint( $school_id ) . ' AND sf.role = "admin"';
		return $query;
	}

	public static function fetch_admins_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT sf.ID) FROM ' . WLSM_STAFF . ' as sf
		JOIN ' . WLSM_ADMINS . ' as a ON a.staff_id = sf.ID
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.school_id = ' . absint( $school_id ) . ' AND sf.role = "admin"';
		return $query;
	}

	public static function create_default_sections( $school_id ) {
		global $wpdb;

		// Get school classes.
		$classes = $wpdb->get_results( $wpdb->prepare( 'SELECT cs.ID, cs.default_section_id FROM ' . WLSM_CLASS_SCHOOL . ' as cs WHERE cs.school_id = %d', $school_id ) );

		foreach ( $classes as $class ) {
			$default_section_id = NULL;

			// Get class sections.
			$section_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se WHERE se.class_school_id = %d', $class->ID ) );

			if ( count( $section_ids ) ) {

				// Check if existing sections has no default section.
				if ( ! in_array( $class->default_section_id, $section_ids ) ) {
					// Assign default section from existing sections.
					$default_section_id = $section_ids[0];
				}

			} else {
				// Create a default section for class.
				$section_data = array(
					'class_school_id' => $class->ID,
					'label'           => 'A',
				);
				$success = $wpdb->insert( WLSM_SECTIONS, $section_data );

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$default_section_id = $wpdb->insert_id;
			}

			if ( $default_section_id ) {
				$success = $wpdb->update(
					WLSM_CLASS_SCHOOL,
					array( 'default_section_id' => $default_section_id, 'updated_at' => date( 'Y-m-d H:i:s' ) ),
					array( 'ID' => $class->ID )
				);

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}
			}

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}
		}
	}

	public static function get_schools() {
		global $wpdb;
		$schools = $wpdb->get_results( 'SELECT s.ID, s.label FROM ' . WLSM_SCHOOLS . ' as s' );
		return $schools;
	}

	public static function get_active_schools() {
		global $wpdb;
		$schools = $wpdb->get_results( 'SELECT s.ID, s.label FROM ' . WLSM_SCHOOLS . ' as s WHERE s.is_active = 1' );
		return $schools;
	}

	public static function get_active_school( $id ) {
		global $wpdb;
		$school = $wpdb->get_row( $wpdb->prepare( 'SELECT s.ID, s.label FROM ' . WLSM_SCHOOLS . ' as s WHERE s.is_active = 1 AND s.ID = %d', $id ) );
		return $school;
	}

	public static function get_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}

	public static function get_phone_text( $phone ) {
		if ( $phone ) {
			return $phone;
		}
		return '-';
	}

	public static function get_email_text( $email ) {
		if ( $email ) {
			return $email;
		}
		return '-';
	}

	public static function get_address_text( $address ) {
		if ( $address ) {
			return $address;
		}
		return '-';
	}

	public static function get_status_text( $is_active ) {
		if ( $is_active ) {
			return self::get_active_text();
		}
		return self::get_inactive_text();
	}

	public static function get_active_text() {
		return esc_html__('Active', 'school-management-system' );
	}

	public static function get_inactive_text() {
		return esc_html__('Inactive', 'school-management-system' );
	}
}
