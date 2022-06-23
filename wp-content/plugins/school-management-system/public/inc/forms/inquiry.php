<?php
defined( 'ABSPATH' ) || die();

$school_id = 1;

$school = WLSM_M_School::get_active_school( $school_id );
if ( ! $school ) {
	$invalid_message = esc_html__( 'School not found.', 'school-management-system' );
	return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/partials/invalid.php';
}

$classes = WLSM_M_Staff_General::fetch_school_classes( $school_id );

$nonce_action = 'wlsm-submit-inquiry';
?>
<div class="wlsm">
	<div id="wlsm-submit-inquiry-section">

		<div class="wlsm-header-title wlsm-font-bold wlsm-mb-3">
			<span class="wlsm-border-bottom wlsm-pb-1">
				<?php esc_html_e( 'Admission Inquiry', 'school-management-system' ); ?>
			</span>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-submit-inquiry-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wlsm-p-submit-inquiry">

			<input type="hidden" name="school_id" value="<?php echo esc_attr( $school_id ); ?>" id="wlsm_school">

			<!-- Inquiry -->
			<div class="wlsm-form-section wlsm-mt-2">
				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_school_class" class="wlsm-form-label wlsm-font-bold">
							<span class="wlsm-text-danger">*</span> <?php esc_html_e( 'Class', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<select name="class_id" class="wlsm-form-control" id="wlsm_school_class">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php foreach ( $classes as $class ) { ?>
								<option value="<?php echo esc_attr( $class->ID ); ?>">
									<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_name" class="wlsm-form-label wlsm-font-bold">
							<span class="wlsm-text-danger">*</span> <?php esc_html_e( 'Name', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<input type="text" name="name" class="form-control" id="wlsm_name" placeholder="<?php esc_attr_e( 'Enter name', 'school-management-system' ); ?>">
					</div>
				</div>

				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_last_name" class="wlsm-form-label wlsm-font-bold">
						<?php esc_html_e( 'Last Name', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<input type="text" name="last_name" class="form-control" id="wlsm_last_name" placeholder="<?php esc_attr_e( 'Enter Last name', 'school-management-system' ); ?>">
					</div>
				</div>

				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_phone" class="wlsm-form-label wlsm-font-bold">
							<span class="wlsm-text-danger">*</span> <?php esc_html_e( 'Phone', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<input type="text" name="phone" class="wlsm-form-control" id="wlsm_phone" placeholder="<?php esc_attr_e( 'Enter phone number', 'school-management-system' ); ?>">
					</div>
				</div>
				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_email" class="wlsm-form-label wlsm-font-bold">
							<?php esc_html_e( 'Email', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<input type="email" name="email" class="wlsm-form-control" id="wlsm_email" placeholder="<?php esc_attr_e( 'Enter email address', 'school-management-system' ); ?>">
					</div>
				</div>
				<div class="wlsm-form-group wlsm-row wlsm-mb-2">
					<div class="wlsm-col-4">
						<label for="wlsm_message" class="wlsm-form-label wlsm-font-bold">
							<span class="wlsm-text-danger">*</span> <?php esc_html_e( 'Message', 'school-management-system' ); ?>:
						</label>
					</div>
					<div class="wlsm-col-8">
						<textarea name="message" class="wlsm-form-control" id="wlsm_message" cols="30" rows="4" placeholder="<?php esc_attr_e( 'Enter message', 'school-management-system' ); ?>"></textarea>
					</div>
				</div>
				<?php
				if ( get_option( 'wlsm_gdpr_enable' ) ) {
					?>
					<div class="wlsm-form-group wlsm-row wlsm-mb-2">
						<input type="checkbox" name="gdpr" id="wlsm_gdpr" value="1">
						<label class="wlsm-font-bold wlsm-inline-block wlsm-ml-1" for="wlsm_gdpr">
							<?php
							printf(
								wp_kses(
									__( 'I agree with GDPR compliant terms & conditions.', 'school-management-system' ),
									array(
										'span' => array( 'class' => array() ),
										'a'    => array(
											'href'  => array(),
											'class' => array(),
										),
									)
								)
							);
							?>
						</label>
					</div>
					<?php
				}
				?>
			</div>

			<div class="wlsm-border-top wlsm-pt-2 wlsm-mt-1">
				<button class="button wlsm-btn btn btn-primary" type="submit" id="wlsm-submit-inquiry-btn">
					<?php esc_html_e( 'Submit', 'school-management-system' ); ?>
				</button>
			</div>

		</form>

	</div>
</div>
<?php
return ob_get_clean();
