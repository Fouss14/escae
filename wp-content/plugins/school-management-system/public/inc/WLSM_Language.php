<?php
defined('ABSPATH') || die();

class WLSM_Language {
	public static function load_translation() {
		load_plugin_textdomain('school-management-system', false, basename(WLSM_PLUGIN_DIR_PATH) . '/languages');
	}
}
