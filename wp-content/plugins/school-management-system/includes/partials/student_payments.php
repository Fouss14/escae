<?php
defined( 'ABSPATH' ) || die();

if ( count( $payments ) ) {
?>
<!-- Student payments. -->
<div class="wlsm-table-section">
	<div class="wlsm-table-caption wlsm-font-bold">
		<?php
		printf(
			wp_kses(
				/* translators: %s: number of payments */
				_n( '%d payment found.', '%d payments found.', count( $payments ), 'school-management-system' ),
				array( 'span' => array( 'class' => array() ) )
			),
			count( $payments )
		);
		?>
	</div>

	<div class="table-responsive w-100 wlsm-w-100">
		<table class="table table-bordered wlsm-student-payments-table wlsm-w-100">
			<thead>
				<tr class="bg-primary text-white">
					<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
					<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Invoice', 'school-management-system' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $payments as $row ) {
				?>
				<tr>
					<td>
						<?php echo esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ); ?>
					</td>
					<td>
						<?php echo esc_html( WLSM_Config::get_money_text( $row->amount ) ); ?>
					</td>
					<td>
						<?php echo esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ); ?>
					</td>
					<td>
						<?php echo esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ); ?>
					</td>
					<td>
						<?php echo esc_html( WLSM_Config::get_date_text( $row->created_at ) ); ?>
					</td>
					<td>
						<?php
						if ( $row->invoice_id ) {
							echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_title ) );
						} else {
							echo '<span class="text-danger">' . esc_html__( 'Deleted', 'school-management-system' ) . '<br><span class="text-secondary">' . esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_label ) ) . '<br><small>' . esc_html( WLSM_Config::get_money_text( $row->invoice_payable ) )  . ' ' . esc_html__( 'Payable', 'school-management-system' ) . '</small></span></span>';
						}
						?>
					</td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
} else {
?>
<div class="wlsm-alert wlsm-alert-warning wlsm-font-bold">
	<span class="wlsm-icon wlsm-icon-red">&#33;</span>
	<?php esc_html_e( 'There is no payment.', 'school-management-system' ); ?>
</div>
<?php
}
