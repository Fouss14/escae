<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_Staff_General {
	public static function fetch_class_sections( $class_school_id ) {
		global $wpdb;
		$sections = $wpdb->get_results( $wpdb->prepare( 'SELECT se.ID, se.label FROM ' . WLSM_SECTIONS . ' as se
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE se.class_school_id = %d', $class_school_id ) );
		return $sections;
	}

	public static function fetch_section_students( $session_id, $section_id, $only_active = true ) {
		global $wpdb;

		if ( $only_active ) {
			$where = ' AND sr.is_active = 1';
		} else {
			$where = '';
		}

		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.enrollment_number FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		WHERE sr.session_id = %d AND sr.section_id = %d' . $where . ' GROUP BY sr.ID', $session_id, $section_id ) );

		return $students;
	}

	public static function fetch_class_students( $session_id, $class_school_id, $only_active = true ) {
		global $wpdb;

		if ( $only_active ) {
			$where = ' AND sr.is_active = 1';
		} else {
			$where = '';
		}

		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.enrollment_number FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		WHERE sr.session_id = %d AND se.class_school_id = %d' . $where . ' GROUP BY sr.ID', $session_id, $class_school_id ) );

		return $students;
	}

	public static function fetch_school_classes( $school_id ) {
		global $wpdb;
		$classes = $wpdb->get_results( $wpdb->prepare( 'SELECT c.ID, c.label FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id AND cs.school_id = %d ORDER BY c.ID ASC', $school_id ) );
		return $classes;
	}

	public static function get_admitted_student_id( $school_id, $session_id, $admission_number, $skip_id = NULL ) {
		global $wpdb;

		if ( $skip_id ) {
			$skip_id = ' AND sr.ID != ' . absint( $skip_id );
		}

		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = %d AND sr.session_id = %d AND sr.admission_number = %s' . $skip_id, $school_id, $session_id, $admission_number ) );
		return $student;
	}

	public static function get_student_with_roll_number( $school_id, $session_id, $class_id, $roll_number, $skip_id = NULL ) {
		global $wpdb;

		if ( $skip_id ) {
			$skip_id = ' AND sr.ID != ' . absint( $skip_id );
		}

		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = %d AND sr.session_id = %d AND cs.class_id = %d AND sr.roll_number = %s' . $skip_id . ' GROUP BY sr.ID', $school_id, $session_id, $class_id, $roll_number ) );
		return $student;
	}

	public static function get_students_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_STUDENTS );
	}

	public static function fetch_students_query( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_students_query.php';

		$query = 'SELECT sr.ID, sr.name as student_name, sr.phone, sr.photo_id, sr.email, sr.father_name, sr.father_phone, sr.admission_number, sr.enrollment_number, sr.admission_date, c.label as class_label, se.label as section_label, sr.roll_number, sr.is_active, u.user_email as login_email, u.user_login as username FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function fetch_students_query_group_by() {
		$group_by = 'GROUP BY sr.ID';
		return $group_by;
	}

	public static function fetch_students_query_count( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_students_query.php';

		$query = 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function get_student( $school_id, $session_id, $id, $only_active = false ) {
		global $wpdb;

		if ( $only_active ) {
			$where = ' AND sr.is_active = 1';
		} else {
			$where = '';
		}

		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID, sr.photo_id, sr.user_id, se.class_school_id, cs.class_id FROM ' . WLSM_STUDENT_RECORDS . ' as sr JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d AND sr.session_id = %d AND sr.ID = %d' . $where, $school_id, $session_id, $id ) );

		return $student;
	}

	public static function fetch_student( $school_id, $session_id, $id ) {
		global $wpdb;
		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID, sr.name as student_name, sr.gender, sr.phone, sr.email, sr.address, sr.religion, sr.caste, sr.blood_group, sr.dob, sr.father_name, sr.father_phone, sr.father_occupation, sr.mother_name, sr.mother_phone, sr.mother_occupation, sr.admission_number, sr.enrollment_number, sr.admission_date, sr.photo_id, c.ID as class_id, c.label as class_label, se.ID as section_id, se.label as section_label, se.class_school_id, sr.roll_number, sr.is_active, ss.label as session_label, u.user_email as login_email, u.user_login as username FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id
		WHERE cs.school_id = %d AND ss.ID = %d AND sr.ID = %d', $school_id, $session_id, $id ) );
		return $student;
	}

	public static function get_student_record( $school_id, $id ) {
		global $wpdb;
		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID, sr.name as student_name, sr.enrollment_number, sr.roll_number, c.label as class_label, se.ID as section_id, se.label as section_label, ss.label as session_label, u.user_email as login_email, u.user_login as username FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id
		WHERE cs.school_id = %d AND sr.ID = %d', $school_id, $id ) );
		return $student;
	}

	public static function get_students_count( $school_id, $session_id, $ids, $only_active = false ) {
		global $wpdb;

		if ( $only_active ) {
			$where = ' AND sr.is_active = 1';
		} else {
			$where = '';
		}

		$ids_count = count( $ids );

		$place_holders = array_fill( 0, $ids_count, '%s' );

		$ids_format = implode( ', ', $place_holders );

		$prepare = array_merge( array( $school_id, $session_id ), $ids );

		$students_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d AND sr.session_id = %d AND sr.ID IN (' . $ids_format . ')' . $where, $prepare ) );

		return $students_count;
	}

	public static function get_admins_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ADMINS );
	}

	public static function get_employees_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EMPLOYEES );
	}

	public static function fetch_staff_query( $school_id, $role ) {
		$query = 'SELECT a.ID, a.name, a.phone, a.email, a.salary, a.designation, a.joining_date, a.assigned_by_manager, a.is_active, r.name as role_name, u.user_email as login_email, u.user_login as username FROM ' . WLSM_ADMINS . ' as a
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_ROLES . ' as r ON r.ID = a.role_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.role = "' . sanitize_text_field( $role ) . '" AND sf.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_staff_query_group_by() {
		$group_by = 'GROUP BY a.ID';
		return $group_by;
	}

	public static function fetch_staff_query_count( $school_id, $role ) {
		$query = 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMINS . ' as a
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_ROLES . ' as r ON r.ID = a.role_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.role = "' . sanitize_text_field( $role ) . '" AND sf.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_staff( $school_id, $role, $id ) {
		global $wpdb;
		$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT a.ID, a.staff_id, sf.user_id, a.assigned_by_manager FROM ' . WLSM_ADMINS . ' as a
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.role = "%s" AND sf.school_id = %d AND a.ID = %d', $role, $school_id, $id ) );
		return $staff;
	}

	public static function fetch_staff( $school_id, $role, $id ) {
		global $wpdb;
		$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT a.ID, a.name, a.gender, a.dob, a.phone, a.email, a.address, a.salary, a.designation, a.joining_date, a.role_id, a.assigned_by_manager, a.is_active, sf.role, sf.permissions, u.user_email as login_email, u.user_login as username FROM ' . WLSM_ADMINS . ' as a
		JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
		LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sf.user_id
		WHERE sf.role = "%s" AND sf.school_id = %d AND a.ID = %d', $role, $school_id, $id ) );
		return $staff;
	}

	public static function get_roles_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ROLES );
	}

	public static function fetch_role_query( $school_id ) {
		$query = 'SELECT r.ID, r.name FROM ' . WLSM_ROLES . ' as r
		WHERE r.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_role_query_group_by() {
		$group_by = 'GROUP BY r.ID';
		return $group_by;
	}

	public static function fetch_role_query_count( $school_id ) {
		$query = 'SELECT COUNT(r.ID) FROM ' . WLSM_ROLES . ' as r
		WHERE r.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_role( $school_id, $id ) {
		global $wpdb;
		$role = $wpdb->get_row( $wpdb->prepare( 'SELECT r.ID FROM ' . WLSM_ROLES . ' as r
		WHERE r.school_id = %d AND r.ID = %d', $school_id, $id ) );
		return $role;
	}

	public static function fetch_role( $school_id, $id ) {
		global $wpdb;
		$role = $wpdb->get_row( $wpdb->prepare( 'SELECT r.ID, r.name, r.permissions FROM ' . WLSM_ROLES . ' as r
		WHERE r.school_id = %d AND r.ID = %d', $school_id, $id ) );
		return $role;
	}

	public static function fetch_inquiry_query( $school_id ) {
		$query = 'SELECT iq.ID, iq.name, iq.phone, iq.email, iq.message, iq.note, iq.created_at, iq.next_follow_up, iq.is_active, c.label as class_label FROM ' . WLSM_INQUIRIES . ' as iq
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = iq.school_id
		LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = iq.class_school_id
		LEFT OUTER JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE iq.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_inquiry_query_group_by() {
		$group_by = 'GROUP BY iq.ID';
		return $group_by;
	}

	public static function fetch_inquiry_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT iq.ID) FROM ' . WLSM_INQUIRIES . ' as iq
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = iq.school_id
		LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = iq.class_school_id
		LEFT OUTER JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE iq.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_inquiry( $school_id, $id ) {
		global $wpdb;
		$inquiry = $wpdb->get_row( $wpdb->prepare( 'SELECT iq.ID FROM ' . WLSM_INQUIRIES . ' as iq
		WHERE iq.school_id = %d AND iq.ID = %d', $school_id, $id ) );
		return $inquiry;
	}

	public static function fetch_inquiry( $school_id, $id ) {
		global $wpdb;
		$inquiry = $wpdb->get_row( $wpdb->prepare( 'SELECT iq.ID, iq.name, iq.phone, iq.email, iq.message, iq.note, iq.next_follow_up, iq.is_active, c.ID as class_id FROM ' . WLSM_INQUIRIES . ' as iq
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = iq.school_id
		LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = iq.class_school_id
		LEFT OUTER JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE iq.school_id = %d AND iq.ID = %d', $school_id, $id ) );
		return $inquiry;
	}

	public static function get_inquiry_message( $school_id, $id ) {
		global $wpdb;
		$inquiry = $wpdb->get_row( $wpdb->prepare( 'SELECT iq.ID, iq.message FROM ' . WLSM_INQUIRIES . ' as iq WHERE iq.school_id = %d AND iq.ID = %d', $school_id, $id ) );
		return $inquiry;
	}

	public static function get_class_school( $school_id, $class_id ) {
		global $wpdb;
		$class_school = $wpdb->get_row( $wpdb->prepare( 'SELECT cs.ID, cs.default_section_id, c.label FROM ' . WLSM_CLASS_SCHOOL . ' as cs JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.school_id = %d AND cs.class_id = %d', $school_id, $class_id ) );
		return $class_school;
	}

	public static function get_class_students( $school_id, $session_id, $class_id ) {
		global $wpdb;
		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.phone, sr.roll_number, sr.enrollment_number, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			LEFT OUTER JOIN ' . WLSM_PROMOTIONS . ' as pm ON pm.from_student_record = sr.ID
			WHERE cs.school_id = %d AND ss.ID = %d AND cs.class_id = %d AND sr.is_active = 1 AND pm.ID IS NULL GROUP BY sr.ID ORDER BY sr.name', $school_id, $session_id, $class_id ) );
		return $students;
	}

	public static function get_class_students_data( $school_id, $session_id, $class_id ) {
		global $wpdb;
		$students = $wpdb->get_results( $wpdb->prepare( 'SELECT sr.* FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			LEFT OUTER JOIN ' . WLSM_PROMOTIONS . ' as pm ON pm.from_student_record = sr.ID
			WHERE cs.school_id = %d AND ss.ID = %d AND cs.class_id = %d AND sr.is_active = 1 AND pm.ID IS NULL GROUP BY sr.ID ORDER BY sr.name', $school_id, $session_id, $class_id ), OBJECT_K );
		return $students;
	}

	public static function is_next_session( $current_session_id, $new_session_id ) {
		global $wpdb;

		$current_session = $wpdb->get_row( $wpdb->prepare( 'SELECT ss.ID, ss.end_date FROM ' . WLSM_SESSIONS . ' as ss
			WHERE ID = %d', $current_session_id ) );

		$new_session = $wpdb->get_row( $wpdb->prepare( 'SELECT ss.ID, ss.start_date FROM ' . WLSM_SESSIONS . ' as ss
			WHERE ID = %d', $new_session_id ) );

		if ( $current_session->end_date > $new_session->start_date ) {
			return false;
		}

		return true;
	}

	public static function get_enrollment_number( $school_id ) {
		global $wpdb;

		$last_enrollment_count = $wpdb->get_var(
			$wpdb->prepare( 'SELECT last_enrollment_count FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $school_id )
		);

		$new_enrollment_count = absint( $last_enrollment_count ) + 1;

		$data = array(
			'last_enrollment_count' => $new_enrollment_count,
		);

		// Enrollment number formatting.
		$enrollment_number = str_pad( $new_enrollment_count, 6, '0', STR_PAD_LEFT );

		$success = $wpdb->update( WLSM_SCHOOLS, $data, array( 'ID' => $school_id ) );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			throw new Exception( $buffer );
		}

		if ( false === $success ) {
			throw new Exception( $wpdb->last_error );
		}

		return $enrollment_number;
	}

	public static function get_active_student( $student_id ) {
		global $wpdb;
		$student = $wpdb->get_row(
			$wpdb->prepare( 'SELECT sr.ID, sr.name as student_name, sr.father_name, sr.enrollment_number, c.label as class_label, se.label as section_label, sr.roll_number FROM ' . WLSM_STUDENT_RECORDS . ' as sr
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				WHERE sr.is_active = 1 AND sr.ID = %d', $student_id )
		);
		return $student;
	}

	public static function get_inquiries_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_INQUIRIES );
	}

	public static function get_inquiry_status_text( $is_active ) {
		if ( $is_active ) {
			return self::get_inquiry_active_text();
		}
		return self::get_inquiry_inactive_text();
	}

	public static function get_inquiry_active_text() {
		return esc_html__('Active', 'school-management-system' );
	}

	public static function get_inquiry_inactive_text() {
		return esc_html__('Inactive', 'school-management-system' );
	}
}
