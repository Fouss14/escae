<?php
defined( 'ABSPATH' ) || die();

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$nonce_action = 'promote-student-' . $session_id;

$promote_to_sessions = WLSM_M_Session::get_next_sessions( $current_session['ID'] );

$promote_to_sessions = array_filter( $promote_to_sessions, function( $session ) use ( $session_id ) {
	if ( $session->ID !== $session_id ) {
		return true;
	}
	return false;
});

$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-2 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-graduation-cap"></i>
				<?php esc_html_e( 'Student Promotion', 'school-management-system' ); ?>
			</span>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-promote-student-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="<?php echo esc_attr( 'wlsm-promote-student' ); ?>">

			<!-- Promote to Session & Class -->
			<div class="wlsm-form-section">
				<div class="alert alert-info">
					<strong><?php esc_html_e( 'Note: ', 'school-management-system' ); ?></strong>
					<?php esc_html_e( 'Promoting student from the present class to the next class will create an enrollment of that student for the next session.', 'school-management-system' ); ?>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Promotion', 'school-management-system' ); ?>
							<br>
							<small class="text-dark">
								<em><?php esc_html_e( 'Select class to promote, next session and new class.', 'school-management-system' ); ?></em>
							</small>
						</div>
					</div>
				</div>

				<div class="form-row mt-2">
					<div class="form-group col-md-6">
						<div class="wlsm-font-bold mb-2 pb-1"><?php esc_html_e( 'Current Session', 'school-management-system' ); ?>:</div>
						<div class="ml-1"><?php echo esc_html( $current_session['label'] ); ?></div>
					</div>

					<div class="form-group col-md-6">
						<label class="wlsm-font-bold" for="wlsm-promote-to-session">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Promote to Session', 'school-management-system' ); ?>:
						</label>
						<select name="promote_to_session" class="form-control selectpicker" id="wlsm-promote-to-session" data-live-search="true" title="<?php esc_attr_e( 'Select Next Session', 'school-management-system' ); ?>">
							<?php foreach ( $promote_to_sessions as $promote_to_session ) { ?>
							<option value="<?php echo esc_attr( $promote_to_session->ID ); ?>">
								<?php echo esc_html( $promote_to_session->label ); ?>
							</option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group col-md-6">
						<label for="wlsm_from_class" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Promotion From Class', 'school-management-system' ); ?>:
						</label>
						<select name="from_class" class="form-control selectpicker" id="wlsm_from_class" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php foreach ( $classes as $class ) { ?>
							<option value="<?php echo esc_attr( $class->ID ); ?>">
								<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
							</option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group col-md-6">
						<label for="wlsm_to_class" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Promotion To Class', 'school-management-system' ); ?>:
						</label>
						<select name="to_class" class="form-control selectpicker" id="wlsm_to_class" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php foreach ( $classes as $class ) { ?>
							<option value="<?php echo esc_attr( $class->ID ); ?>">
								<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
							</option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="button" class="btn btn-sm btn-primary" id="wlsm-manage-promotion-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'manage-promotion' ) ); ?>">
						<?php esc_html_e( 'Manage Promotion', 'school-management-system' ); ?>
					</button>
				</div>
			</div>

			<div class="wlsm-students-to-promote mt-2"></div>

		</form>
	</div>
</div>
