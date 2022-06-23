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
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';

	if ( 'save' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/subjects/save.php';
	} else if ( 'teachers' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/subjects/teachers.php';
	} else {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/subjects/index.php';
	}
	?>
</div>
