<?php
defined( 'ABSPATH' ) || die();

global $wpdb;

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$schools_page_url  = WLSM_M_School::get_page_url();
$students_page_url = WLSM_M_Staff_General::get_students_page_url();
$invoices_page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $current_school['permissions'] ) ) {
	// Total Classes.
	$total_classes_count  = $wpdb->get_var( WLSM_M_Staff_Class::fetch_classes_query_count( $school_id ) );

	// Total Sections.
	$total_sections_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT se.ID) FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id WHERE cs.school_id = %d', $school_id ) );
}

if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $current_school['permissions'] ) ) {
	// Total Students.
	$total_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d', $session_id, $school_id )
	);

	// Students Active.
	$active_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d AND sr.is_active = 1', $session_id, $school_id )
	);
}

if ( WLSM_M_Role::check_permission( array( 'manage_admins' ), $current_school['permissions'] ) ) {
	// Total Admins.
	$admins_query       = new WP_User_Query( array( 'role' => 'Administrator' ) );
	$total_admins_count = (int) $admins_query->get_total();
}

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

if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $current_school['permissions'] ) ) {
	// Last 15 Admissions
	$admissions = $wpdb->get_results(
		$wpdb->prepare( 'SELECT sr.ID, sr.name as student_name, sr.enrollment_number, sr.admission_number, sr.admission_date, c.label as class_label, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE cs.school_id = %d AND ss.ID = %d GROUP BY sr.ID ORDER BY sr.admission_date DESC LIMIT 15', $school_id, $session_id )
	);
}

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

if ( WLSM_M_Role::check_permission( array( 'manage_inquiries' ), $current_school['permissions'] ) ) {
	// Total Inquiries.
	$total_inquiries_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(iq.ID) FROM ' . WLSM_INQUIRIES . ' as iq
		WHERE iq.school_id = %d', $school_id ) );

	// Inquiries Active.
	$active_inquiries_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(iq.ID) FROM ' . WLSM_INQUIRIES . ' as iq
		WHERE iq.school_id = %d AND iq.is_active = 1', $school_id ) );

	// Last 10 Active Inquiries
	$active_inquiries = $wpdb->get_results(
		$wpdb->prepare( 'SELECT iq.ID, iq.name, iq.phone, iq.email, iq.message, iq.created_at, iq.next_follow_up, c.label as class_label FROM ' . WLSM_INQUIRIES . ' as iq
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = iq.school_id
		LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = iq.class_school_id
		LEFT OUTER JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE iq.school_id = %d AND iq.is_active = 1 GROUP BY iq.ID ORDER BY iq.created_at DESC LIMIT 10', $school_id )
	);
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-tachometer-alt"></i>
				<?php esc_html_e( 'School Dashboard', 'school-management-system' ); ?>
			</span>
			<?php if ( current_user_can( WLSM_ADMIN_CAPABILITY ) ) { ?>
			<span class="float-right">
				<a class="btn btn-sm btn-outline-light" href="<?php echo esc_url( $schools_page_url . '&action=classes&id=' . $school_id ); ?>">
					<i class="fas fa-layer-group"></i>&nbsp;
					<?php esc_html_e( 'Assign Classes', 'school-management-system' ); ?>
				</a>&nbsp;
			</span>
			<?php } ?>
		</div>
	</div>
</div>

<div class="row mt-1 wlsm-stats-blocks">
	<?php if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-layer-group wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_classes_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Classes', 'school-management-system' ); ?></div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-layer-group wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_sections_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Sections', 'school-management-system' ); ?></div>
		</div>
	</div>
	<?php } ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-users wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_students_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Total Students <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
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
			<i class="fas fa-users wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $active_students_count ); ?></div>
			<div class="wlsm-stats-label">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Students Active <br><small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ), 'br' => array() )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_admins' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-user-shield wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_admins_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Admins', 'school-management-system' ); ?></div>
		</div>
	</div>
	<?php } ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_roles' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-user-tag wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_roles_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Roles', 'school-management-system' ); ?></div>
		</div>
	</div>
	<?php } ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_employees' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-user-shield wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_staff_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Staff', 'school-management-system' ); ?></div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-user-shield wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $active_staff_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Staff Active', 'school-management-system' ); ?></div>
		</div>
	</div>
	<?php } ?>

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

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_inquiries' ), $current_school['permissions'] ) ) { ?>
	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-envelope wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $total_inquiries_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Total Inquiries', 'school-management-system' ); ?></div>
		</div>
	</div>

	<div class="col-md-4 col-lg-3">
		<div class="wlsm-stats-block">
			<i class="fas fa-envelope wlsm-stats-icon"></i>
			<div class="wlsm-stats-counter"><?php echo esc_html( $active_inquiries_count ); ?></div>
			<div class="wlsm-stats-label"><?php esc_html_e( 'Inquiries Active', 'school-management-system' ); ?></div>
		</div>
	</div>
	<?php } ?>
</div>

<?php if ( WLSM_M_Role::check_permission( array( 'manage_inquiries' ), $current_school['permissions'] ) ) { ?>
<div class="row">
	<div class="col-md-12">
		<div class="wlsm-stats-heading-block">
			<div class="wlsm-stats-heading">
				<?php esc_html_e( 'Last 10 Active Inquiries', 'school-management-system' ); ?>
			</div>
		</div>
		<table class="table wlsm-stats-table wlsm-stats-active-inquiries-table">
			<thead class="bg-primary text-white">
				<tr>
					<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Message', 'school-management-system' ); ?></th>
					<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
					<th class="text-nowrap"><?php esc_html_e( 'Follow Up Date', 'school-management-system' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $active_inquiries as $row ) { ?>
				<tr>
					<td><?php echo esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ); ?></td>
					<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ); ?></td>
					<td><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ); ?></td>
					<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $row->email ) ); ?></td>
					<td>
					<?php
					if ( $row->message ) {
						echo esc_html( WLSM_Config::limit_string( $row->message, 50 ) );
						echo '<a class="text-primary wlsm-view-inquiry-message" data-nonce="' . esc_attr( wp_create_nonce( 'view-inquiry-message-' . $row->ID ) ) . '" data-inquiry="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Inquiry Message', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><span class="dashicons dashicons-search"></span></a>';
					} else {
						echo esc_attr('-');
					}
					?>
					</td>
					<td class="text-nowrap"><?php echo esc_html( WLSM_Config::get_date_text( $row->created_at ) ); ?></td>
					<td class="text-nowrap"><?php echo esc_html( $row->next_follow_up ? WLSM_Config::get_date_text( $row->next_follow_up ) : '-' ); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php } ?>

<?php if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $current_school['permissions'] ) ) { ?>
<div class="row">
	<div class="col-md-12">
		<div class="wlsm-stats-heading-block">
			<div class="wlsm-stats-heading">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Last 15 Admissions <small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ) )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);
				?>
			</div>
		</div>
		<table class="table wlsm-stats-table wlsm-stats-admission-table">
			<thead class="bg-primary text-white">
				<tr>
					<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Admission Date', 'school-management-system' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $admissions as $row ) { ?>
				<tr>
					<td><a href="<?php echo esc_url( $students_page_url . "&action=save&id=" . $row->ID ); ?>" class="wlsm-link"><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $row->student_name ) ); ?></a></td>
					<td><?php echo esc_html( $row->enrollment_number ); ?></td>
					<td><?php echo esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ); ?></td>
					<td><?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ); ?></td>
					<td><?php echo esc_html( WLSM_M_Staff_Class::get_admission_no_text( $row->admission_number ) ); ?></td>
					<td><?php echo esc_html( WLSM_Config::get_date_text( $row->admission_date ) ); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?php } ?>

<?php if ( WLSM_M_Role::check_permission( array( 'manage_invoices' ), $current_school['permissions'] ) ) { ?>
<div class="row">
	<div class="col-md-12">
		<div class="wlsm-stats-heading-block">
			<div class="wlsm-stats-heading">
				<?php
				printf(
					wp_kses(
						/* translators: %s: session label */
						__( 'Last 15 Payments <small class="text-secondary"> - Session: %s</small>', 'school-management-system' ),
						array( 'small' => array( 'class' => array() ) )
					),
					esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) )
				);

				$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );
				?>
			</div>
		</div>
		<table class="table wlsm-stats-table wlsm-stats-payment-table">
			<thead class="bg-primary text-white">
				<tr>
					<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Invoice', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Admission Number', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Fahter Name', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Fahter Phone', 'school-management-system' ); ?></th>
					<?php if ( $can_delete_payments ) { ?>
					<th class="text-nowrap"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></th>
					<?php } ?>
				</tr>
			</thead>
		</table>
	</div>
</div>
<?php }
