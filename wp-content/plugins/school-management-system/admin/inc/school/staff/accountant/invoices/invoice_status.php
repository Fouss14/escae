<?php
defined( 'ABSPATH' ) || die();

$due = $invoice->payable - $invoice->paid;
?>

<!-- Student Detail -->
<div class="wlsm-form-section">
	<div class="row">
		<div class="col-md-4">
			<div class="wlsm-form-sub-heading wlsm-font-bold">
				<?php esc_html_e( 'Student Detail', 'school-management-system' ); ?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student_name ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_admission_no_text( $admission_number ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Class::get_label_text( $class_label ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $section_label ) ); ?></span>
				</li>					
			</ul>
		</div>
		<div class="col-md-6">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Phone', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $phone ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Email', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $email ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Father Name', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $father_name ) ); ?></span>
				</li>
				<li class="list-group-item">
					<span class="wlsm-font-bold"><?php esc_html_e( 'Father Phone', 'school-management-system' ); ?>:</span>
					<span><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $father_phone ) ); ?></span>
				</li>
			</ul>
		</div>
	</div>
</div>

<!-- Invoice Status -->
<div class="wlsm-fee-invoice-status-box">
	<div class="wlsm-form-section wlsm-fee-invoice-status" id="wlsm-fee-invoice-status">
		<div class="row">
			<div class="col-md-4">
				<div class="wlsm-form-sub-heading wlsm-font-bold">
					<?php esc_html_e( 'Invoice Status', 'school-management-system' ); ?>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Payable', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_Config::get_money_text( $invoice->payable ) ); ?></span>
					</li>
					<li class="list-group-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Paid', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_Config::get_money_text( $invoice->paid ) ); ?></span>
					</li>
				</ul>
			</div>
			<div class="col-md-6">
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Due', 'school-management-system' ); ?>:</span>
						<span class="wlsm-font-bold"><?php echo esc_html( WLSM_Config::get_money_text( $due ) ); ?></span>
					</li>
					<li class="list-group-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Status', 'school-management-system' ); ?>:</span>
						<span>
							<?php
							echo wp_kses(
									WLSM_M_Invoice::get_status_text( $invoice->status ),
									array( 'span' => array( 'class' => array() ) )
								);
							if ( WLSM_M_Invoice::get_paid_key() !== $invoice->status ) {
							?>
							<?php if ( isset( $show_collect_payment_link ) ) { ?>
							<div class="mt-1">
								<a href="<?php echo esc_url( $page_url . '&action=collect_payment&id=' . $invoice->ID . '#wlsm-fee-invoice-status' ); ?>" class="btn btn-sm btn-success">
									<?php esc_html_e( 'Collect Payment', 'school-management-system' ); ?>
								</a>
							</div>
							<?php } ?>
							<?php
							}
							?>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
