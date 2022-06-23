<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';

global $wpdb;

$page_url = WLSM_M_Class::get_page_url();

$class = NULL;

$nonce_action = 'add-class';

$label = '';

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id    = absint( $_GET['id'] );
	$class = WLSM_M_Class::fetch_class( $id );

	if ( $class ) {
		$nonce_action = 'edit-class-' . $class->ID;

		$label = $class->label;
	}
}
?>
<div class="wlsm">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-main-header card col wlsm-page-heading-box">
					<h1 class="h3 text-center wlsm-page-heading">
					<?php if ( $class ) { ?>
						<i class="fas fa-edit text-primary"></i>
						<?php
						printf(
							wp_kses(
								/* translators: %s: class name */
								__( 'Edit Class: <span class="text-secondary">%s</span>', 'school-management-system' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( WLSM_M_Class::get_label_text( $label ) )
						);
						?>
					<?php } else { ?>
						<i class="fas fa-plus-square text-primary"></i>
						<?php esc_html_e( 'Add New Class', 'school-management-system' ); ?>
					<?php } ?>
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
						<span class="float-right">
							<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-info">
								<i class="fas fa-layer-group"></i>&nbsp;
								<?php esc_html_e( 'View All', 'school-management-system' ); ?>
							</a>
						</span>
					</div>
					<div class="card-body">
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-class-form">

							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-class">

							<?php if ( $class ) { ?>
							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class->ID ); ?>">
							<?php } ?>

							<div class="form-group">
								<label for="wlsm_label" class="font-weight-bold"><span class="wlsm-important">*</span> <?php esc_html_e( 'Class', 'school-management-system' ); ?>:</label>
								<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e( 'Enter class label', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_M_Class::get_label_text( $label ) ); ?>">
							</div>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-sm btn-primary" id="wlsm-save-class-btn">
										<?php
										if ( $class ) {
											?>
											<i class="fas fa-save"></i>&nbsp;
											<?php
											esc_html_e( 'Update Class', 'school-management-system' );
										} else {
											?>
											<i class="fas fa-plus-square"></i>&nbsp;
											<?php
											esc_html_e( 'Add New Class', 'school-management-system' );
										}
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
