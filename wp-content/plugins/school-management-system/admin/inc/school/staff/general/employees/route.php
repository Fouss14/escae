<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$action = '';
if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
	$action = sanitize_text_field( $_GET['action'] );
}
?>
<div class="wlsm container-fluid">
	<?php
	$disallow_session_change = true;

	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';

	if ( 'save' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/employees/save.php';
	} else {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/employees/index.php';
	}
	?>
</div>
