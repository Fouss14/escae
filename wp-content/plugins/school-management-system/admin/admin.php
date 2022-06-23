<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/constants.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/WLSM_Menu.php';

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/WLSM_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/WLSM_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/WLSM_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/manager/WLSM_Setting.php';

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/WLSM_Staff_School.php';

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/WLSM_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/WLSM_Staff_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/WLSM_Staff_Accountant.php';

add_action( 'admin_menu', array( 'WLSM_Menu', 'create_menu' ) );

// Manager: School.
add_action( 'wp_ajax_wlsm-save-school', array( 'WLSM_School', 'save' ) );
add_action( 'wp_ajax_wlsm-delete-school', array( 'WLSM_School', 'delete' ) );
add_action( 'wp_ajax_wlsm-fetch-school-classes', array( 'WLSM_School', 'fetch_school_classes' ) );
add_action( 'wp_ajax_wlsm-delete-school-class', array( 'WLSM_School', 'delete_school_class' ) );
add_action( 'wp_ajax_wlsm-get-keyword-classes', array( 'WLSM_School', 'get_keyword_classes' ) );
add_action( 'wp_ajax_wlsm-assign-classes', array( 'WLSM_School', 'assign_classes' ) );
add_action( 'wp_ajax_wlsm-fetch-school-admins', array( 'WLSM_School', 'fetch_school_admins' ) );
add_action( 'wp_ajax_wlsm-assign-admin', array( 'WLSM_School', 'assign_admin' ) );
add_action( 'wp_ajax_wlsm-delete-school-admin', array( 'WLSM_School', 'delete_school_admin' ) );
add_action( 'wp_ajax_wlsm-edit-school-admin', array( 'WLSM_School', 'save_school_admin' ) );
add_action( 'wp_ajax_wlsm-set-school', array( 'WLSM_School', 'set_school' ) );

// Manager: Classes.
add_action( 'wp_ajax_wlsm-fetch-classes', array( 'WLSM_Class', 'fetch_classes' ) );
add_action( 'wp_ajax_wlsm-save-class', array( 'WLSM_Class', 'save' ) );
add_action( 'wp_ajax_wlsm-delete-class', array( 'WLSM_Class', 'delete' ) );

// Manager: Sessions.
add_action( 'wp_ajax_wlsm-fetch-sessions', array( 'WLSM_Session', 'fetch_sessions' ) );
add_action( 'wp_ajax_wlsm-save-session', array( 'WLSM_Session', 'save' ) );
add_action( 'wp_ajax_wlsm-delete-session', array( 'WLSM_Session', 'delete' ) );

// Manager: Settings.
add_action( 'wp_ajax_wlsm-save-general-settings', array( 'WLSM_Setting', 'save_general_settings' ) );
add_action( 'wp_ajax_wlsm-reset-plugin', array( 'WLSM_Setting', 'reset_plugin' ) );
add_action( 'wp_ajax_wlsm-save-uninstall-settings', array( 'WLSM_Setting', 'save_uninstall_settings' ) );

// Staff: Set school.
add_action( 'wp_ajax_wlsm-staff-set-school', array( 'WLSM_Staff_School', 'set_school' ) );

// Staff: Set session.
add_action( 'wp_ajax_wlsm-staff-set-session', array( 'WLSM_Staff_School', 'set_session' ) );

// Staff: Classes & Sections.
add_action( 'wp_ajax_wlsm-fetch-staff-classes', array( 'WLSM_Staff_Class', 'fetch_classes' ) );
add_action( 'wp_ajax_wlsm-fetch-class-sections', array( 'WLSM_Staff_Class', 'fetch_class_sections' ) );
add_action( 'wp_ajax_wlsm-save-section', array( 'WLSM_Staff_Class', 'save_section' ) );
add_action( 'wp_ajax_wlsm-delete-section', array( 'WLSM_Staff_Class', 'delete_section' ) );

// Staff: Admissions.
add_action( 'wp_ajax_wlsm-add-admission', array( 'WLSM_Staff_General', 'add_admission' ) );

// Staff: Students.
add_action( 'wp_ajax_wlsm-edit-student', array( 'WLSM_Staff_General', 'edit_student' ) );
add_action( 'wp_ajax_wlsm-get-students', array( 'WLSM_Staff_General', 'get_students' ) );
add_action( 'wp_ajax_wlsm-delete-student', array( 'WLSM_Staff_General', 'delete_student' ) );
add_action( 'wp_ajax_wlsm-view-session-records', array( 'WLSM_Staff_General', 'view_session_records' ) );
add_action( 'wp_ajax_wlsm-print-id-card', array( 'WLSM_Staff_General', 'print_id_card' ) );
add_action( 'wp_ajax_wlsm-print-bulk-id-cards', array( 'WLSM_Staff_General', 'print_id_cards' ) );

// Staff: Promote.
add_action( 'wp_ajax_wlsm-manage-promotion', array( 'WLSM_Staff_General', 'manage_promotion' ) );
add_action( 'wp_ajax_wlsm-promote-student', array( 'WLSM_Staff_General', 'promote_student' ) );

// Staff: Roles.
add_action( 'wp_ajax_wlsm-fetch-roles', array( 'WLSM_Staff_General', 'fetch_roles' ) );
add_action( 'wp_ajax_wlsm-save-role', array( 'WLSM_Staff_General', 'save_role' ) );
add_action( 'wp_ajax_wlsm-delete-role', array( 'WLSM_Staff_General', 'delete_role' ) );
add_action( 'wp_ajax_wlsm-get-role-permissions', array( 'WLSM_Staff_General', 'get_role_permissions' ) );

// Staff: Inquiries.
add_action( 'wp_ajax_wlsm-fetch-inquiries', array( 'WLSM_Staff_General', 'fetch_inquiries' ) );
add_action( 'wp_ajax_wlsm-save-inquiry', array( 'WLSM_Staff_General', 'save_inquiry' ) );
add_action( 'wp_ajax_wlsm-delete-inquiry', array( 'WLSM_Staff_General', 'delete_inquiry' ) );
add_action( 'wp_ajax_wlsm-view-inquiry-message', array( 'WLSM_Staff_General', 'view_inquiry_message' ) );

// Staff: Notices.
add_action( 'wp_ajax_wlsm-fetch-notices', array( 'WLSM_Staff_Class', 'fetch_notices' ) );
add_action( 'wp_ajax_wlsm-save-notice', array( 'WLSM_Staff_Class', 'save_notice' ) );
add_action( 'wp_ajax_wlsm-delete-notice', array( 'WLSM_Staff_Class', 'delete_notice' ) );

// Staff: Subjects.
add_action( 'wp_ajax_wlsm-fetch-subjects', array( 'WLSM_Staff_Class', 'fetch_subjects' ) );
add_action( 'wp_ajax_wlsm-save-subject', array( 'WLSM_Staff_Class', 'save_subject' ) );
add_action( 'wp_ajax_wlsm-delete-subject', array( 'WLSM_Staff_Class', 'delete_subject' ) );
add_action( 'wp_ajax_wlsm-fetch-subject-admins', array( 'WLSM_Staff_Class', 'fetch_subject_admins' ) );
add_action( 'wp_ajax_wlsm-delete-subject-admin', array( 'WLSM_Staff_Class', 'delete_subject_admin' ) );
add_action( 'wp_ajax_wlsm-get-keyword-admins', array( 'WLSM_Staff_Class', 'get_keyword_admins' ) );
add_action( 'wp_ajax_wlsm-assign-subject-admins', array( 'WLSM_Staff_Class', 'assign_subject_admins' ) );

// Staff: Employees.
add_action( 'wp_ajax_wlsm-fetch-staff-employee', array( 'WLSM_Staff_General', 'fetch_employees' ) );
add_action( 'wp_ajax_wlsm-save-employee', array( 'WLSM_Staff_General', 'save_employee' ) );
add_action( 'wp_ajax_wlsm-delete-employee', array( 'WLSM_Staff_General', 'delete_employee' ) );

// Staff: Settings.
add_action( 'wp_ajax_wlsm-save-school-general-settings', array( 'WLSM_Staff_General', 'save_school_general_settings' ) );
add_action( 'wp_ajax_wlsm-save-school-email-carrier-settings', array( 'WLSM_Staff_General', 'save_school_email_carrier_settings' ) );
add_action( 'wp_ajax_wlsm-save-school-email-templates-settings', array( 'WLSM_Staff_General', 'save_school_email_templates_settings' ) );
add_action( 'wp_ajax_wlsm-save-school-payment-method-settings', array( 'WLSM_Staff_General', 'save_school_payment_method_settings' ) );

// Staff: Invoices.
add_action( 'wp_ajax_wlsm-get-invoices', array( 'WLSM_Staff_Accountant', 'get_invoices' ) );
add_action( 'wp_ajax_wlsm-save-invoice', array( 'WLSM_Staff_Accountant', 'save_invoice' ) );
add_action( 'wp_ajax_wlsm-delete-invoice', array( 'WLSM_Staff_Accountant', 'delete_invoice' ) );
add_action( 'wp_ajax_wlsm-print-invoice', array( 'WLSM_Staff_Accountant', 'print_invoice' ) );
add_action( 'wp_ajax_wlsm-print-invoice-fee-structure', array( 'WLSM_Staff_Accountant', 'print_invoice_fee_structure' ) );

// Staff: Invoice Payments.
add_action( 'wp_ajax_wlsm-fetch-invoice-payments', array( 'WLSM_Staff_Accountant', 'fetch_invoice_payments' ) );
add_action( 'wp_ajax_wlsm-collect-invoice-payment', array( 'WLSM_Staff_Accountant', 'collect_invoice_payment' ) );
add_action( 'wp_ajax_wlsm-delete-invoice-payment', array( 'WLSM_Staff_Accountant', 'delete_invoice_payment' ) );
add_action( 'wp_ajax_wlsm-print-invoice-payment', array( 'WLSM_Staff_Accountant', 'print_payment' ) );

// Staff: Payments.
add_action( 'wp_ajax_wlsm-fetch-payments', array( 'WLSM_Staff_Accountant', 'fetch_payments' ) );
add_action( 'wp_ajax_wlsm-delete-payment', array( 'WLSM_Staff_Accountant', 'delete_payment' ) );
add_action( 'wp_ajax_wlsm-view-payment-note', array( 'WLSM_Staff_Accountant', 'view_payment_note' ) );

// Staff: Dashboard.
add_action( 'wp_ajax_wlsm-fetch-stats-payments', array( 'WLSM_Staff_General', 'fetch_stats_payments' ) );

// Staff: General Actions.
add_action( 'wp_ajax_wlsm-get-class-sections', array( 'WLSM_Staff_General', 'get_class_sections' ) );
add_action( 'wp_ajax_wlsm-get-section-students', array( 'WLSM_Staff_General', 'get_section_students' ) );
add_action( 'wp_ajax_wlsm-get-school-classes', array( 'WLSM_Staff_General', 'get_school_classes' ) );
add_action( 'wp_ajax_wlsm-get-school-class-sections', array( 'WLSM_Staff_General', 'get_school_class_sections' ) );
