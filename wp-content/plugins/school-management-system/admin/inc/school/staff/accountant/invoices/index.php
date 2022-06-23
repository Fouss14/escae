<?php
defined( 'ABSPATH' ) || die();

$page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

global $wpdb;

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$search_fields = WLSM_Helper::invoice_search_field_list();
$classes       = WLSM_M_Staff_Class::fetch_classes( $school_id );

// Total Payment Received.
$total_payment_received = WLSM_M_Staff_Accountant::get_total_payments_received( $school_id, $session_id );

// Invoices pending amount.
$invoices_pending_amount = $wpdb->get_col(
	$wpdb->prepare( 'SELECT ((i.amount - i.discount) - COALESCE(SUM(p.amount), 0)) as due FROM ' . WLSM_INVOICES . ' as i 
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id 
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID 
		WHERE cs.school_id = %d AND ss.ID = %d AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID', $school_id, $session_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key() )
);

$invoices_pending_amount = array_sum( $invoices_pending_amount );
?>
<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-file-invoice"></i>
				<?php esc_html_e( 'Student Fee Invoices', 'school-management-system' ); ?>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url . '&action=payment_history' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-file-invoice"></i>&nbsp;
					<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
				</a>&nbsp;
				<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-plus-square"></i>&nbsp;
					<?php esc_html_e( 'Add New Fee Invoice', 'school-management-system' ); ?>
				</a>
			</span>
		</div>
		<div class="wlsm-table-block">
			<div class="row">
				<div class="col-md-8">
					<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-get-invoices-form" class="mb-3">
						<?php
						$nonce_action = 'get-invoices';
						?>
						<?php $nonce = wp_create_nonce( $nonce_action ); ?>
						<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

						<input type="hidden" name="action" value="wlsm-get-invoices">

						<div class="row">
							<div class="col-md-12">
								<div class="h6 text-secondary wlsm-font-bold">
									<span class="border-bottom"><?php esc_html_e( 'Search Fee Invoices', 'school-management-system' ); ?></span>
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
										<?php esc_html_e( 'Search Fee Invoice By Keyword', 'school-management-system' ); ?>
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
										<?php esc_html_e( 'Search Fee Invoice By Class', 'school-management-system' ); ?>
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
								<button type="button" class="btn btn-sm btn-outline-primary" id="wlsm-get-invoices-btn">
									<i class="fas fa-file-invoice"></i>&nbsp;
									<?php esc_html_e( 'Get Invoices!', 'school-management-system' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
				<div class="col-md-4 wlsm-fee-invoices-amount-total-box">
					<div class="wlsm-fee-invoices-amount-total">
						<span class="wlsm-font-bold text-secondary">
							<?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?>
						</span>
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<span class="wlsm-font-bold">
									<?php esc_html_e( 'Payment Received', 'school-management-system' ); ?>:
								</span>
								<span><?php echo esc_html( WLSM_Config::get_money_text( $total_payment_received ) ); ?></span>
							</li>
							<li class="list-group-item">
								<span class="wlsm-font-bold">
									<?php esc_html_e( 'Amount Pending', 'school-management-system' ); ?>:
								</span>
								<span><?php echo esc_html( WLSM_Config::get_money_text( $invoices_pending_amount ) ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<table class="table table-hover table-bordered" id="wlsm-staff-invoices-table">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Invoice Number', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Invoice Title', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Payable', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Paid', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Due', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Date Issued', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Due Date', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
