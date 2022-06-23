<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_admissions = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ADMISSIONS );
$page_url_students   = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_STUDENTS );
$page_url_id_cards   = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ID_CARDS );
$page_url_promote    = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_PROMOTE );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-users"></i>
					<?php esc_html_e( 'Student', 'school-management-system' ); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-3">
		<?php if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $current_school['permissions'] ) ) { ?>
		<div class="col-md-4 col-sm-6">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Admission', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_admissions ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Add New Admission', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $current_school['permissions'] ) ) { ?>
		<div class="col-md-4 col-sm-6">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Students', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_students ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Students', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="col-md-4 col-sm-6">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'ID Cards', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_id_cards ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Print ID Cards', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_promote' ), $current_school['permissions'] ) ) { ?>
		<div class="col-sm-6 col-md-4">
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Student Promotion', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_promote ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Promote Students', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
