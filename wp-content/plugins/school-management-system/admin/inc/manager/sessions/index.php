<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';

$page_url = WLSM_M_Session::get_page_url();
?>
<div class="wlsm container-fluid">
	<div class="row">
		<div class="wlsm-main-header card col">
			<div class="card-header">
				<h1 class="h3 text-center">
					<i class="fas fa-calendar-alt text-primary"></i>
					<?php esc_html_e( 'Sessions', 'school-management-system' ); ?>
				</h1>
				<div class="float-right">
					<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-primary">
						<i class="fas fa-plus-square"></i>&nbsp;
						<?php esc_html_e( 'Add New Session', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
			<div class="card-body">
				<table class="table table-hover table-bordered" id="wlsm-sessions-table">
					<thead>
						<tr class="text-white bg-primary">
							<th scope="col"><?php esc_html_e( 'Session', 'school-management-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Start Date', 'school-management-system' ); ?></th>
							<th scope="col"><?php esc_html_e( 'End Date', 'school-management-system' ); ?></th>
							<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>
