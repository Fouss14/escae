<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

$school           = WLSM_M_School::fetch_school( $school_id );
$settings_general = WLSM_M_Setting::get_settings_general( $school_id );
$school_logo      = $settings_general['school_logo'];
?>

<!-- School header -->
<div class="container-fluid">
	<div class="row wlsm-school-header justify-content-center">
		<div class="col-3 text-right">
			<?php if ( ! empty ( $school_logo ) ) { ?>
			<img src="<?php echo esc_url( wp_get_attachment_url( $school_logo ) ); ?>" class="wlsm-print-school-logo">
			<?php } ?>
		</div>
		<div class="col-9">
			<div class="wlsm-print-school-label">
				<?php echo esc_html( WLSM_M_School::get_label_text( $school->label ) ); ?>
			</div>
			<div class="wlsm-print-school-contact">
				<?php if ( $school->phone ) { ?>
				<span class="wlsm-print-school-phone">
					<span class="wlsm-font-bold">
						<?php esc_html_e( 'Phone:', 'school-management-system' ); ?>
					</span>
					<span><?php echo esc_html( WLSM_M_School::get_label_text( $school->phone ) ); ?></span>
				</span>
				<?php } ?>
				<?php if ( $school->email ) { ?>
				<span class="wlsm-print-school-email">
					<span class="wlsm-font-bold">
						| <?php esc_html_e( 'Email:', 'school-management-system' ); ?>
					</span>
					<span><?php echo esc_html( WLSM_M_School::get_phone_text( $school->email ) ); ?></span>
				</span>
				<br>
				<?php } ?>
				<?php if ( $school->address ) { ?>
				<span class="wlsm-print-school-address">
					<span class="wlsm-font-bold">
						<?php esc_html_e( 'Address:', 'school-management-system' ); ?>
					</span>
					<span><?php echo esc_html( WLSM_M_School::get_email_text( $school->address ) ); ?></span>
				</span>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
