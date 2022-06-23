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

	if ( 'sections' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/classes/sections.php';
	} else {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/class/classes/index.php';
	}
	?>
</div>
