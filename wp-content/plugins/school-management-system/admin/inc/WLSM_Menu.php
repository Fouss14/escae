<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';

class WLSM_Menu {
	// Create menu pages.
	public static function create_menu() {
		$school_management = add_menu_page( esc_html__( 'School Management', 'school-management-system' ), esc_html__( 'School Management', 'school-management-system' ), WLSM_ADMIN_CAPABILITY, WLSM_MENU_SM, array( 'WLSM_Menu', 'school_management' ), 'dashicons-welcome-learn-more', 27 );
		add_action( 'admin_print_styles-' . $school_management, array( 'WLSM_Menu', 'menu_page_assets' ) );

		$dashboard_submenu = add_submenu_page( WLSM_MENU_SM, esc_html__( 'Dashboard', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), WLSM_ADMIN_CAPABILITY, WLSM_MENU_SM, array( 'WLSM_Menu', 'school_management' ) );
		add_action( 'admin_print_styles-' . $dashboard_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

		$classes_submenu = add_submenu_page( WLSM_MENU_SM, esc_html__( 'Classes', 'school-management-system' ), esc_html__( 'Classes', 'school-management-system' ), WLSM_ADMIN_CAPABILITY, WLSM_MENU_CLASSES, array( 'WLSM_Menu', 'classes' ) );
		add_action( 'admin_print_styles-' . $classes_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

		$sessions_submenu = add_submenu_page( WLSM_MENU_SM, esc_html__( 'Sessions', 'school-management-system' ), esc_html__( 'Sessions', 'school-management-system' ), WLSM_ADMIN_CAPABILITY, WLSM_MENU_SESSIONS, array( 'WLSM_Menu', 'sessions' ) );
		add_action( 'admin_print_styles-' . $sessions_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

		$settings_submenu = add_submenu_page( WLSM_MENU_SM, esc_html__( 'Settings', 'school-management-system' ), esc_html__( 'Settings', 'school-management-system' ), WLSM_ADMIN_CAPABILITY, WLSM_MENU_SETTINGS, array( 'WLSM_Menu', 'settings' ) );
		add_action( 'admin_print_styles-' . $settings_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

		require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';

		$user_info = WLSM_M_Role::get_user_info();

		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) && count( $user_info['schools_assigned'] ) > 1 ) {
			$schools_assigned = add_menu_page( esc_html__( 'School Management', 'school-management-system' ), esc_html__( 'School Management', 'school-management-system' ), 'read', WLSM_MENU_SCHOOLS_ASSIGNED, array( 'WLSM_Menu', 'schools_assigned' ), 'dashicons-welcome-learn-more', 27 );
			add_action( 'admin_print_styles-' . $schools_assigned, array( 'WLSM_Menu', 'menu_page_assets' ) );

			$schools_assigned_submenu = add_submenu_page( WLSM_MENU_SCHOOLS_ASSIGNED, esc_html__( 'Dashboard', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_SCHOOLS_ASSIGNED, array( 'WLSM_Menu', 'schools_assigned' ) );
			add_action( 'admin_print_styles-' . $schools_assigned_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
		}

		if ( $current_school = $user_info['current_school'] ) {
			$role = $current_school['role'];
			if ( in_array( $role, array_keys( WLSM_M_Role::get_roles() ) ) ) {

				$permissions = $current_school['permissions'];

				// School - Menu.
				$school_staff_school_menu = add_menu_page( esc_html__( 'School', 'school-management-system' ), esc_html__( 'SM School', 'school-management-system' ), 'read', WLSM_MENU_STAFF_SCHOOL, array( 'WLSM_Menu', 'school_staff_dashboard' ), 'dashicons-building', 31 );
				add_action( 'admin_print_styles-' . $school_staff_school_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

				// School - Dashboard.
				$school_staff_dashboard_submenu = add_submenu_page( WLSM_MENU_STAFF_SCHOOL, esc_html__( 'Dashboard', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_SCHOOL, array( 'WLSM_Menu', 'school_staff_dashboard' ) );
				add_action( 'admin_print_styles-' . $school_staff_dashboard_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

				// School - Inquiries.
				if ( WLSM_M_Role::check_permission( array( 'manage_inquiries' ), $permissions ) ) {
					$school_staff_inquiries_submenu = add_submenu_page( WLSM_MENU_STAFF_SCHOOL, esc_html__( 'Inquiries', 'school-management-system' ), esc_html__( 'Inquiries', 'school-management-system' ), 'read', WLSM_MENU_STAFF_INQUIRIES, array( 'WLSM_Menu', 'school_staff_inquiries' ) );
					add_action( 'admin_print_styles-' . $school_staff_inquiries_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
				}

				// School - Settings.
				if ( WLSM_M_Role::check_permission( array( 'manage_settings' ), $permissions ) ) {
					$school_staff_settings_submenu = add_submenu_page( WLSM_MENU_STAFF_SCHOOL, esc_html__( 'Settings', 'school-management-system' ), esc_html__( 'Settings', 'school-management-system' ), 'read', WLSM_MENU_STAFF_SETTINGS, array( 'WLSM_Menu', 'school_staff_settings' ) );
					add_action( 'admin_print_styles-' . $school_staff_settings_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
				}

				// Academic - Group.
				if ( WLSM_M_Role::check_permission( array( 'manage_classes', 'manage_subjects', 'manage_notices' ), $permissions ) ) {

					$school_staff_group_academic_menu = add_menu_page( esc_html__( 'Academic', 'school-management-system' ), esc_html__( 'SM Academic', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ACADEMIC, array( 'WLSM_Menu', 'school_staff_group_academic' ), 'dashicons-welcome-learn-more', 31 );
					add_action( 'admin_print_styles-' . $school_staff_group_academic_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					$school_staff_group_academic_menu = add_submenu_page( WLSM_MENU_STAFF_ACADEMIC, esc_html__( 'Academic', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ACADEMIC, array( 'WLSM_Menu', 'school_staff_group_academic' ) );
					add_action( 'admin_print_styles-' . $school_staff_group_academic_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $permissions ) ) {
						// Class - Classes & Sections.
						$school_staff_classes_submenu = add_submenu_page( WLSM_MENU_STAFF_ACADEMIC, esc_html__( 'Sections', 'school-management-system' ), esc_html__( 'Class Sections', 'school-management-system' ), 'read', WLSM_MENU_STAFF_CLASSES, array( 'WLSM_Menu', 'school_staff_classes' ) );
						add_action( 'admin_print_styles-' . $school_staff_classes_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_subjects' ), $permissions ) ) {
						// Class - Subjects.
						$school_staff_subjects_submenu = add_submenu_page( WLSM_MENU_STAFF_ACADEMIC, esc_html__( 'Subjects', 'school-management-system' ), esc_html__( 'Subjects', 'school-management-system' ), 'read', WLSM_MENU_STAFF_SUBJECTS, array( 'WLSM_Menu', 'school_staff_subjects' ) );
						add_action( 'admin_print_styles-' . $school_staff_subjects_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_notices' ), $permissions ) ) {
						// Class - Notices.
						$school_staff_notices_submenu = add_submenu_page( WLSM_MENU_STAFF_ACADEMIC, esc_html__( 'Noticeboard', 'school-management-system' ), esc_html__( 'Noticeboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_NOTICES, array( 'WLSM_Menu', 'school_staff_notices' ) );
						add_action( 'admin_print_styles-' . $school_staff_notices_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}
				}

				// Student - Group.
				if ( WLSM_M_Role::check_permission( array( 'manage_admissions', 'manage_students', 'manage_promote' ), $permissions ) ) {

					$school_staff_group_student_menu = add_menu_page( esc_html__( 'Student', 'school-management-system' ), esc_html__( 'SM Student', 'school-management-system' ), 'read', WLSM_MENU_STAFF_STUDENT, array( 'WLSM_Menu', 'school_staff_group_student' ), 'dashicons-groups', 32 );
					add_action( 'admin_print_styles-' . $school_staff_group_student_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					$school_staff_group_student_submenu = add_submenu_page( WLSM_MENU_STAFF_STUDENT, esc_html__( 'Student', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_STUDENT, array( 'WLSM_Menu', 'school_staff_group_student' ) );
					add_action( 'admin_print_styles-' . $school_staff_group_student_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $permissions ) ) {
						// School - Admissions.
						$school_staff_admissions_submenu = add_submenu_page( WLSM_MENU_STAFF_STUDENT, esc_html__( 'Admission', 'school-management-system' ), esc_html__( 'Admission', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ADMISSIONS, array( 'WLSM_Menu', 'school_staff_admissions' ) );
						add_action( 'admin_print_styles-' . $school_staff_admissions_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $permissions ) ) {
						// General - Students.
						$school_staff_students_submenu = add_submenu_page( WLSM_MENU_STAFF_STUDENT, esc_html__( 'Students', 'school-management-system' ), esc_html__( 'Students', 'school-management-system' ), 'read', WLSM_MENU_STAFF_STUDENTS, array( 'WLSM_Menu', 'school_staff_students' ) );
						add_action( 'admin_print_styles-' . $school_staff_students_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $permissions ) ) {
						// School - ID Cards.
						$school_staff_id_cards_submenu = add_submenu_page( WLSM_MENU_STAFF_STUDENT, esc_html__( 'ID Cards', 'school-management-system' ), esc_html__( 'ID Cards', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ID_CARDS, array( 'WLSM_Menu', 'school_staff_id_cards' ) );
						add_action( 'admin_print_styles-' . $school_staff_id_cards_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_promote' ), $permissions ) ) {
						// School - Promote.
						$school_staff_promote_submenu = add_submenu_page( WLSM_MENU_STAFF_STUDENT, esc_html__( 'Promote', 'school-management-system' ), esc_html__( 'Promote', 'school-management-system' ), 'read', WLSM_MENU_STAFF_PROMOTE, array( 'WLSM_Menu', 'school_staff_promote' ) );
						add_action( 'admin_print_styles-' . $school_staff_promote_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}
				}

				// Administrator - Group.
				if ( WLSM_M_Role::check_permission( array( 'manage_admins', 'manage_roles', 'manage_employees' ), $permissions ) ) {

					$school_staff_group_administrator_menu = add_menu_page( esc_html__( 'Administrator', 'school-management-system' ), esc_html__( 'SM Administrator', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ADMINISTRATOR, array( 'WLSM_Menu', 'school_staff_group_administrator' ), 'dashicons-businessman', 33 );
					add_action( 'admin_print_styles-' . $school_staff_group_administrator_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					$school_staff_group_administrator_submenu = add_submenu_page( WLSM_MENU_STAFF_ADMINISTRATOR, esc_html__( 'Administrator', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ADMINISTRATOR, array( 'WLSM_Menu', 'school_staff_group_administrator' ) );
					add_action( 'admin_print_styles-' . $school_staff_group_administrator_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					if ( WLSM_M_Role::check_permission( array( 'manage_roles' ), $permissions ) ) {
						// School - Roles.
						$school_staff_roles_submenu = add_submenu_page( WLSM_MENU_STAFF_ADMINISTRATOR, esc_html__( 'Roles', 'school-management-system' ), esc_html__( 'Roles', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ROLES, array( 'WLSM_Menu', 'school_staff_roles' ) );
						add_action( 'admin_print_styles-' . $school_staff_roles_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}

					if ( WLSM_M_Role::check_permission( array( 'manage_employees' ), $permissions ) ) {
						// School - Employees.
						$school_staff_employees_submenu = add_submenu_page( WLSM_MENU_STAFF_ADMINISTRATOR, esc_html__( 'Staff', 'school-management-system' ), esc_html__( 'Staff', 'school-management-system' ), 'read', WLSM_MENU_STAFF_EMPLOYEES, array( 'WLSM_Menu', 'school_staff_employees' ) );
						add_action( 'admin_print_styles-' . $school_staff_employees_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}
				}

				// Accounting - Group.
				if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $permissions ) ) {

					$school_staff_group_accounting_menu = add_menu_page( esc_html__( 'Accounting', 'school-management-system' ), esc_html__( 'SM Accounting', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ACCOUNTING, array( 'WLSM_Menu', 'school_staff_group_accounting' ), 'dashicons-media-spreadsheet', 33 );
					add_action( 'admin_print_styles-' . $school_staff_group_accounting_menu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					$school_staff_group_accounting_submenu = add_submenu_page( WLSM_MENU_STAFF_ACCOUNTING, esc_html__( 'Accounting', 'school-management-system' ), esc_html__( 'Dashboard', 'school-management-system' ), 'read', WLSM_MENU_STAFF_ACCOUNTING, array( 'WLSM_Menu', 'school_staff_group_accounting' ) );
					add_action( 'admin_print_styles-' . $school_staff_group_accounting_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );

					if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $permissions ) ) {
						// Accountant - Invoices.
						$school_staff_invoices_submenu = add_submenu_page( WLSM_MENU_STAFF_ACCOUNTING, esc_html__( 'Fee Invoices', 'school-management-system' ), esc_html__( 'Fee Invoices', 'school-management-system' ), 'read', WLSM_MENU_STAFF_INVOICES, array( 'WLSM_Menu', 'school_staff_invoices' ) );
						add_action( 'admin_print_styles-' . $school_staff_invoices_submenu, array( 'WLSM_Menu', 'menu_page_assets' ) );
					}
				}
			}
		}
	}

	// Manager dashboard.
	public static function school_management() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/dashboard/route.php';
	}

	// Manager classes.
	public static function classes() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/classes/route.php';
	}

	// Manager sessions.
	public static function sessions() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/sessions/route.php';
	}

	// Manager settings.
	public static function settings() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/settings/index.php';
	}

	// Schools assigned.
	public static function schools_assigned() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/schools-assigned.php';
	}

	// School - Dashboard.
	public static function school_staff_dashboard() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/dashboard/route.php';
	}

	// School - Inquiries.
	public static function school_staff_inquiries() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/inquiries/route.php';
	}

	// School - Admissions.
	public static function school_staff_admissions() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/admissions/route.php';
	}

	// School - Students.
	public static function school_staff_students() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/students/route.php';
	}

	// School - ID Cards.
	public static function school_staff_id_cards() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/id-cards/route.php';
	}

	// School - Roles.
	public static function school_staff_roles() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/roles/route.php';
	}

	// School - Employees.
	public static function school_staff_employees() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/employees/route.php';
	}

	// School - Promote.
	public static function school_staff_promote() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/promote/route.php';
	}

	// School - Settings.
	public static function school_staff_settings() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/route.php';
	}

	// Class - Classes & Sections.
	public static function school_staff_classes() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/classes/route.php';
	}

	// Class - Subjects.
	public static function school_staff_subjects() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/subjects/route.php';
	}

	// Class - Notices.
	public static function school_staff_notices() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/notices/route.php';
	}

	// Accountant - Invoices.
	public static function school_staff_invoices() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/route.php';
	}

	// Academic - Group.
	public static function school_staff_group_academic() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/groups/academic.php';
	}

	// Student - Group.
	public static function school_staff_group_student() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/groups/student.php';
	}

	// Administrator - Group.
	public static function school_staff_group_administrator() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/groups/administrator.php';
	}

	// Accounting - Group.
	public static function school_staff_group_accounting() {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/groups/accounting.php';
	}

	public static function menu_page_assets() {
		wp_enqueue_style( 'bootstrap', WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' );
		wp_enqueue_style( 'jquery-confirm', WLSM_PLUGIN_URL . 'assets/css/jquery-confirm.min.css' );
		wp_enqueue_style( 'toastr', WLSM_PLUGIN_URL . 'assets/css/toastr.min.css' );
		wp_enqueue_style( 'font-awesome-free', WLSM_PLUGIN_URL . 'assets/css/all.min.css' );
		wp_enqueue_style( 'zebra-datepicker', WLSM_PLUGIN_URL . 'assets/css/zebra_datepicker.min.css' );
		wp_enqueue_style( 'bootstrap-select', WLSM_PLUGIN_URL . 'assets/css/bootstrap-select.min.css' );

		wp_enqueue_style( 'dataTables-bootstrap4', WLSM_PLUGIN_URL . 'assets/css/datatable/dataTables.bootstrap4.min.css' );
		wp_enqueue_style( 'responsive-bootstrap4', WLSM_PLUGIN_URL . 'assets/css/datatable/responsive.bootstrap4.min.css' );
		wp_enqueue_style( 'jquery-dataTables', WLSM_PLUGIN_URL . 'assets/css/datatable/jquery.dataTables.min.css' );
		wp_enqueue_style( 'buttons-bootstrap4', WLSM_PLUGIN_URL . 'assets/css/datatable/buttons.bootstrap4.min.css' );

		wp_enqueue_style( 'wlsm-print-preview', WLSM_PLUGIN_URL . 'assets/css/print/wlsm-preview.css', array(), '1.8', 'all' );
		wp_enqueue_style( 'wlsm-admin', WLSM_PLUGIN_URL . 'assets/css/wlsm-admin.css', array(), '1.8', 'all' );
		wp_enqueue_style( 'wlsm-school-header', WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css', array(), '1.8', 'all' );

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script( 'popper', WLSM_PLUGIN_URL . 'assets/js/popper.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'bootstrap', WLSM_PLUGIN_URL . 'assets/js/bootstrap.min.js', array( 'popper' ), true, true );
		wp_enqueue_script( 'jquery-confirm', WLSM_PLUGIN_URL . 'assets/js/jquery-confirm.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'toastr', WLSM_PLUGIN_URL . 'assets/js/toastr.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'zebra-datepicker', WLSM_PLUGIN_URL . 'assets/js/zebra_datepicker.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'bootstrap-select', WLSM_PLUGIN_URL . 'assets/js/bootstrap-select.min.js', array( 'bootstrap' ), true, true );

		wp_enqueue_script( 'jquery-dataTables', WLSM_PLUGIN_URL . 'assets/js/datatable/jquery.dataTables.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'dataTables-bootstrap4', WLSM_PLUGIN_URL . 'assets/js/datatable/dataTables.bootstrap4.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'dataTables-responsive', WLSM_PLUGIN_URL . 'assets/js/datatable/dataTables.responsive.min.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'responsive-bootstrap4', WLSM_PLUGIN_URL . 'assets/js/datatable/responsive.bootstrap4.min.js', array( 'jquery' ), true, true );

		wp_enqueue_script( 'wlsm-admin', WLSM_PLUGIN_URL . 'assets/js/wlsm-admin.js', array( 'jquery', 'jquery-form' ), '1.8', true );
		// wp_localize_script( 'wlsm-admin', 'wlsmsecurity', wp_create_nonce( 'wlsm-security' ) );
		wp_localize_script( 'wlsm-admin', 'wlsmsecurity', array(
			'nonce' => wp_create_nonce( 'wlsm-security' )
		) );

		// wp_localize_script( 'wlsm-admin', 'wlsmdateformat', WLSM_Config::date_format() );
		wp_localize_script('wlsm-admin', 'wlsmdateformat', array(
			'wlsmDateFormat' => WLSM_Config::date_format()) );

		//wp_localize_script( 'wlsm-admin', 'wlsmadminurl', admin_url() );
		wp_localize_script('wlsm-admin', 'wlsmadminurl', array(
			'wlsmAdminURL' => admin_url()) );

		//wp_localize_script( 'wlsm-admin', 'wlsmloadingtext', esc_html__( 'Loading...', 'school-management-system' ) );
		wp_localize_script( 'wlsm-admin', 'wlsmloadingtext', array(			
			'loadingtext' => esc_html__( 'Loading...', 'school-management-system' )
		) );
	}
}
