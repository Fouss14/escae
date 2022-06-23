<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_classes  = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_CLASSES );
$page_url_subjects = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_SUBJECTS );
$page_url_notices  = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_NOTICES );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-graduation-cap"></i>
					<?php esc_html_e( 'Academic', 'school-management-system' ); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-3">
		<?php if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $current_school['permissions'] ) ) { ?>
		<div class="col-sm-6 col-md-4">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Class Sections', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_classes ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Class Sections', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_subjects' ), $current_school['permissions'] ) ) { ?>
		<div class="col-sm-6 col-md-4">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Subjects', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_subjects ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Subjects', 'school-management-system' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_subjects ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New Subject', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_notices' ), $current_school['permissions'] ) ) { ?>
		<div class="col-sm-6 col-md-4">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Noticeboard', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_notices ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Noticeboard', 'school-management-system' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_notices . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New Notice', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
