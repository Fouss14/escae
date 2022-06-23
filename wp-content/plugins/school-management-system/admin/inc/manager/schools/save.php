<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';

global $wpdb;

$page_url = WLSM_M_School::get_page_url();

$school = NULL;

$label     = '';
$phone     = '';
$email     = '';
$address   = '';
$is_active = 1;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id     = absint( $_GET['id'] );
	$school = WLSM_M_School::fetch_school( $id );

	if ( $school ) {
		$nonce_action = 'edit-school-' . $school->ID;

		$label     = $school->label;
		$phone     = $school->phone;
		$email     = $school->email;
		$address   = $school->address;
		$is_active = $school->is_active;
	}
}

if ( ! $school ) {
	die;
}
?>
<div class="wlsm">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-main-header card col wlsm-page-heading-box">
					<h1 class="h3 text-center wlsm-page-heading">
						<i class="fas fa-edit text-primary"></i>
						<?php
						printf(
							wp_kses(
								/* translators: %s: school name */
								__( 'Edit School: <span class="text-secondary">%s</span>', 'school-management-system' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( WLSM_M_School::get_label_text( $label ) )
						);
						?>
					</h1>
				</div>
			</div>
		</div>
		<div class="row justify-content-md-center">
			<div class="col-md-8">
				<div class="card col">
					<div class="card-header">
						<span class="h6 float-left">
							<?php echo wp_kses( __( 'Fill all the required fields (<span class="wlsm-important">*</span>).', 'school-management-system' ), array( 'span' => array( 'class' => array() ) ) ); ?>
						</span>
					</div>
					<div class="card-body">
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-school-form">

							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-school">

							<input type="hidden" name="school_id" value="<?php echo esc_attr( $school->ID ); ?>">

							<div class="form-group">
								<label for="wlsm_label" class="font-weight-bold"><span class="wlsm-important">*</span> <?php esc_html_e( 'School Name', 'school-management-system' ); ?>:</label>
								<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e( 'Enter school name', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_M_School::get_label_text( $label ) ); ?>">
							</div>

							<div class="form-group">
								<label for="wlsm_phone" class="font-weight-bold"><?php esc_html_e( 'Phone', 'school-management-system' ); ?>:</label>
								<input type="text" name="phone" class="form-control" id="wlsm_phone" placeholder="<?php esc_attr_e( 'Enter school phone number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $phone ); ?>">
							</div>

							<div class="form-group">
								<label for="wlsm_email" class="font-weight-bold"><?php esc_html_e( 'Email', 'school-management-system' ); ?>:</label>
								<input type="email" name="email" class="form-control" id="wlsm_email" placeholder="<?php esc_attr_e( 'Enter school email', 'school-management-system' ); ?>" value="<?php echo esc_attr( $email ); ?>">
							</div>

							<div class="form-group">
								<label for="wlsm_address" class="font-weight-bold"><?php esc_html_e( 'Address', 'school-management-system' ); ?>:</label>
								<textarea name="address" class="form-control" id="wlsm_address" rows="3" placeholder="<?php esc_attr_e( 'Enter school address', 'school-management-system' ); ?>"><?php echo esc_html( $address ); ?></textarea>
							</div>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-sm btn-primary" id="wlsm-save-school-btn">
										<i class="fas fa-save"></i>&nbsp;
										<?php
										esc_html_e( 'Update School', 'school-management-system' );
										?>
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
