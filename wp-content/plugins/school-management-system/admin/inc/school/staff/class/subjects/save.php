<?php
defined( 'ABSPATH' ) || die();

global $wpdb;

$page_url = WLSM_M_Staff_Class::get_subjects_page_url();

$school_id = $current_school['id'];

$subject = NULL;

$nonce_action = 'add-subject';

$subject_name  = '';
$subject_code  = '';
$subject_type  = '';

$class_id = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id      = absint( $_GET['id'] );
	$subject = WLSM_M_Staff_Class::fetch_subject( $school_id, $id );

	if ( $subject ) {
		$nonce_action = 'edit-subject-' . $subject->ID;

		$subject_name = $subject->subject_name;
		$subject_code = $subject->code;
		$subject_type = $subject->type;

		$class_id = $subject->class_id;
	}
}

$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );

$subject_types = WLSM_Helper::subject_type_list();
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ( $subject ) {
						printf(
							wp_kses(
								/* translators: 1: subject name, 2: subject code */
								__( 'Edit Subject: %1$s (%2$s)', 'school-management-system' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( WLSM_M_Staff_Class::get_subject_label_text( $subject_name ) ),
							esc_html( $subject_code )
						);
					} else {
						esc_html_e( 'Add New Subject', 'school-management-system' );
					}
					?>
				</span>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-tags"></i>&nbsp;
					<?php esc_html_e( 'View All', 'school-management-system' ); ?>
				</a>
			</span>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-subject-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wlsm-save-subject">

			<?php if ( $subject ) { ?>
			<input type="hidden" name="subject_id" value="<?php echo esc_attr( $subject->ID ); ?>">
			<?php } ?>

			<div class="wlsm-form-section">
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="wlsm_label" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Subject Name', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e( 'Enter subject name', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripslashes( $subject_name ) ); ?>">
					</div>
					<div class="form-group col-md-6">
						<label for="wlsm_code" class="wlsm-font-bold">
							<?php esc_html_e( 'Subject Code', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="code" class="form-control" id="wlsm_code" placeholder="<?php esc_attr_e( 'Enter subject code', 'school-management-system' ); ?>" value="<?php echo esc_attr( $subject_code ); ?>">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="wlsm_type" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Subject Type', 'school-management-system' ); ?>:
						</label>
						<select name="type" class="form-control selectpicker" id="wlsm_type">
							<?php foreach ( $subject_types as $key => $value ) { ?>
							<option <?php selected( $subject_type, $key, true ); ?> value="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $value ); ?>
							</option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group col-md-6">
						<label for="wlsm_class_id" class="wlsm-font-bold">
							<?php esc_html_e( 'Class', 'school-management-system' ); ?>:
						</label>
						<select name="class_id" class="form-control selectpicker" id="wlsm_class_id" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php foreach ( $classes as $class ) { ?>
							<option <?php selected( $class_id, $class->ID, true ); ?> value="<?php echo esc_attr( $class->ID ); ?>">
								<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
							</option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-save-subject-btn">
						<?php
						if ( $subject ) {
							?>
							<i class="fas fa-save"></i>&nbsp;
							<?php
							esc_html_e( 'Update Subject', 'school-management-system' );
						} else {
							?>
							<i class="fas fa-plus-square"></i>&nbsp;
							<?php
							esc_html_e( 'Add New Subject', 'school-management-system' );
						}
						?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
