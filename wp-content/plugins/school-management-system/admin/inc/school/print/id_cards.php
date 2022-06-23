<?php
defined( 'ABSPATH' ) || die();

if ( ! count( $students ) ) {
	?>
	<div class="text-center">
		<span class="text-danger wlsm-font-bold">
			<?php esc_html_e( 'No student found.', 'school-management-system' ); ?>
		</span>
	</div>
	<?php
	return;
}

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}
?>

<!-- Print ID cards. -->
<div class="d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-id-cards-btn" data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/print/wlsm-id-cards.css' ); ?>"]' data-title="
												<?php
												printf(
												/* translators: 1: class label, 2: section label */
													esc_attr__( 'ID Cards - Class: %1$s, Section: (%2$s)', 'school-management-system' ),
													esc_attr( $class_label ),
													esc_attr( $section_label )
												);
												?>
		"><?php esc_html_e( 'Print ID Cards', 'school-management-system' ); ?>
		</button>
	</div>
</div>

<!-- Print ID cards section. -->
<div class="wlsm" id="wlsm-print-id-cards">
	<div class="wlsm-print-id-cards-container">
		<!-- Print ID cards section. -->
		<?php
		foreach ( $students as $student ) {
			$photo_id = $student->photo_id;
			?>
		<!-- Print ID card section. -->
		<div class="wlsm wlsm-print-id-card">
			<?php require WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/id_card.php'; ?>
		</div>
			<?php
		}
		?>
	</div>
</div>
