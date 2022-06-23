<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Email.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';

$school_id = $current_school['id'];

$session_id    = $current_session['ID'];
$session_label = $current_session['label'];

$default_session_id    = $current_session['default_session_id'];
$default_session_label = WLSM_M_Session::get_session_label( $default_session_id );

// Currency.
$currency = WLSM_Config::currency();
?>
<div class="wlsm">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card col wlsm-page-heading-box">
					<h1 class="h3 text-center wlsm-page-heading">
						<i class="fas fa-cogs text-primary"></i>
						<?php esc_html_e( 'Settings', 'school-management-system' ); ?>
					</h1>
				</div>
			</div>
		</div>

		<div class="row justify-content-md-center mt-3">
			<div class="col-md-12">
				<ul class="nav nav-pills mb-3 mt-1" id="wlsm-school-settings-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link border border-primary active" id="wlsm-school-general-tab" data-toggle="tab" href="#wlsm-school-general" role="tab" aria-controls="wlsm-school-general" aria-selected="true">
							<?php esc_html_e( 'General', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-school-email-carrier-tab" data-toggle="tab" href="#wlsm-school-email-carrier" role="tab" aria-controls="wlsm-school-email-carrier" aria-selected="true">
							<?php esc_html_e( 'Email Carrier', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-school-email-templates-tab" data-toggle="tab" href="#wlsm-school-email-templates" role="tab" aria-controls="wlsm-school-email-templates" aria-selected="true">
							<?php esc_html_e( 'Email Templates', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-school-payment-method-tab" data-toggle="tab" href="#wlsm-school-payment-method" role="tab" aria-controls="wlsm-school-payment-method" aria-selected="true">
							<?php esc_html_e( 'Payment Methods', 'school-management-system' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="tab-content wlsm-school-settings" id="wlsm-tabs">
		<?php
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/general/index.php';
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-carrier/index.php';
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-templates/index.php';
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/payment-methods/index.php';
		?>
		</div>

	</div>
</div>
