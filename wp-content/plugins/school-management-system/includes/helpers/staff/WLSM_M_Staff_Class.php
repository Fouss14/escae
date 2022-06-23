<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_Staff_Class {
	public static function get_sections_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_CLASSES );
	}

	public static function fetch_classes_query( $school_id, $session_id ) {
		$query = 'SELECT c.ID, c.label, COUNT(DISTINCT se.ID) as sections_count, COUNT(DISTINCT sr.ID) as students_count FROM ' . WLSM_CLASS_SCHOOL . ' as cs
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_SECTIONS . ' as se ON se.class_school_id = cs.ID
		LEFT OUTER JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.section_id = se.ID AND sr.session_id = ' . absint( $session_id ) . '
		WHERE cs.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_classes_query_group_by() {
		$group_by = 'GROUP BY c.ID';
		return $group_by;
	}

	public static function fetch_classes_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT c.ID) FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.school_id =' . absint( $school_id );
		return $query;
	}

	public static function get_class( $school_id, $class_id ) {
		global $wpdb;
		$class = $wpdb->get_row( $wpdb->prepare( 'SELECT cs.ID, cs.default_section_id FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.class_id = %d AND cs.school_id = %d', $class_id, $school_id ) );
		return $class;
	}

	public static function fetch_class( $school_id, $class_id ) {
		global $wpdb;
		$class = $wpdb->get_row( $wpdb->prepare( 'SELECT cs.ID, c.ID as class_id, c.label, cs.default_section_id FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.class_id = %d AND cs.school_id = %d', $class_id, $school_id ) );
		return $class;
	}

	public static function fetch_sections_query( $school_id, $session_id, $class_school_id ) {
		$query = 'SELECT se.ID, se.label, cs.class_id as class_id, cs.default_section_id, COUNT(sr.ID) as students_count FROM ' . WLSM_SECTIONS . ' as se
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id
		LEFT OUTER JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.section_id = se.ID AND sr.session_id = ' . absint( $session_id ) . '
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND se.class_school_id = ' . absint( $class_school_id );
		return $query;
	}

	public static function fetch_sections_query_group_by() {
		$group_by = 'GROUP BY se.ID';
		return $group_by;
	}

	public static function fetch_sections_query_count( $school_id, $class_school_id ) {
		$query = 'SELECT COUNT(DISTINCT se.ID) FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id WHERE cs.school_id = ' . absint( $school_id ) . ' AND se.class_school_id = ' . absint( $class_school_id );
		return $query;
	}

	public static function get_section( $school_id, $id, $class_school_id ) {
		global $wpdb;
		$section = $wpdb->get_row( $wpdb->prepare( 'SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.school_id = %d AND se.ID = %d AND se.class_school_id = %d', $school_id, $id, $class_school_id ) );
		return $section;
	}

	public static function fetch_section( $school_id, $id, $class_school_id ) {
		global $wpdb;
		$section = $wpdb->get_row( $wpdb->prepare( 'SELECT se.ID, se.label FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.school_id = %d AND se.ID = %d AND se.class_school_id = %d', $school_id, $id, $class_school_id ) );
		return $section;
	}

	public static function fetch_classes( $school_id ) {
		global $wpdb;
		$classes = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT(c.ID), c.label FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.school_id = %d ORDER BY c.ID ASC', $school_id ) );
		return $classes;
	}

	public static function fetch_classes_ids( $school_id ) {
		global $wpdb;
		$classes_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT DISTINCT(c.ID) FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.school_id = %d ORDER BY c.ID ASC', $school_id ) );
		return $classes_ids;
	}

	public static function fetch_sections( $class_school_id ) {
		global $wpdb;
		$sections = $wpdb->get_results( $wpdb->prepare( 'SELECT se.ID, se.label FROM ' . WLSM_SECTIONS . ' as se WHERE se.class_school_id = %d', $class_school_id ) );
		return $sections;
	}

	public static function get_class_students( $school_id, $session_id, $class_id ) {
		global $wpdb;
		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.enrollment_number, sr.roll_number, sr.phone, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND cs.class_id = %d AND sr.is_active = 1 GROUP BY sr.ID ORDER BY sr.roll_number ASC, sr.name ASC', $school_id, $session_id, $class_id ), OBJECT_K );
		return $students;
	}

	public static function get_section_students( $school_id, $session_id, $section_id ) {
		global $wpdb;
		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.enrollment_number, sr.roll_number, sr.phone, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND se.ID = %d AND sr.is_active = 1 GROUP BY sr.ID ORDER BY sr.roll_number ASC, sr.name ASC', $school_id, $session_id, $section_id ), OBJECT_K );
		return $students;
	}

	public static function get_notices_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_NOTICES );
	}

	public static function fetch_notice_query( $school_id ) {
		$query = 'SELECT n.ID, n.title, n.attachment, n.url, n.link_to, n.is_active, n.created_at, u.user_login as username FROM ' . WLSM_NOTICES . ' as n LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = n.added_by WHERE n.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_notice_query_group_by() {
		$group_by = 'GROUP BY n.ID';
		return $group_by;
	}

	public static function fetch_notice_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT n.ID) FROM ' . WLSM_NOTICES . ' as n LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = n.added_by WHERE n.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_notice( $school_id, $id ) {
		global $wpdb;
		$notice = $wpdb->get_row( $wpdb->prepare( 'SELECT n.ID, n.attachment FROM ' . WLSM_NOTICES . ' as n WHERE n.school_id = %d AND n.ID = %d', $school_id, $id ) );
		return $notice;
	}

	public static function fetch_notice( $school_id, $id ) {
		global $wpdb;
		$notice = $wpdb->get_row( $wpdb->prepare( 'SELECT n.ID, n.title, n.attachment, n.url, n.link_to, n.is_active FROM ' . WLSM_NOTICES . ' as n WHERE n.school_id = %d AND n.ID = %d', $school_id, $id ) );
		return $notice;
	}

	public static function get_school_notices( $school_id, $limit = '' ) {
		global $wpdb;
		$sql = 'SELECT n.ID, n.title, n.attachment, n.url, n.link_to, n.is_active, n.created_at FROM ' . WLSM_NOTICES . ' as n WHERE n.school_id = %d AND n.is_active = 1 GROUP BY n.ID ORDER BY n.ID DESC';
		if ( $limit ) {
			$sql .= ( ' LIMIT ' . absint( $limit ) );
		}
		$notices = $wpdb->get_results( $wpdb->prepare( $sql, $school_id ) );
		return $notices;
	}

	public static function get_subjects_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_SUBJECTS );
	}

	public static function fetch_subject_query( $school_id ) {
		$query = 'SELECT sj.ID, sj.label as subject_name, sj.code, sj.type, c.label as class_label, COUNT(DISTINCT asj.ID) as admins_count FROM ' . WLSM_SUBJECTS . ' as sj
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_ADMIN_SUBJECT . ' as asj ON asj.subject_id = sj.ID
		WHERE cs.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_subject_query_group_by() {
		$group_by = 'GROUP BY sj.ID';
		return $group_by;
	}

	public static function fetch_subject_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT sj.ID) FROM ' . WLSM_SUBJECTS . ' as sj
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE cs.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_subject( $school_id, $id ) {
		global $wpdb;
		$subject = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID FROM ' . WLSM_SUBJECTS . ' as sj
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
			WHERE cs.school_id = %d AND sj.ID = %d', $school_id, $id ) );
		return $subject;
	}

	public static function fetch_subject( $school_id, $id ) {
		global $wpdb;
		$subject = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID, sj.label as subject_name, sj.code, sj.type, cs.class_id, c.label as class_label FROM ' . WLSM_SUBJECTS . ' as sj
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			WHERE cs.school_id = %d AND sj.ID = %d', $school_id, $id ) );
		return $subject;
	}

	public static function fetch_subject_admins_query( $school_id, $subject_id ) {
		$query = 'SELECT a.ID, a.name, a.phone, a.is_active, u.user_login as username FROM ' . WLSM_ADMIN_SUBJECT . ' as asj
		JOIN ' . WLSM_SUBJECTS . ' as sj ON sj.ID = asj.subject_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
		JOIN ' . WLSM_ADMINS . ' as a ON a.ID = asj.admin_id
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.school_id = ' . absint( $school_id ) . ' AND sj.ID = ' . absint( $subject_id );
		return $query;
	}

	public static function fetch_subject_admins_query_count( $school_id, $subject_id ) {
		$query = 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMIN_SUBJECT . ' as asj
		JOIN ' . WLSM_SUBJECTS . ' as sj ON sj.ID = asj.subject_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
		JOIN ' . WLSM_ADMINS . ' as a ON a.ID = asj.admin_id
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.school_id = ' . absint( $school_id ) . ' AND sj.ID = ' . absint( $subject_id );
		return $query;
	}

	public static function get_admin_subject( $school_id, $subject_id, $admin_id ) {
		global $wpdb;
		$admin = $wpdb->get_row( $wpdb->prepare( 'SELECT asj.ID FROM ' . WLSM_ADMIN_SUBJECT . ' as asj
		JOIN ' . WLSM_SUBJECTS . ' as sj ON sj.ID = asj.subject_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id
		JOIN ' . WLSM_ADMINS . ' as a ON a.ID = asj.admin_id
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		WHERE sf.school_id = %d AND sj.ID = %d AND a.ID = %d', $school_id, $subject_id, $admin_id ) );
		return $admin;
	}

	public static function get_keyword_active_admins( $school_id, $keyword ) {
		global $wpdb;
		$admins = $wpdb->get_results( $wpdb->prepare( 'SELECT a.ID, a.name as label, a.phone, u.user_login as username FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
			WHERE sf.school_id = %d AND a.is_active = 1 AND a.name LIKE "%%%s%%"', $school_id, $wpdb->esc_like( $keyword ) ) );
		return $admins;
	}

	public static function get_active_admins_ids_in_school( $school_id, $admin_ids ) {
		global $wpdb;

		$values        = array( $school_id );
		$place_holders = array();

		foreach ( $admin_ids as $admin_id ) {
			array_push( $values, $admin_id );
			array_push( $place_holders, '%d' );
		}

		$admin_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT a.ID FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
			WHERE sf.school_id = %d AND a.is_active = 1 AND a.ID IN(' . implode( ', ', $place_holders ) . ')', $values ) );

		return $admin_ids;
	}

	public static function fetch_active_students_of_classes( $school_id, $session_id, $class_ids ) {
		global $wpdb;

		$values        = array( $school_id, $session_id );
		$place_holders = array();

		foreach ( $class_ids as $class_id ) {
			array_push( $values, $class_id );
			array_push( $place_holders, '%d' );
		}

		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.enrollment_number, sr.name, sr.phone, sr.email, c.label as class_label, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			WHERE cs.school_id = %d AND sr.session_id = %d AND sr.is_active = 1 AND c.ID IN(' . implode( ', ', $place_holders ) . ') AND GROUP BY sr.ID ORDER BY sr.name', $values ) );
		return $students;
	}

	public static function get_section_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}

	public static function get_subject_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}

	public static function get_subject_code_text( $code ) {
		if ( $code ) {
			return $code;
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

	public static function get_link_to_text( $link_to ) {
		if ( 'attachment' === $link_to ) {
			return self::get_attachment_text();
		} else if ( 'url' === $link_to ) {
			return self::get_url_text();
		}
		return self::get_none_text();
	}

	public static function get_none_text() {
		return esc_html__('None', 'school-management-system' );
	}

	public static function get_attachment_text() {
		return esc_html__('Attachment', 'school-management-system' );
	}

	public static function get_url_text() {
		return esc_html__('URL', 'school-management-system' );
	}

	public static function get_subject_type_text( $subject_type ) {
		if ( isset( WLSM_Helper::subject_type_list()[ $subject_type ] ) ) {
			return WLSM_Helper::subject_type_list()[ $subject_type ];
		}
		return '-';
	}

	public static function get_name_text( $name ) {
		if ( $name ) {
			return stripcslashes( $name );
		}
		return '-';
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

	public static function get_username_text( $username ) {
		if ( $username ) {
			return $username;
		}
		return '-';
	}

	public static function get_admission_no_text( $admission_number ) {
		if ( $admission_number ) {
			return $admission_number;
		}
		return '-';
	}

	public static function get_roll_no_text( $roll_number ) {
		if ( $roll_number ) {
			return $roll_number;
		}
		return '-';
	}

	public static function get_designation_text( $designation ) {
		if ( $designation ) {
			return $designation;
		}
		return '-';
	}

	public static function get_default_section_text() {
		return esc_html__( 'Default', 'school-management-system' );
	}
}
