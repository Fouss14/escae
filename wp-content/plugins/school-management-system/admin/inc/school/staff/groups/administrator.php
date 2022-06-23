<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_roles     = admin_url('admin.php?page=' . WLSM_MENU_STAFF_ROLES);
$page_url_employees = admin_url('admin.php?page=' . WLSM_MENU_STAFF_EMPLOYEES);
global $wpdb;
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];


if ( WLSM_M_Role::check_permission( array( 'manage_roles' ), $current_school['permissions'] ) ) {
	// Total Roles.
	$total_roles_count = $wpdb->get_var( WLSM_M_Staff_General::fetch_role_query_count( $school_id ) );
}

if ( WLSM_M_Role::check_permission( array( 'manage_employees' ), $current_school['permissions'] ) ) {
	// Total Staff.
	$total_staff_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			WHERE sf.role = "%s" AND sf.school_id = %d', WLSM_M_Role::get_employee_key(), $school_id )
	);

	// Staff Active.
	$active_staff_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			WHERE sf.role = "%s" AND sf.school_id = %d AND a.is_active = 1', WLSM_M_Role::get_employee_key(), $school_id )
	);
}

?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-user-shield"></i>
					<?php esc_html_e('Administrator', 'school-management-system'); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-3">
		
		<?php if (WLSM_M_Role::check_permission(array('manage_roles'), $current_school['permissions'])) { ?>
			<div class="col-md-4 col-lg-4">
				<div class="wlsm-stats-block">
					<i class="fas fa-user-tag wlsm-stats-icon"></i>
					<div class="wlsm-stats-counter"><?php echo esc_html($total_roles_count); ?></div>
					<div class="wlsm-stats-label"><?php esc_html_e('Total Roles', 'school-management-system'); ?></div>
				</div>
			</div>
		<?php } ?>

		<?php if (WLSM_M_Role::check_permission(array('manage_employees'), $current_school['permissions'])) { ?>
			<div class="col-md-4 col-lg-4">
				<div class="wlsm-stats-block">
					<i class="fas fa-user-shield wlsm-stats-icon"></i>
					<div class="wlsm-stats-counter"><?php echo esc_html($total_staff_count); ?></div>
					<div class="wlsm-stats-label"><?php esc_html_e('Total Staff', 'school-management-system'); ?></div>
				</div>
			</div>

			<div class="col-md-4 col-lg-4">
				<div class="wlsm-stats-block">
					<i class="fas fa-user-shield wlsm-stats-icon"></i>
					<div class="wlsm-stats-counter"><?php echo esc_html($active_staff_count); ?></div>
					<div class="wlsm-stats-label"><?php esc_html_e('Staff Active', 'school-management-system'); ?></div>
				</div>
			</div>
		<?php } ?>
		</div>

		<div class="row mt-3 mb-3">
			<?php if (WLSM_M_Role::check_permission(array('manage_roles'), $current_school['permissions'])) { ?>
				<div class="col-md-4 col-sm-6">
					<div class="wlsm-group">
						<span class="wlsm-group-title"><?php esc_html_e('Roles', 'school-management-system'); ?></span>
						<div class="wlsm-group-actions">
							<a href="<?php echo esc_url($page_url_roles); ?>" class="btn btn-sm btn-primary">
								<?php esc_html_e('View Roles', 'school-management-system'); ?>
							</a>
							<a href="<?php echo esc_url($page_url_roles . '&action=save'); ?>" class="btn btn-sm btn-outline-primary">
								<?php esc_html_e('Add New Role', 'school-management-system'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php } ?>

			<?php if (WLSM_M_Role::check_permission(array('manage_employees'), $current_school['permissions'])) { ?>
				<div class="col-md-4 col-sm-6">
					<div class="wlsm-group">
						<span class="wlsm-group-title"><?php esc_html_e('Staff', 'school-management-system'); ?></span>
						<div class="wlsm-group-actions">
							<a href="<?php echo esc_url($page_url_employees); ?>" class="btn btn-sm btn-primary">
								<?php esc_html_e('View Staff', 'school-management-system'); ?>
							</a>
							<a href="<?php echo esc_url($page_url_employees . '&action=save'); ?>" class="btn btn-sm btn-outline-primary">
								<?php esc_html_e('Add New Staff', 'school-management-system'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	