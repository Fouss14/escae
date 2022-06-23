<?php
defined( 'ABSPATH' ) || die();
?>
<div class="wlsm-print-id-card-container">

	<?php require WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/school_header.php'; ?>

	<div class="row wlsm-print-id-card-details mt-1">
		<div class="col-9 wlsm-print-id-card-right">
			<ul>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student->student_name ) ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( $student->enrollment_number ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Session', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Session::get_label_text( $session_label ) ); ?></span>
				</li>
				<li>
					<span class="pr-3">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Class::get_label_text( $student->class_label ) ); ?></span>
					</span>
					<span class="pl-3">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Class::get_label_text( $student->section_label ) ); ?></span>
					</span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $student->roll_number ) ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Father Name', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student->father_name ) ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Phone', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $student->phone ) ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Email', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student->email ) ); ?></span>
				</li>
			</ul>
		</div>

		<div class="col-3 wlsm-print-id-card-left">
			<div class="wlsm-print-id-card-photo-box">
			<?php if ( ! empty ( $photo_id ) ) { ?>
				<img src="<?php echo esc_url( wp_get_attachment_url( $photo_id ) ); ?>" class="wlsm-print-id-card-photo">
			<?php } ?>
			</div>
			<div class="wlsm-print-id-card-authorized-by">
				<span><?php esc_html_e( 'Authorized By', 'school-management-system' ); ?></span>
			</div>
		</div>
	</div>

</div>
