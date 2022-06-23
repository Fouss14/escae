<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

$user_info = WLSM_M_Role::get_user_info();

if ( ! $user_info['current_school'] ) {
	die();
}

$current_school   = $user_info['current_school'];
$schools_assigned = $user_info['schools_assigned'];

$current_session = WLSM_Config::current_session();
