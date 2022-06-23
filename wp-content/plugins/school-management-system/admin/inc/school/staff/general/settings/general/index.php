<?php
defined( 'ABSPATH' ) || die();

// General settings.
$settings_general = WLSM_M_Setting::get_settings_general( $school_id );
$school_logo      = $settings_general['school_logo'];
?>
<div class="tab-pane fade show active" id="wlsm-school-general" role="tabpanel" aria-labelledby="wlsm-school-general-tab">

	<div class="row">
		<div class="col-md-9">
			<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-school-general-settings-form">
				<?php
				$nonce_action = 'save-school-general-settings';
				$nonce        = wp_create_nonce( $nonce_action );
				?>
				<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

				<input type="hidden" name="action" value="wlsm-save-school-general-settings">

				<div class="row">
					<div class="col-md-3">
						<label for="wlsm_school_logo" class="wlsm-font-bold mt-1">
							<?php esc_html_e( 'Upload School Logo', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="col-md-9">
						<div class="wlsm-school-logo-box">
							<div class="wlsm-school-logo-section">
								<div class="form-group">
									<div class="custom-file mb-3">
										<input type="file" class="custom-file-input" id="wlsm_school_logo" name="school_logo">
										<label class="custom-file-label" for="wlsm_school_logo">
											<?php esc_html_e( 'Choose File', 'school-management-system' ); ?>
										</label>
									</div>
								</div>

								<?php if ( ! empty ( $school_logo ) ) { ?>
								<img src="<?php echo esc_url( wp_get_attachment_url( $school_logo ) ); ?>" class="img-responsive wlsm-school-logo">

								<div class="form-group">
									<input class="form-check-input mt-2" type="checkbox" name="remove_school_logo" id="wlsm_school_remove_logo" value="1">
									<label class="ml-4 mb-1 mt-1 form-check-label wlsm-font-bold text-danger" for="wlsm_school_remove_logo">
										<?php esc_html_e( 'Remove School Logo?', 'school-management-system' ); ?>
									</label>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<div>
					<span class="float-right">
						<button type="submit" class="btn btn-primary" id="wlsm-save-school-general-settings-btn">
							<i class="fas fa-save"></i>&nbsp;
							<?php esc_html_e( 'Save', 'school-management-system' ); ?>
						</button>
					</span>
				</div>
			</form>
		</div>
	</div>

</div>
