<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';

$default_session_id = get_option( 'wlsm_current_session' );
$active_currency    = WLSM_Config::currency();
$active_date_format = WLSM_Config::date_format();
$gdpr_enable        = get_option( 'wlsm_gdpr_enable' );

$sessions         = WLSM_M_Session::fetch_sessions();
$currency_symbols = WLSM_Helper::currency_symbols();
$date_formats     = WLSM_Helper::date_formats();

$delete_on_uninstall = get_option( 'wlsm_delete_on_uninstall' );
?>
<div class="wlsm">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-main-header card col wlsm-page-heading-box">
					<h1 class="h3 text-center wlsm-page-heading">
						<i class="fas fa-cogs text-primary"></i>
						<?php esc_html_e( 'Settings', 'school-management-system' ); ?>
					</h1>
				</div>
			</div>
		</div>

		<div class="row justify-content-md-center mt-3">
			<div class="col-md-12">
				<ul class="nav nav-pills mb-3 mt-1" id="wlsm-settings-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link border border-primary active" id="wlsm-general-tab" data-toggle="tab" href="#wlsm-general" role="tab" aria-controls="wlsm-general" aria-selected="true">
							<?php esc_html_e( 'General', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-shortcodes-tab" data-toggle="tab" href="#wlsm-shortcodes" role="tab" aria-controls="wlsm-shortcodes" aria-selected="true">
							<?php esc_html_e( 'Shortcodes', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-reset-plugin-tab" data-toggle="tab" href="#wlsm-reset-plugin" role="tab" aria-controls="wlsm-reset-plugin" aria-selected="true">
							<?php esc_html_e( 'Reset Plugin', 'school-management-system' ); ?>
						</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link border border-primary" id="wlsm-uninstall-tab" data-toggle="tab" href="#wlsm-uninstall" role="tab" aria-controls="wlsm-uninstall" aria-selected="true">
							<?php esc_html_e( 'Uninstall', 'school-management-system' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="row">
			<div class="col-md-8">
				<div class="tab-content" id="wlsm-tabs">
					<div class="ml-1 tab-pane fade show active" id="wlsm-general" role="tabpanel" aria-labelledby="wlsm-general-tab">

						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-general-settings-form">
							<?php
							$nonce_action = 'save-general-settings';
							$nonce        = wp_create_nonce( $nonce_action );
							?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-general-settings">

							<div class="row">
								<div class="col-md-4">
									<label for="wlsm_active_session" class="wlsm-font-bold">
										<?php esc_html_e( 'Set Active Session', 'school-management-system' ); ?>:
									</label>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<select name="active_session" id="wlsm_active_session" class="form-control">
											<option value=""></option>
											<?php foreach ( $sessions as $session ) { ?>
											<option <?php selected( $session->ID, $default_session_id, true ); ?> value="<?php echo esc_attr( $session->ID ); ?>"><?php echo esc_attr( $session->label ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-4">
									<label for="wlsm_date_format" class="wlsm-font-bold">
										<?php esc_html_e( 'Set Date Format', 'school-management-system' ); ?>:
									</label>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<select name="date_format" id="wlsm_date_format" class="form-control">
											<?php foreach ( $date_formats as $key => $date_format ) { ?>
											<option <?php selected( $key, $active_date_format, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $date_format ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-4">
									<label for="wlsm_currency" class="wlsm-font-bold">
										<?php esc_html_e( 'Set Currency', 'school-management-system' ); ?>:
									</label>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<select name="currency" id="wlsm_currency" class="form-control">
											<?php foreach ( $currency_symbols as $key => $currency_symbol ) { ?>
											<option <?php selected( $key, $active_currency, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $key ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-4">
									<label for="wlsm_gdpr_enable" class="wlsm-font-bold">
										<?php esc_html_e( 'GDPR Compliance', 'school-management-system' ); ?>:
									</label>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<input <?php checked( $gdpr_enable, 1, true ); ?> class="form-check-input mt-1" type="checkbox" name="gdpr_enable" id="wlsm_gdpr_enable" value="1">
										<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-secondary" for="wlsm_gdpr_enable">
											<?php esc_html_e( 'Enable GDPR Compliance for Inquiry Form', 'school-management-system' ); ?>
										</label>
									</div>
								</div>
							</div>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-primary" id="wlsm-save-general-settings-btn">
										<i class="fas fa-save"></i>&nbsp;
										<?php esc_html_e( 'Save', 'school-management-system' ); ?>
									</button>
								</span>
							</div>
						</form>

					</div>
					<div class="tab-pane fade" id="wlsm-shortcodes" role="tabpanel" aria-labelledby="wlsm-shortcodes-tab">

						<ul class="list-group list-group-flush">
							<li class="list-inline-item">
								<div class="alert alert-light">
									<?php esc_html_e( 'To display fees submission form on a page or post, use shortcode', 'school-management-system' ); ?>:<br>
			 						<span id="wlsm_school_management_fees_shortcode" class="wlsm-font-bold text-dark">[school_management_fees]</span>
									<button id="wlsm_school_management_fees_copy_btn" class="btn btn-outline-success btn-sm" type="button">
										<?php esc_html_e( 'Copy', 'school-management-system' ); ?>
									</button>
								</div>
							</li>

							<li class="list-inline-item">
								<div class="alert alert-light">
									<?php esc_html_e( 'To display login form and student dashboard on a page or post, use shortcode', 'school-management-system' ); ?>:<br>
			 						<span id="wlsm_school_management_account_shortcode" class="wlsm-font-bold text-dark">[school_management_account]</span>
									<button id="wlsm_school_management_account_copy_btn" class="btn btn-outline-success btn-sm" type="button">
										<?php esc_html_e( 'Copy', 'school-management-system' ); ?>
									</button>
								</div>
							</li>

							<li class="list-inline-item">
								<div class="alert alert-light">
									<?php esc_html_e( 'To display admission inquiry form on a page or post, use shortcode', 'school-management-system' ); ?>:<br>
			 						<span id="wlsm_school_management_inquiry_shortcode" class="wlsm-font-bold text-dark">[school_management_inquiry]</span>
									<button id="wlsm_school_management_inquiry_copy_btn" class="btn btn-outline-success btn-sm" type="button">
										<?php esc_html_e( 'Copy', 'school-management-system' ); ?>
									</button>
								</div>
							</li>

							<li class="list-inline-item">
								<div class="alert alert-light">
									<?php esc_html_e( 'To display noticeboard on a page or post, use shortcode', 'school-management-system' ); ?>:<br>
										<span id="wlsm_school_management_noticeboard_shortcode" class="wlsm-font-bold text-dark">[school_management_noticeboard]</span>
									<button id="wlsm_school_management_noticeboard_copy_btn" class="btn btn-outline-success btn-sm" type="button">
										<?php esc_html_e( 'Copy', 'school-management-system' ); ?>
									</button>
								</div>
							</li>
						</ul>

					</div>
					<div class="tab-pane fade" id="wlsm-reset-plugin" role="tabpanel" aria-labelledby="wlsm-reset-plugin-tab">

						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-reset-plugin-form">
							<?php
							$nonce_action = 'reset-plugin';
							$nonce        = wp_create_nonce( $nonce_action );
							?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-reset-plugin">

							<div class="mb-3 mt-1 alert alert-info">	
								<?php esc_html_e( 'Here, you can reset the plugin to its initial state.', 'school-management-system' ); ?>
							</div>

							<div class="ml-4 mb-2 mt-2 wlsm-font-bold">
								<?php esc_html_e( 'This will:', 'school-management-system' ); ?>
							</div>

							<ul class="list-group list-group-flush text-dark">
								<li class="list-group-item">
									* <?php esc_html_e( 'Recreate all database tables.', 'school-management-system' ); ?>
								</li>
								<li class="list-group-item">
									* <?php esc_html_e( 'Reset all settings.', 'school-management-system' ); ?>
								</li>
							</ul>

							<div class="mt-3 text-right">
								<button type="button" class="btn btn-danger" id="wlsm-reset-plugin-btn" data-message-title="<?php esc_attr_e( 'Reset Plugin!', 'school-management-system' ); ?>" data-message-content="<?php esc_attr_e( 'Are you sure to reset the plugin to its initial state?', 'school-management-system' ); ?>" data-submit="<?php esc_attr_e( 'Reset', 'school-management-system' ); ?>" data-cancel="<?php esc_attr_e( 'Cancel', 'school-management-system' ); ?>">
									<i class="fas fa-redo"></i>&nbsp;
									<?php esc_html_e( 'Reset Plugin', 'school-management-system' ); ?>
								</button>
							</div>
						</form>

					</div>
					<div class="ml-1 tab-pane fade" id="wlsm-uninstall" role="tabpanel" aria-labelledby="wlsm-uninstall-tab">

						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-uninstall-settings-form">
							<?php
							$nonce_action = 'save-uninstall-settings';
							$nonce        = wp_create_nonce( $nonce_action );
							?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-uninstall-settings">

							<div class="row">
								<div class="col-md-4">
									<label for="wlsm_delete_on_uninstall" class="wlsm-font-bold">
										<?php esc_html_e( 'Delete Data On Uninstall', 'school-management-system' ); ?>:
									</label>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<input <?php checked( $delete_on_uninstall, 1, true ); ?> class="form-check-input mt-1" type="checkbox" name="delete_on_uninstall" id="wlsm_delete_on_uninstall" value="1">
										<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-secondary" for="wlsm_delete_on_uninstall">
											<?php esc_html_e( 'Delete database tables and settings when you delete the plugin?', 'school-management-system' ); ?>
										</label>
									</div>
								</div>
							</div>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-primary" id="wlsm-save-uninstall-settings-btn">
										<i class="fas fa-save"></i>&nbsp;
										<?php esc_html_e( 'Save', 'school-management-system' ); ?>
									</button>
								</span>
							</div>
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
