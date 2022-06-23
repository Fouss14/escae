<?php
defined( 'ABSPATH' ) || die();

$action = '';
if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
	$action = sanitize_text_field( $_GET['action'] );
}
?>
<div class="wlsm-container wlsm-container-student">
<?php
if ( 'fee-invoices' == $action ) {
	wp_enqueue_script( 'stripe-checkout', '//checkout.stripe.com/checkout.js', array(), NULL, true );
	require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/fee_invoices.php';
} else if ( 'payment-history' == $action ) {
	require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/payment_history.php';
} else if ( 'noticeboard' == $action ) {
	require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/noticeboard.php';
} else {
	require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/dashboard.php';
}
?>
</div>
