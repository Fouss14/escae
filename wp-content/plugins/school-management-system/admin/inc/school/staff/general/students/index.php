<?php
defined( 'ABSPATH' ) || die();

$school_id = $current_school['id'];

$search_fields = WLSM_Helper::search_field_list();
$classes       = WLSM_M_Staff_Class::fetch_classes( $school_id );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-2 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-users"></i>
				<?php esc_html_e( 'Students', 'school-management-system' ); ?>
			</span>
		</div>
		<div class="wlsm-students-block">
			<div class="row">
				<div class="col-md-8">
					<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-get-students-form" class="mb-3">
						<?php
						$nonce_action = 'get-students';
						?>
						<?php $nonce = wp_create_nonce( $nonce_action ); ?>
						<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

						<input type="hidden" name="action" value="wlsm-get-students">

						<div class="row">
							<div class="col-md-12">
								<div class="h6 text-secondary wlsm-font-bold">
									<span class="border-bottom"><?php esc_html_e( 'Search Students', 'school-management-system' ); ?></span>
								</div>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group col-md-12">
								<div class="form-check form-check-inline">
									<input checked class="form-check-input" type="radio" name="search_students_by" id="wlsm_search_by_keyword" value="search_by_keyword">
									<label class="ml-1 form-check-label wlsm-font-bold" for="wlsm_search_by_keyword">
										<?php esc_html_e( 'Search By Keyword', 'school-management-system' ); ?>
									</label>
								</div>
								<div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="search_students_by" id="wlsm_search_by_class" value="search_by_class">
									<label class="ml-1 form-check-label wlsm-font-bold" for="wlsm_search_by_class">
										<?php esc_html_e( 'Search By Class', 'school-management-system' ); ?>
									</label>
								</div>
							</div>
						</div>

						<div class="wlsm-search-students wlsm-search-keyword-students pt-2">
							<div class="row">
								<div class="col-md-8 mb-1">
									<div class="h6">
										<span class="text-secondary border-bottom">
										<?php esc_html_e( 'Search Student By Keyword', 'school-management-system' ); ?>
										</span>
									</div>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-4">
									<label for="wlsm_search_field" class="wlsm-font-bold">
										<?php esc_html_e( 'Search Field', 'school-management-system' ); ?>:
									</label>
									<select name="search_field" class="form-control selectpicker" id="wlsm_search_field" data-live-search="true">
										<option value=""><?php esc_html_e( 'Select Search Field', 'school-management-system' ); ?></option>
										<?php foreach ( $search_fields as $key => $value ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>">
											<?php echo esc_html( $value ); ?>
										</option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group col-md-4">
									<label for="wlsm_search_keyword" class="wlsm-font-bold">
										<?php esc_html_e( 'Keyword', 'school-management-system' ); ?>:
									</label>
									<input type="text" name="search_keyword" class="form-control" id="wlsm_search_keyword" placeholder="<?php esc_attr_e( 'Enter Search Keyword', 'school-management-system' ); ?>">
								</div>
							</div>
						</div>

						<div class="wlsm-search-students wlsm-search-class-students pt-2">
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
							</div>
						</div>

						<div class="form-row">
							<div class="col-md-12">
								<button type="button" class="btn btn-sm btn-outline-primary" id="wlsm-get-students-btn">
									<i class="fas fa-users"></i>&nbsp;
									<?php esc_html_e( 'Get Students!', 'school-management-system' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<table class="table table-hover table-bordered" id="wlsm-staff-students-table">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Father Name', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Father Phone', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Login Email', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Login Username', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Admission Date', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Session Records', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'ID Card', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
