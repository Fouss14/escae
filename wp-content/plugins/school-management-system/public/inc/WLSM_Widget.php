<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/widgets/WLSM_Login_Widget.php';
require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/widgets/WLSM_Noticeboard_Widget.php';

class WLSM_Widget {
	public static function register_widgets() {
		register_widget( 'WLSM_Login_Widget' );
		register_widget( 'WLSM_Noticeboard_Widget' );
	}
}
