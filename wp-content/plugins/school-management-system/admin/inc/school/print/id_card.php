<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}

$photo_id      = $student->photo_id;
$session_label = $student->session_label;
?>

<!-- Print ID card. -->
<div class="d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-id-card-btn" data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/print/wlsm-id-card.css' ); ?>"]' data-title="
												<?php
												printf(
												/* translators: 1: student name, 2: enrollment number */
													esc_attr__( 'ID Card - %1$s (%2$s)', 'school-management-system' ),
													esc_attr( WLSM_M_Staff_Class::get_name_text( $student->student_name ) ),
													esc_attr( $student->enrollment_number )
												);
												?>
		"><?php esc_html_e( 'Print ID Card', 'school-management-system' ); ?>
		</button>
	</div>
</div>

<!-- Print ID card section. -->
<div class="wlsm" id="wlsm-print-id-card">
<?php require WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/id_card.php'; ?>
</div>
