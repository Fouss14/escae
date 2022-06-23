<?php
defined( 'ABSPATH' ) || die();

global $wpdb;

/* Table names */
define( 'WLSM_USERS', $wpdb->base_prefix . 'users' );
define( 'WLSM_POSTS', $wpdb->prefix . 'posts' );
define( 'WLSM_SCHOOLS', $wpdb->prefix . 'wlsm_schools' );
define( 'WLSM_SETTINGS', $wpdb->prefix . 'wlsm_settings' );
define( 'WLSM_CLASSES', $wpdb->prefix . 'wlsm_classes' );
define( 'WLSM_CLASS_SCHOOL', $wpdb->prefix . 'wlsm_class_school' );
define( 'WLSM_SESSIONS', $wpdb->prefix . 'wlsm_sessions' );
define( 'WLSM_INQUIRIES', $wpdb->prefix . 'wlsm_inquiries' );
define( 'WLSM_ROLES', $wpdb->prefix . 'wlsm_roles' );
define( 'WLSM_STAFF', $wpdb->prefix . 'wlsm_staff' );
define( 'WLSM_ADMINS', $wpdb->prefix . 'wlsm_admins' );
define( 'WLSM_SECTIONS', $wpdb->prefix . 'wlsm_sections' );
define( 'WLSM_STUDENT_RECORDS', $wpdb->prefix . 'wlsm_student_records' );
define( 'WLSM_PROMOTIONS', $wpdb->prefix . 'wlsm_promotions' );
define( 'WLSM_INVOICES', $wpdb->prefix . 'wlsm_invoices' );
define( 'WLSM_PAYMENTS', $wpdb->prefix . 'wlsm_payments' );
define( 'WLSM_SUBJECTS', $wpdb->prefix . 'wlsm_subjects' );
define( 'WLSM_NOTICES', $wpdb->prefix . 'wlsm_notices' );
define( 'WLSM_ADMIN_SUBJECT', $wpdb->prefix . 'wlsm_admin_subject' );

/* Super admin capability */
define( 'WLSM_ADMIN_CAPABILITY', 'manage_options' );

/* Menu page slugs for manager */
define( 'WLSM_MENU_SM', 'school-management-system' );
define( 'WLSM_MENU_SCHOOLS', 'sm-schools' );
define( 'WLSM_MENU_CLASSES', 'sm-classes' );
define( 'WLSM_MENU_SESSIONS', 'sm-sessions' );
define( 'WLSM_MENU_SETTINGS', 'sm-settings' );

/* Menu page slugs for schools assigned */
define( 'WLSM_MENU_SCHOOLS_ASSIGNED', 'sm-schools-assigned' );

/* Menu page slugs for school staff */

/* School */
define( 'WLSM_MENU_STAFF_SCHOOL', 'sm-staff-dashboard' );
define( 'WLSM_MENU_STAFF_INQUIRIES', 'sm-staff-inquiries' );
define( 'WLSM_MENU_STAFF_ADMISSIONS', 'sm-staff-admissions' );
define( 'WLSM_MENU_STAFF_STUDENTS', 'sm-staff-students' );
define( 'WLSM_MENU_STAFF_ID_CARDS', 'sm-staff-id-cards' );
define( 'WLSM_MENU_STAFF_ADMINS', 'sm-staff-admins' );
define( 'WLSM_MENU_STAFF_ROLES', 'sm-staff-roles' );
define( 'WLSM_MENU_STAFF_EMPLOYEES', 'sm-staff-employees' );
define( 'WLSM_MENU_STAFF_PROMOTE', 'sm-staff-promote' );
define( 'WLSM_MENU_STAFF_SETTINGS', 'sm-staff-settings' );

/* Class */
define( 'WLSM_MENU_STAFF_CLASSES', 'sm-staff-classes' );
define( 'WLSM_MENU_STAFF_SUBJECTS', 'sm-staff-subjects' );
define( 'WLSM_MENU_STAFF_NOTICES', 'sm-staff-notices' );

/* Accountant */
define( 'WLSM_MENU_STAFF_INVOICES', 'sm-staff-invoices' );

/* Groups */
define( 'WLSM_MENU_STAFF_ACADEMIC', 'sm-staff-academic' );
define( 'WLSM_MENU_STAFF_STUDENT', 'sm-staff-student' );
define( 'WLSM_MENU_STAFF_ADMINISTRATOR', 'sm-staff-administrator' );
define( 'WLSM_MENU_STAFF_ACCOUNTING', 'sm-staff-accounting' );
