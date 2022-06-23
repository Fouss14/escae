<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_invoices = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_INVOICES );

global $wpdb;

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$schools_page_url  = WLSM_M_School::get_page_url();
$students_page_url = WLSM_M_Staff_General::get_students_page_url();
$invoices_page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $current_school['permissions'] ) ) {
	// Total Invoices.
	$total_invoices_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d', $school_id, $session_id )
	);

	// Paid Invoices.
	$invoices_paid_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_paid_key() )
	);

	// Unpaid Invoices.
	$invoices_unpaid_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_unpaid_key() )
	);

	// Invoices Partially Paid.
	$invoices_partially_paid_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_partially_paid_key() )
	);

	// Total Payments.
	$total_payments_count = $wpdb->get_var( WLSM_M_Staff_Accountant::fetch_payments_query_count( $school_id, $session_id ) );

	// Total Payment Received.
	$total_payment_received = WLSM_M_Staff_Accountant::get_total_payments_received( $school_id, $session_id );
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
					<i class="fas fa-file-invoice"></i>
					<?php esc_html_e( 'Accounting', 'school-management-system' ); ?>
				</span>
			</div>
		</div>
	</div>

	

	<div class="row mt-3 mb-3">

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-file-invoice wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_invoices_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Total Invoices <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-file-invoice wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $invoices_paid_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Paid Invoices <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-file-invoice wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $invoices_unpaid_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Unpaid Invoices <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-file-invoice wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $invoices_partially_paid_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Partially Paid Invoices <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-file-invoice wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_payments_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Total Payments <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-dollar-sign wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( WLSM_Config::get_money_text( $total_payment_received ) ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Payment Received <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>
	<?php } ?>
				
		
	</div>
	<?php if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $current_school['permissions'] ) ) { ?>
		<!-- <div class="col-md-4 col-sm-6"> -->
			<div class="wlsm-group">
				<span class="wlsm-group-title"><?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?></span>
				<div class="wlsm-group-actions">
					<a href="<?php echo esc_url( $page_url_invoices ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_invoices . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New Fee Invoice', 'school-management-system' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_invoices . '&action=payment_history' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
					</a>
				</div>
			</div>
		<!-- </div> -->
		<?php } ?>
</div>
