<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/constants.php';

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_Language.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_Shortcode.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_Widget.php';

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Schedule.php';

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_P_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_P_Invoice.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_P_Inquiry.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/WLSM_P_Print.php';

// Load translation.
add_action( 'plugins_loaded', array( 'WLSM_Language', 'load_translation' ) );

// Register widgets.
add_action( 'widgets_init', array( 'WLSM_Widget', 'register_widgets' ) );

// Add shortcodes.
add_shortcode( 'school_management_account', array( 'WLSM_Shortcode', 'account' ) );
add_shortcode( 'school_management_inquiry', array( 'WLSM_Shortcode', 'inquiry' ) );
add_shortcode( 'school_management_fees', array( 'WLSM_Shortcode', 'fees' ) );
add_shortcode( 'school_management_noticeboard', array( 'WLSM_Shortcode', 'noticeboard' ) );

// Enqueue shortcode assets.
add_action('wp_enqueue_scripts', array( 'WLSM_Shortcode', 'enqueue_assets' ) );

// Schedules.
add_action( 'wlsm_notify_for_student_admission', array( 'WLSM_Schedule', 'notify_for_student_admission' ), 10, 4 );
add_action( 'wlsm_notify_for_invoice_generated', array( 'WLSM_Schedule', 'notify_for_invoice_generated' ), 10, 3 );
add_action( 'wlsm_notify_for_online_fee_submission', array( 'WLSM_Schedule', 'notify_for_online_fee_submission' ), 10, 3 );
add_action( 'wlsm_notify_for_offline_fee_submission', array( 'WLSM_Schedule', 'notify_for_offline_fee_submission' ), 10, 3 );

// Get students with pending invoices.
add_action( 'wp_ajax_wlsm-p-get-students-with-pending-invoices', array( 'WLSM_P_Invoice', 'get_students_with_pending_invoices' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-get-students-with-pending-invoices', array( 'WLSM_P_Invoice', 'get_students_with_pending_invoices' ) );

// Get student pending invoices.
add_action( 'wp_ajax_wlsm-p-get-student-pending-invoices', array( 'WLSM_P_Invoice', 'get_student_pending_invoices' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-get-student-pending-invoices', array( 'WLSM_P_Invoice', 'get_student_pending_invoices' ) );

// Get student pending invoice.
add_action( 'wp_ajax_wlsm-p-get-student-pending-invoice', array( 'WLSM_P_Invoice', 'get_student_pending_invoice' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-get-student-pending-invoice', array( 'WLSM_P_Invoice', 'get_student_pending_invoice' ) );

// Pay invoice amount.
add_action( 'wp_ajax_wlsm-p-pay-invoice-amount', array( 'WLSM_P_Invoice', 'pay_invoice_amount' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-pay-invoice-amount', array( 'WLSM_P_Invoice', 'pay_invoice_amount' ) );

// Submit inquiry.
add_action( 'wp_ajax_wlsm-p-submit-inquiry', array( 'WLSM_P_Inquiry', 'submit_inquiry' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-submit-inquiry', array( 'WLSM_P_Inquiry', 'submit_inquiry' ) );

// Process Stripe.
add_action( 'wp_ajax_wlsm-p-pay-with-stripe', array( 'WLSM_P_Invoice', 'process_stripe' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-pay-with-stripe', array( 'WLSM_P_Invoice', 'process_stripe' ) );

// General Actions.
add_action( 'wp_ajax_wlsm-p-get-school-classes', array( 'WLSM_P_General', 'get_school_classes' ) );
add_action( 'wp_ajax_nopriv_wlsm-p-get-school-classes', array( 'WLSM_P_General', 'get_school_classes' ) );

// Student: Print ID card.
add_action( 'wp_ajax_wlsm-p-st-print-id-card', array( 'WLSM_P_Print', 'student_print_id_card' ) );

// Student: Print invoice payment.
add_action( 'wp_ajax_wlsm-p-st-print-invoice-payment', array( 'WLSM_P_Print', 'student_print_payment' ) );
