<?php
defined('ABSPATH') || die();

$school_id = 1;

$school = WLSM_M_School::get_active_school($school_id);

if (!$school) {
	$invalid_message = esc_html__('School not found.', 'school-management-system');
	return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/partials/invalid.php';
}

require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/noticeboard.php';

return ob_get_clean();
