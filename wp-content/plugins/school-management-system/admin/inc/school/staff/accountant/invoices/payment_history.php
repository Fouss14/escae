<?php
defined( 'ABSPATH' ) || die();

$page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );
?>

<!-- Payment History -->
<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-file-invoice"></i>
				<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-plus-square"></i>&nbsp;
					<?php esc_html_e( 'Add New Fee Invoice', 'school-management-system' ); ?>
				</a>&nbsp;
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-file-invoice"></i>&nbsp;
					<?php esc_html_e( 'View Invoices', 'school-management-system' ); ?>
				</a>
			</span>
		</div>
		<div class="wlsm-table-block">
			<table class="table table-hover table-bordered" id="wlsm-payments-table">
				<thead>
					<tr class="text-white bg-primary">
						<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
						<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Note', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Invoice', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Fahter Name', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Fahter Phone', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Print', 'school-management-system' ); ?></th>
						<?php if ( $can_delete_payments ) { ?>
						<th class="text-nowrap"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></th>
						<?php } ?>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
