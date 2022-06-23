<?php
defined( 'ABSPATH' ) || die();

// Email Invoice Generated settings.
$settings_email_invoice_generated = WLSM_M_Setting::get_settings_email_invoice_generated( $school_id );
$email_invoice_generated_enable   = $settings_email_invoice_generated['enable'];
$email_invoice_generated_subject  = $settings_email_invoice_generated['subject'];
$email_invoice_generated_body     = $settings_email_invoice_generated['body'];

$email_invoice_generated_placeholders = WLSM_Email::invoice_generated_placeholders();
?>
<button type="button" class="mt-2 btn btn-block btn-primary" data-toggle="collapse" data-target="#wlsm_email_invoice_generated_fields" aria-expanded="true" aria-controls="wlsm_email_invoice_generated_fields">
	<?php esc_html_e( 'Invoice Generated Email Template', 'school-management-system' ); ?>
</button>

<div class="collapse border border-top-0 border-primary p-3" id="wlsm_email_invoice_generated_fields">

	<div class="wlsm_email_template wlsm_email_invoice_generated">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_invoice_generated_enable" class="wlsm-font-bold">
					<?php esc_html_e( 'Invoice Generated Email', 'school-management-system' ); ?>:
				</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<label for="wlsm_email_invoice_generated_enable" class="wlsm-font-bold">
						<input <?php checked( $email_invoice_generated_enable, true, true ); ?> type="checkbox" name="email_invoice_generated_enable" id="wlsm_email_invoice_generated_enable" value="1">
						<?php esc_html_e( 'Enable', 'school-management-system' ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_invoice_generated mb-3">
		<div class="row">
			<div class="col-md-12">
				<span class="wlsm-font-bold text-dark"><?php esc_html_e( 'You can use the following variables:', 'school-management-system' ); ?></span>
				<div class="row">
					<?php foreach ( $email_invoice_generated_placeholders as $key => $value ) { ?>
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

	<div class="wlsm_email_template wlsm_email_invoice_generated">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_invoice_generated_subject" class="wlsm-font-bold"><?php esc_html_e( 'Email Subject', 'school-management-system' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<input name="email_invoice_generated_subject" type="text" id="wlsm_email_invoice_generated_subject" value="<?php echo esc_attr( $email_invoice_generated_subject ); ?>" class="form-control" placeholder="<?php esc_attr_e( 'Email Subject', 'school-management-system' ); ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_invoice_generated">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_invoice_generated_body" class="wlsm-font-bold"><?php esc_html_e( 'Email Body', 'school-management-system' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<?php
					$settings = array(
						'media_buttons' => false,
						'textarea_name' => 'email_invoice_generated_body',
						'textarea_rows' => 10,
						'wpautop'       => false,
					);
					wp_editor( wp_kses_post( stripslashes( $email_invoice_generated_body ) ), 'wlsm_email_invoice_generated_body', $settings );
					?>
				</div>
			</div>
		</div>
	</div>

</div>
