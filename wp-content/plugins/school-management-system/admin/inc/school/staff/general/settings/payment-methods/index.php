<?php
defined( 'ABSPATH' ) || die();

// Stripe settings.
$settings_stripe               = WLSM_M_Setting::get_settings_stripe( $school_id );
$school_stripe_enable          = $settings_stripe['enable'];
$school_stripe_publishable_key = $settings_stripe['publishable_key'];
$school_stripe_secret_key      = $settings_stripe['secret_key'];
?>
<div class="tab-pane fade" id="wlsm-school-payment-method" role="tabpanel" aria-labelledby="wlsm-school-payment-method-tab">

	<div class="row">
		<div class="col-md-12">
			<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-school-payment-method-settings-form">
				<?php
				$nonce_action = 'save-school-payment-method-settings';
				$nonce        = wp_create_nonce( $nonce_action );
				?>
				<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

				<input type="hidden" name="action" value="wlsm-save-school-payment-method-settings">

				<button type="button" class="mt-2 btn btn-block btn-primary" data-toggle="collapse" data-target="#wlsm_stripe_fields" aria-expanded="true" aria-controls="wlsm_stripe_fields">
					<?php esc_html_e( 'Stripe Payment Gateway', 'school-management-system' ); ?>
				</button>

				<div class="collapse border border-top-0 border-primary p-3" id="wlsm_stripe_fields">

					<div class="wlsm_payment_method wlsm_stripe">
						<div class="row">
							<div class="col-md-3">
								<label for="wlsm_stripe_enable" class="wlsm-font-bold">
									<?php esc_html_e( 'Stripe Payment', 'school-management-system' ); ?>:
								</label>
							</div>
							<div class="col-md-9">
								<div class="form-group">
									<label for="wlsm_stripe_enable" class="wlsm-font-bold">
										<input <?php checked( $school_stripe_enable, true, true ); ?> type="checkbox" name="stripe_enable" id="wlsm_stripe_enable" value="1">
										<?php esc_html_e( 'Enable', 'school-management-system' ); ?>
									</label>
									<?php if ( ! WLSM_Payment::currency_supports_stripe( $currency ) ) { ?>
									<br>
									<small class="text-secondary">
										<?php
										printf(
											/* translators: %s: currency code */
											__( 'Stripe does not support currency %s.', 'school-management-system' ),
											esc_html( $currency )
										);
										?>
									</small>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

					<div class="wlsm_payment_method wlsm_stripe">
						<div class="row">
							<div class="col-md-3">
								<label for="wlsm_stripe_publishable_key" class="wlsm-font-bold"><?php esc_html_e( 'Stripe Publishable Key', 'school-management-system' ); ?>:</label>
							</div>
							<div class="col-md-9">
								<div class="form-group">
									<input name="stripe_publishable_key" type="text" id="wlsm_stripe_publishable_key" value="<?php echo esc_attr( $school_stripe_publishable_key ); ?>" class="form-control" placeholder="<?php esc_attr_e( 'Stripe Publishable Key', 'school-management-system' ); ?>">
								</div>
							</div>
						</div>
					</div>

					<div class="wlsm_payment_method wlsm_stripe">
						<div class="row">
							<div class="col-md-3">
								<label for="wlsm_stripe_secret_key" class="wlsm-font-bold"><?php esc_html_e( 'Stripe Secret Key', 'school-management-system' ); ?>:</label>
							</div>
							<div class="col-md-9">
								<div class="form-group">
									<input name="stripe_secret_key" type="text" id="wlsm_stripe_secret_key" value="<?php echo esc_attr( $school_stripe_secret_key ); ?>" class="form-control" placeholder="<?php esc_attr_e( 'Stripe Secret Key', 'school-management-system' ); ?>">
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="mt-2">
					<span class="float-right">
						<button type="submit" class="btn btn-primary" id="wlsm-save-school-payment-method-settings-btn">
							<i class="fas fa-save"></i>&nbsp;
							<?php esc_html_e( 'Save', 'school-management-system' ); ?>
						</button>
					</span>
				</div>
			</form>
		</div>
	</div>

</div>
