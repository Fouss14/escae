<?php
defined( 'ABSPATH' ) || die();

// Email Online Fee Submission settings.
$settings_email_online_fee_submission = WLSM_M_Setting::get_settings_email_online_fee_submission( $school_id );
$email_online_fee_submission_enable   = $settings_email_online_fee_submission['enable'];
$email_online_fee_submission_subject  = $settings_email_online_fee_submission['subject'];
$email_online_fee_submission_body     = $settings_email_online_fee_submission['body'];

$email_online_fee_submission_placeholders = WLSM_Email::online_fee_submission_placeholders();
?>
<button type="button" class="mt-2 btn btn-block btn-primary" data-toggle="collapse" data-target="#wlsm_email_online_fee_submission_fields" aria-expanded="true" aria-controls="wlsm_email_online_fee_submission_fields">
	<?php esc_html_e( 'Online Fee Submission Email Template', 'school-management-system' ); ?>
</button>

<div class="collapse border border-top-0 border-primary p-3" id="wlsm_email_online_fee_submission_fields">

	<div class="wlsm_email_template wlsm_email_online_fee_submission">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_online_fee_submission_enable" class="wlsm-font-bold">
					<?php esc_html_e( 'Online Fee Submission Email', 'school-management-system' ); ?>:
				</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<label for="wlsm_email_online_fee_submission_enable" class="wlsm-font-bold">
						<input <?php checked( $email_online_fee_submission_enable, true, true ); ?> type="checkbox" name="email_online_fee_submission_enable" id="wlsm_email_online_fee_submission_enable" value="1">
						<?php esc_html_e( 'Enable', 'school-management-system' ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_online_fee_submission mb-3">
		<div class="row">
			<div class="col-md-12">
				<span class="wlsm-font-bold text-dark"><?php esc_html_e( 'You can use the following variables:', 'school-management-system' ); ?></span>
				<div class="row">
					<?php foreach ( $email_online_fee_submission_placeholders as $key => $value ) { ?>
					<div class="col-sm-6 col-md-3 pb-1 pt-1 border">
						<span class="wlsm-font-bold text-secondary"><?php echo esc_html( $value ); ?></span>
						<br>
						<span><?php echo esc_html( $key ); ?></span>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_online_fee_submission">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_online_fee_submission_subject" class="wlsm-font-bold"><?php esc_html_e( 'Email Subject', 'school-management-system' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<input name="email_online_fee_submission_subject" type="text" id="wlsm_email_online_fee_submission_subject" value="<?php echo esc_attr( $email_online_fee_submission_subject ); ?>" class="form-control" placeholder="<?php esc_attr_e( 'Email Subject', 'school-management-system' ); ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_online_fee_submission">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_online_fee_submission_body" class="wlsm-font-bold"><?php esc_html_e( 'Email Body', 'school-management-system' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<?php
					$settings = array(
						'media_buttons' => false,
						'textarea_name' => 'email_online_fee_submission_body',
						'textarea_rows' => 10,
						'wpautop'       => false,
					);
					wp_editor( wp_kses_post( stripslashes( $email_online_fee_submission_body ) ), 'wlsm_email_online_fee_submission_body', $settings );
					?>
				</div>
			</div>
		</div>
	</div>

</div>
