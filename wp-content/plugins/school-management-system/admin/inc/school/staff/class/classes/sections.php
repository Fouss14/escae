<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

global $wpdb;

$page_url = WLSM_M_Staff_Class::get_sections_page_url();

$school_id = $current_school['id'];

$class = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$class_id = absint( $_GET['id'] );
	$class    = WLSM_M_Staff_Class::fetch_class( $school_id, $class_id );
}

if ( ! $class ) {
	die();
}

$section = NULL;

$nonce_action = 'add-section';

$section_id = NULL;

$label = '';


if ( isset( $_GET['section_id'] ) && ! empty( $_GET['section_id'] ) ) {
	$section_id = absint( $_GET['section_id'] );
	$section    = WLSM_M_Staff_Class::fetch_section( $school_id, $section_id, $class->ID );

	if ( $section ) {
		$nonce_action = 'edit-section-' . $section->ID;

		$section_id = $section->ID;

		$label = $section->label;
	}
}
?>
<div class="row justify-content-md-center">
	<div class="col-md-12">
		<div class="card col">
			<div class="card-header">
				<span class="float-left h5">
					<?php
					printf(
						wp_kses(
							/* translators: %s: class label */
							__( '<span class="text-secondary">Class:</span> %s', 'school-management-system' ),
							array(
								'span' => array( 'class' => array() )
							)
						),
						$class->label
					);
					?>
				</span>
				<span class="float-right">
					<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-info">
						<i class="fas fa-layer-group"></i>&nbsp;
						<?php esc_html_e( 'View Classes', 'school-management-system' ); ?>
					</a>
				</span>
			</div>
			<div class="card-body">
				<div class="row<?php if ( $section ) { echo esc_attr(' justify-content-md-center' ); } ?>">
					<?php if ( ! $section ) { ?>
					<div class="col-md-7">
						<h2 class="h4 border-bottom pb-2">
							<i class="fas fa-layer-group text-primary"></i>
							<?php esc_html_e( 'Class Sections', 'school-management-system' ); ?>
						</h2>
						<table class="table table-hover table-bordered" id="wlsm-class-sections-table" data-class-school="<?php echo esc_attr( $class->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'class-sections-' . $class->ID ) ); ?>">
							<thead>
								<tr class="text-white bg-primary">
									<th scope="col"><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Total Students', 'school-management-system' ); ?></th>
									<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
								</tr>
							</thead>
						</table>
					</div>
					<?php } ?>
					<div class="col-md-5">
						<div class="wlsm-page-heading-box">
							<h2 class="h4 border-bottom pb-2 wlsm-page-heading">
								<?php if ( $section ) { ?>
								<i class="fas fa-edit text-primary"></i>
								<?php
								printf(
									wp_kses(
										/* translators: %s: section label */
										__( 'Edit Section: <span class="text-secondary">%s</span>', 'school-management-system' ),
										array(
											'span' => array( 'class' => array() )
										)
									),
									esc_html( WLSM_M_Staff_Class::get_section_label_text( $label ) )
								);
								?>
								<a href="<?php echo esc_url( $page_url . '&action=sections&id=' . $class->class_id ); ?>" class="float-right btn btn-sm btn-outline-primary">
									<i class="fas fa-plus-square"></i>
									<?php esc_html_e( 'Add New Section', 'school-management-system' ); ?>
								</a>
								<?php } else { ?>
								<i class="fas fa-plus-square text-primary"></i>
								<?php esc_html_e( 'Add New Section', 'school-management-system' ); ?>
								<?php } ?>
							</h2>
						</div>
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-section-form">

							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-section">

							<?php if ( $section ) { ?>
							<input type="hidden" name="section_id" value="<?php echo esc_attr( $section->ID ); ?>">
							<?php } ?>

							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class->class_id ); ?>">

							<div class="form-group">
								<label for="wlsm_section_label" class="font-weight-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?>:</label>
								<input type="text" name="label" class="form-control" id="wlsm_section_label" placeholder="<?php esc_attr_e( 'Enter section', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_M_Staff_Class::get_section_label_text( $label ) ); ?>">
							</div>

							<?php if ( $section && ( $section_id == $class->default_section_id ) ) { ?>
							<div class="text-secondary mb-1">
								<?php esc_html_e( 'Currently set to default section.', 'school-management-system' ); ?>
							</div>
							<?php } else { ?>
							<div class="form-group">
								<input class="form-check-input mt-1" type="checkbox" name="is_default" id="wlsm_is_default" value="1">
								<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-dark" for="wlsm_is_default">
									<?php esc_html_e( 'Set as default section?', 'school-management-system' ); ?>
								</label>
							</div>
							<?php } ?>

							<div>
								<span class="float-right">
									<button type="submit" class="btn btn-sm btn-primary" id="wlsm-save-section-btn">
										<?php
										if ( $section ) {
											?>
											<i class="fas fa-save"></i>&nbsp;
											<?php
											esc_html_e( 'Update Section', 'school-management-system' );
										} else {
											?>
											<i class="fas fa-plus-square"></i>&nbsp;
											<?php
											esc_html_e( 'Add New Section', 'school-management-system' );
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
