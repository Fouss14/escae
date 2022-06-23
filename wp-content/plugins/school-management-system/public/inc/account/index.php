<?php
defined( 'ABSPATH' ) || die();

global $wp;

$current_page_url = home_url( add_query_arg( array(), $wp->request ) );
if ( ! is_user_logged_in() ) {
	$login_form_args = array(
		'form_id'        => 'wlsm-login-form',
		'id_username'    => 'wlsm-login-username',
		'id_password'    => 'wlsm-login-password',
		'id_remember'    => 'wlsm-login-remember',
		'id_submit'      => 'wlsm-login-submit',
		'value_username' => '',
	);
	wp_login_form( $login_form_args );
	?>
	<a target="_blank" href="<?php echo esc_url( wp_lostpassword_url( $current_page_url ) ); ?>">
		<?php esc_html_e( 'Lost your password?', 'school-management-system' ); ?>
	</a>
	<?php
} else {
	global $wpdb;

	$user_id = get_current_user_id();

	// Checks if user is student.
	$student = $wpdb->get_row(
		$wpdb->prepare( 'SELECT sr.ID, sr.name as student_name, sr.email, sr.phone, sr.father_name, sr.admission_number, sr.enrollment_number, c.label as class_label, se.class_school_id, se.label as section_label, sr.roll_number, u.user_email as login_email, u.user_login as username, sr.session_id, ss.label as session_label, s.ID as school_id, s.label as school_name FROM ' . WLSM_STUDENT_RECORDS . ' as sr 
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
			JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id 
			JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id 
			WHERE sr.is_active = 1 AND sr.user_id = %d', $user_id )
	);

	$logout_url = wp_logout_url( $current_page_url );
	esc_html_e( 'You are logged in.', 'school-management-system' );
	?>
	<a href="<?php echo esc_url( $logout_url ); ?>">
		<?php esc_html_e( 'Logout', 'school-management-system' ); ?>
	</a>
	<?php
	if ( $student ) {
		$school_id  = $student->school_id;
		$session_id = $student->session_id;
		require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/route.php';
	} else {
		require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/no_record.php';
	}
}
