<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

$page_url = WLSM_M_Staff_Class::get_subjects_page_url();

$school_id = $current_school['id'];

$subject = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id      = absint( $_GET['id'] );
	$subject = WLSM_M_Staff_Class::fetch_subject( $school_id, $id );
}

if ( ! $subject ) {
	die();
}

$nonce_action = 'assign-admins-' . $subject->ID;

$subject_name = $subject->subject_name;
$subject_code = $subject->code;
$subject_type = $subject->type;
$class_label  = $subject->class_label;
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<i class="fas fa-tag text-primary"></i>
					<?php
					printf(
						wp_kses(
							/* translators: 1: subject name, 2: subject code, 3: class label */
							__( 'Subject: %1$s (%2$s), Class: %3$s', 'school-management-system' ),
							array(
								'span' => array( 'class' => array() )
							)
						),
						esc_html( WLSM_M_Staff_Class::get_subject_label_text( $subject_name ) ),
						esc_html( WLSM_M_Staff_Class::get_subject_code_text( $subject_code ) ),
						esc_html( WLSM_M_Class::get_label_text( $class_label ) )
					);
					?>
				</span>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-tags"></i>&nbsp;
					<?php esc_html_e( 'View Subjects', 'school-management-system' ); ?>
				</a>
			</span>
		</div>

		<div class="mt-3 row">
			<div class="col-md-7">

				<h3 class="h5 border-bottom pb-2">
					<i class="fas fa-user-shield text-primary"></i>
					<?php esc_html_e( 'Teachers', 'school-management-system' ); ?>
				</h3>
				<table class="table table-hover table-bordered" id="wlsm-subject-admins-table" data-subject="<?php echo esc_attr( $subject->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'subject-admins-' . $subject->ID ) ); ?>">
					<thead>
						<tr class="text-white bg-primary">
							<th scope="col"><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Username', 'school-management-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
							<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
						</tr>
					</thead>
				</table>

			</div>
			<div class="col-md-5">

				<h3 class="h5 border-bottom pb-2">
					<i class="fas fa-plus-square text-primary"></i>
					<?php esc_html_e( 'Assign Teachers', 'school-management-system' ); ?>
				</h3>
				<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-assign-subject-admins-form">

					<?php $nonce = wp_create_nonce( $nonce_action ); ?>
					<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

					<input type="hidden" name="action" value="wlsm-assign-subject-admins">

					<input type="hidden" name="subject_id" value="<?php echo esc_attr( $subject->ID ); ?>">

					<div class="form-group">
						<label for="wlsm_admin_search" class="font-weight-bold"><?php esc_html_e( 'Teacher', 'school-management-system' ); ?>:</label>
						<input type="text" name="keyword" class="form-control" id="wlsm_admin_search" placeholder="<?php esc_attr_e( 'Type 1 or more characters... then click to select', 'school-management-system' ); ?>" autocomplete="off">
					</div>

					<div class="wlsm_subject_admins"></div>

					<div>
						<span class="float-right">
							<button type="submit" class="btn btn-sm btn-primary" id="wlsm-assign-subject-admins-btn">
								<i class="fas fa-save"></i>&nbsp;
								<?php esc_html_e( 'Assign Teachers', 'school-management-system' ); ?>
							</button>
						</span>
					</div>

				</form>

			</div>
		</div>
	</div>
</div>
