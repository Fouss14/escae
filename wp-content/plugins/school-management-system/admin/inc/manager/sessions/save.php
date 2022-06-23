<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';

global $wpdb;

$page_url = WLSM_M_Session::get_page_url();

$session = NULL;

$nonce_action = 'add-session';

$label      = '';
$start_date = '';
$end_date   = '';

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id      = absint( $_GET['id'] );
	$session = WLSM_M_Session::fetch_session( $id );

	if ( $session ) {
		$nonce_action = 'edit-session-' . $session->ID;

		$label      = $session->label;
		$start_date = $session->start_date;
		$end_date   = $session->end_date;
	}
}
?>
<div class="wlsm">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-main-header card col wlsm-page-heading-box">
					<h1 class="h3 text-center wlsm-page-heading">
					<?php if ( $session ) { ?>
						<i class="fas fa-edit text-primary"></i>
						<?php
						printf(
							wp_kses(
								/* translators: %s: session */
								__( 'Edit Session: <span class="text-secondary">%s</span>', 'school-management-system' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( WLSM_M_Session::get_label_text( $label ) . ' (' . WLSM_Config::get_date_text( $start_date ) . ' - ' . WLSM_Config::get_date_text( $end_date ) . ')' )
						);
						?>
					<?php } else { ?>
						<i class="fas fa-plus-square text-primary"></i>
						<?php esc_html_e( 'Add New Session', 'school-management-system' ); ?>
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
								<i class="fas fa-calendar-alt"></i>&nbsp;
								<?php esc_html_e( 'View All', 'school-management-system' ); ?>
							</a>
						</span>
					</div>
					<div class="card-body">
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-session-form">

							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-session">

							<?php if ( $session ) { ?>
							<input type="hidden" name="session_id" value="<?php echo esc_attr( $session->ID ); ?>">
							<?php } ?>

							<div class="form-group">
								<label for="wlsm_label" class="font-weight-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e( 'Session', 'school-management-system' ); ?>:
								</label>
								<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e( 'Enter label', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_M_Session::get_label_text( $label ) ); ?>">
							</div>

							<div class="form-group">
								<label for="wlsm_start_date" class="font-weight-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e( 'Start Date', 'school-management-system' ); ?>:
								</label>
								<input type="text" name="start_date" class="form-control" id="wlsm_start_date" placeholder="<?php esc_attr_e( 'Enter start date', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $start_date ) ); ?>">
							</div>

							<div class="form-group">
								<label for="wlsm_end_date" class="font-weight-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e( 'End Date', 'school-management-system' ); ?>:
								</label>
								<input type="text" name="end_date" class="form-control" id="wlsm_end_date" placeholder="<?php esc_attr_e( 'Enter end date', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $end_date ) ); ?>">
							</div>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-sm btn-primary" id="wlsm-save-session-btn">
										<?php
										if ( $session ) {
											?>
											<i class="fas fa-save"></i>&nbsp;
											<?php
											esc_html_e( 'Update Session', 'school-management-system' );
										} else {
											?>
											<i class="fas fa-plus-square"></i>&nbsp;
											<?php
											esc_html_e( 'Add New Session', 'school-management-system' );
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
