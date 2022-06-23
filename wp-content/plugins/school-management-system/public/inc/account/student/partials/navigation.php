<?php
defined( 'ABSPATH' ) || die();
?>
<ul class="wlsm-navigation-links">
	<li>
		<a class="wlsm-navigation-link<?php if ( '' == $action ) { echo ' active'; } ?>" href="<?php echo esc_url( add_query_arg( array(), $current_page_url ) ); ?>"><?php esc_html_e( 'Dashboard', 'school-management-system' ); ?></a>
	</li>
	<li>
		<a class="wlsm-navigation-link<?php if ( 'fee-invoices' == $action ) { echo ' active'; } ?>" href="<?php echo esc_url( add_query_arg( array( 'action' => 'fee-invoices' ), $current_page_url ) ); ?>"><?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?></a>
	</li>
	<li>
		<a class="wlsm-navigation-link<?php if ( 'payment-history' == $action ) { echo ' active'; } ?>" href="<?php echo esc_url( add_query_arg( array( 'action' => 'payment-history' ), $current_page_url ) ); ?>"><?php esc_html_e( 'Payment History', 'school-management-system' ); ?></a>
	</li>
	<li>
		<a class="wlsm-navigation-link<?php if ( 'noticeboard' == $action ) { echo ' active'; } ?>" href="<?php echo esc_url( add_query_arg( array( 'action' => 'noticeboard' ), $current_page_url ) ); ?>"><?php esc_html_e( 'Noticeboard', 'school-management-system' ); ?></a>
	</li>
</ul>
