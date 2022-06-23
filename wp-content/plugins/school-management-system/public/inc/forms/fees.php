<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';

if ( isset( $attr['session_id'] ) ) {
	$session_id = absint( $attr['session_id'] );
} else {
	$session_id = get_option( 'wlsm_current_session' );
}

$school_id = 1;

$school = WLSM_M_School::get_active_school( $school_id );
if ( ! $school ) {
	$invalid_message = esc_html__( 'School not found.', 'school-management-system' );
	return require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/partials/invalid.php';
}

$classes = WLSM_M_Staff_General::fetch_school_classes( $school_id );

$sessions = WLSM_M_Session::fetch_sessions();

$nonce_action = 'wlsm-get-pending-invoices';
?>
<div class="wlsm">
	<div id="wlsm-get-pending-invoices-students-section">

		<div class="wlsm-header-title wlsm-font-bold wlsm-mb-3">
			<span class="wlsm-border-bottom wlsm-pb-1">
				<?php esc_html_e( 'Online Fee Submission', 'school-management-system' ); ?>
			</span>
		</div>

		<?php $nonce = wp_create_nonce( $nonce_action ); ?>
		<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

		<input type="hidden" name="action" value="wlsm-p-get-pending-invoices-students">

		<input type="hidden" name="school_id" value="<?php echo esc_attr( $school_id ); ?>" id="wlsm_school">

		<div class="wlsm-form-group wlsm-row">
			<div class="wlsm-col-4">
				<label for="wlsm_session" class="wlsm-form-label wlsm-font-bold">
					<?php esc_html_e( 'Session', 'school-management-system' ); ?>:
				</label>
			</div>
			<div class="wlsm-col-8">
				<select name="session_id" class="wlsm-form-control" id="wlsm_session">
					<?php
					if ( isset( $sessions ) ) {
						foreach ( $sessions as $session ) {
						?>
						<option <?php selected( $session_id, $session->ID, true ); ?> value="<?php echo esc_attr( $session->ID ); ?>">
							<?php echo esc_html( WLSM_M_Session::get_label_text( $session->label ) ); ?>
						</option>
						<?php
						}
					}
					?>
				</select>
			</div>
		</div>

		<div class="wlsm-form-group wlsm-row">
			<div class="wlsm-col-4">
				<label for="wlsm_school_class" class="wlsm-form-label wlsm-font-bold">
					<?php esc_html_e( 'Class', 'school-management-system' ); ?>:
				</label>
			</div>
			<div class="wlsm-col-8">
				<select name="class_id" class="wlsm-form-control wlsm_school_class" id="wlsm_school_class" data-school="<?php echo esc_attr( $school_id ); ?>">
					<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
					<?php foreach ( $classes as $class ) { ?>
					<option value="<?php echo esc_attr( $class->ID ); ?>">
						<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
					</option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div class="wlsm-form-group wlsm-row">
			<div class="wlsm-col-4">
				<label for="wlsm_student_name" class="wlsm-form-label wlsm-font-bold">
					<?php esc_html_e( 'Name', 'school-management-system' ); ?>:
				</label>
			</div>
			<div class="wlsm-col-8">
				<input type="text" name="student_name" class="wlsm-form-control" id="wlsm_student_name" placeholder="<?php esc_attr_e( 'Enter student name', 'school-management-system' ); ?>">
			</div>
		</div>

		<div class="wlsm-border-top wlsm-pt-2 wlsm-mt-1">
			<button class="button wlsm-btn btn btn-primary" type="button" id="wlsm-get-pending-invoices-students-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-pending-invoices-students' ) ); ?>">
				<?php esc_html_e( 'Search', 'school-management-system' ); ?>
			</button>
		</div>

		<div class="wlsm-students-with-pending-invoices wlsm-mt-2"></div>

	</div>
</div>
<?php
return ob_get_clean();
