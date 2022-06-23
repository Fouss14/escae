<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';

global $wpdb;

$school_ids = array();
foreach ( $schools_assigned as $school ) {
	array_push( $school_ids, $school['id'] );
}

$school_ids_string = implode( ',', $school_ids );

$schools = $wpdb->get_results( 'SELECT s.ID, s.label, s.phone, s.is_active, COUNT(DISTINCT cs.ID) as classes_count FROM ' . WLSM_SCHOOLS . ' as s LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.school_id = s.ID WHERE s.ID IN (' . sanitize_text_field( $school_ids_string ) . ') GROUP BY s.ID' );
?>
<div class="wlsm container-fluid">
	<div class="card col">
		<div class="card-header">
			<h1 class="h3 text-center">
				<i class="fas fa-school text-primary"></i>
				<?php esc_html_e( 'Dashboard', 'school-management-system' ); ?>
			</h1>
		</div>
	</div>
	<?php
	if ( count( $schools ) ) {
	?>
	<div class="row justify-content-md-center">
		<?php
		foreach ( $schools as $school ) {
		?>
		<div class="col-sm-10 col-md-6">
			<a href="javascript:void(0)" class="wlsm-staff-school-card-link" data-school="<?php echo esc_attr( $school->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'set-school-' . $school->ID ) ); ?>">
				<div class="py-2 px-1 wlsm-school-card <?php if ( $school->ID === $current_school['id'] ) { echo esc_attr('wlsm-school-card-border'); } ?>">
					<div class="card-body">
						<h6 class="card-title wlsm-school-card-title wlsm-school-card-dark"><?php echo esc_html( WLSM_M_School::get_label_text( $school->label ) ); ?></h6>
						<ul>
							<li>
								<span class="wlsm-school-card-light wlsm-font-bold"><?php esc_html_e( 'Phone:', 'school-management-system' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( $school->phone ); ?></span>
							</li>
							<li>
								<span class="wlsm-school-card-light wlsm-font-bold"><?php esc_html_e( 'Total Classes:', 'school-management-system' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( $school->classes_count ); ?></span>
							</li>
							<li>
								<span class="wlsm-school-card-light wlsm-font-bold"><?php esc_html_e( 'Status:', 'school-management-system' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( WLSM_M_School::get_status_text( $school->is_active ) ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</a>
		</div>
		<?php
		}
		?>
	</div>
	<?php
	}
	?>
</div>
