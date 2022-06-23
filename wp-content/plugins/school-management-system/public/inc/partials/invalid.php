<?php
defined( 'ABSPATH' ) || die();
?>

<div class="wlsm-error-message-box">
	<span class="wlsm-error-message wlsm-font-large wlsm-font-bold">
		<?php echo esc_html( $invalid_message ); ?>
	</span>
</div>

<?php
return ob_get_clean();
