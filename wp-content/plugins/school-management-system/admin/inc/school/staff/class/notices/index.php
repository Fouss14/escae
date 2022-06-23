<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

$page_url = WLSM_M_Staff_Class::get_notices_page_url();
?>

<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-newspaper"></i>
				<?php esc_html_e( 'Noticeboard', 'school-management-system' ); ?>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-plus-square"></i>&nbsp;
					<?php esc_html_e( 'Add New Notice', 'school-management-system' ); ?>
				</a>
			</span>
		</div>
		<div class="wlsm-table-block">
			<table class="table table-hover table-bordered" id="wlsm-notices-table">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Notice', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Link To', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Is Active', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Added By', 'school-management-system' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management-system' ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
