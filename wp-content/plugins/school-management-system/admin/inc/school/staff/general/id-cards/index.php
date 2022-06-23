<?php
defined( 'ABSPATH' ) || die();

$school_id = $current_school['id'];

$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-2 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-id-card"></i>
				<?php esc_html_e( 'Print ID Cards in Bulk', 'school-management-system' ); ?>
			</span>
		</div>
		<div class="wlsm-students-block">
			<div class="row">
				<div class="col-md-12">
					<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-print-bulk-id-cards-form" class="mb-3">
						<?php
							$nonce_action = 'print-id-cards';
						?>
						<?php $nonce = wp_create_nonce( $nonce_action ); ?>
						<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

						<input type="hidden" name="action" value="wlsm-print-bulk-id-cards">

						<div class="pt-2">
							<div class="row">
								<div class="col-md-8 mb-1">
									<div class="h6">
										<span class="text-secondary border-bottom">
										<?php esc_html_e( 'Search Students By Class', 'school-management-system' ); ?>
										</span>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-4">
									<label for="wlsm_class" class="wlsm-font-bold">
										<?php esc_html_e( 'Class', 'school-management-system' ); ?>:
									</label>
									<select name="class_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>" id="wlsm_class" data-live-search="true">
										<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
										<?php foreach ( $classes as $class ) { ?>
										<option value="<?php echo esc_attr( $class->ID ); ?>">
											<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
										</option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group col-md-4">
									<label for="wlsm_section" class="wlsm-font-bold">
										<?php esc_html_e( 'Section', 'school-management-system' ); ?>:
									</label>
									<select name="section_id" class="form-control selectpicker" id="wlsm_section" data-live-search="true" title="<?php esc_attr_e( 'All Sections', 'school-management-system' ); ?>" data-all-sections="1">
									</select>
								</div>
								<div class="form-group col-md-4">
									<label for="wlsm_only_active" class="wlsm-font-bold">
										<?php esc_html_e( 'Students', 'school-management-system' ); ?>:
									</label>
									<select name="only_active" class="form-control selectpicker" id="wlsm_only_active">
										<option value="1"><?php esc_html_e( 'Active Students', 'school-management-system' ); ?></option>
										<option value="0"><?php esc_html_e( 'All Students', 'school-management-system' ); ?></option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-row">
							<div class="col-md-12">
								<button type="submit" class="btn btn-sm btn-outline-primary" id="wlsm-print-bulk-id-cards-btn">
									<i class="fas fa-print"></i>&nbsp;
									<?php esc_html_e( 'Print ID Cards', 'school-management-system' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
