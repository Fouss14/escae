<?php
defined( 'ABSPATH' ) || die();
?>
<div class="wlsm-main-header card col">
	<div class="card-header">
		<h1 class="h3 text-center">
			<i class="fas fa-school text-primary"></i>
			<?php echo esc_html( WLSM_M_School::get_label_text( $current_school['name'] ) ); ?>
			<small class="wlsm_text_secondary"><?php echo esc_html( $current_session['label'] ); ?></small>
		</h1>
		<?php if ( ! isset( $disallow_session_change ) ) { ?>
		<div class="text-center wlsm_user_current_session_block">
			<?php if ( $current_session['ID'] ) { ?>
			<label for="wlsm_user_current_session" class="text-dark">
				<?php esc_html_e( 'Current Session: ', 'school-management-system' ); ?>
			</label>
			<select name="current_session" id="wlsm_user_current_session">
				<?php foreach ( $current_session['sessions'] as $session ) { ?>
				<option <?php selected( $session->ID, $current_session['ID'], true ); ?> value="<?php echo esc_attr( $session->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'set-session-' . $session->ID ) ); ?>">
					<?php echo esc_html( $session->label ); ?>
				</option>
				<?php } ?>
			</select>
			<?php } else { ?>
			<span class="text-danger">
				<?php esc_html_e( 'Default session is not set. Please contact the administrator.', 'school-management-system' ); ?>
			</span>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
<?php
if ( ! $current_session['ID'] ) {
	die();
}
