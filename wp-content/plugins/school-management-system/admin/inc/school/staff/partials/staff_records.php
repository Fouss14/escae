<?php
defined( 'ABSPATH' ) || die();

/* translators: %s: role name */
$add_new_label = sprintf( esc_html__( 'Add New %s', 'school-management-system' ), esc_html( WLSM_M_Role::get_role_text( $role ) ) );
?>
<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-user-shield"></i>
				<?php echo esc_html( $table_heading ); ?>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-plus-square"></i>&nbsp;
					<?php echo esc_html( $add_new_label ); ?>
				</a>
			</span>
		</div>
		<div class="wlsm-table-block">
			<table class="table table-hover table-bordered" id="wlsm-staff-table" data-role="<?php echo esc_attr( $role ); ?>">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Salary', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Designation', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Role', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Login Email', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Login Username', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Joining Date', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
