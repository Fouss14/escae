<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';

global $wpdb;

$page_url = WLSM_M_Staff_General::get_roles_page_url();

$school_id = $current_school['id'];

$permission_list = WLSM_M_Role::get_permissions( array( 'manage_admins' ) );

$role = NULL;

$nonce_action = 'add-role';

$name        = '';
$permissions = array();

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id   = absint( $_GET['id'] );
	$role = WLSM_M_Staff_General::fetch_role( $school_id, $id );

	if ( $role ) {
		$nonce_action = 'edit-role-' . $role->ID;

		$name = $role->name;

		if ( $role->permissions ) {
			$permissions = $role->permissions;
			if ( is_serialized( $permissions ) ) {
				$permissions = unserialize( $permissions );
			}
		}
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ( $role ) {
						printf(
							wp_kses(
								/* translators: %s: role name */
								__( 'Edit Role: %s', 'school-management-system' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( $name )
						);
					} else {
						esc_html_e( 'Add New Role', 'school-management-system' );
					}
					?>
				</span>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-user-tag"></i>&nbsp;
					<?php esc_html_e( 'View All', 'school-management-system' ); ?>
				</a>
			</span>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-role-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wlsm-save-role">

			<?php if ( $role ) { ?>
			<input type="hidden" name="role_id" value="<?php echo esc_attr( $role->ID ); ?>">
			<?php } ?>

			<!-- Role -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-4">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Role', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-6">
						<input type="text" name="name" class="form-control" id="wlsm_name" placeholder="<?php esc_attr_e( 'Enter role name', 'school-management-system' ); ?>" value="<?php echo esc_attr( $name ); ?>">
					</div>
				</div>
			</div>

			<!-- Permissions -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Permissions', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<?php foreach ( $permission_list as $key => $value ) { ?>
					<div class="form-group col-md-4">
						<input <?php checked( in_array( $key, $permissions ), true, true ); ?> class="form-check-input mt-1" type="checkbox" name="permission[]" id="wlsm_role_permission_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>">
						&nbsp;
						<label class="ml-4 mb-1 form-check-label wlsm-font-bold" for="wlsm_role_permission_<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $value ); ?>
						</label>
					</div>
					<?php } ?>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-save-role-btn">
						<?php
						if ( $role ) {
							?>
							<i class="fas fa-save"></i>&nbsp;
							<?php
							esc_html_e( 'Update Role', 'school-management-system' );
						} else {
							?>
							<i class="fas fa-plus-square"></i>&nbsp;
							<?php
							esc_html_e( 'Add New Role', 'school-management-system' );
						}
						?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
