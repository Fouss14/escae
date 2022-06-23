<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$page_url = WLSM_M_Staff_General::get_employees_page_url();
$role     = WLSM_M_Role::get_employee_key();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/save_staff.php';
