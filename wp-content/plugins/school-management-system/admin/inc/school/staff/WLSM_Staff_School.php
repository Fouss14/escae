<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';

class WLSM_Staff_School {
	public static function set_school() {
		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school'] ) ? absint( $_POST['school'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'set-school-' . $school_id ], 'set-school-' . $school_id ) ) {
				die();
			}

			$user_info = WLSM_M_Role::get_user_info();

			$schools_assigned = $user_info['schools_assigned'];

			if ( count( $schools_assigned ) < 2 ) {
				die();
			}

			$school_assigned = false;
			foreach ( $schools_assigned as $school ) {
				if ( absint( $school['id'] ) === $school_id ) {
					$school_assigned = true;
				}
			}

			if ( ! $school_assigned ) {
				die();
			}

			$user_id = get_current_user_id();

			// Check if staff exists.
			$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID, sf.role FROM ' . WLSM_STAFF . ' as sf WHERE sf.school_id = %d AND sf.user_id = %d', $school_id, $user_id ) );

			if ( ! $staff ) {
				die();
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		try {
			$wpdb->query( 'BEGIN;' );

			$role = $staff->role;

			if ( in_array( $role, array_keys( WLSM_M_Role::get_roles() ), true ) ) {
				$url     = admin_url() . 'admin.php?page=' . WLSM_MENU_STAFF_SCHOOL;
				$message = esc_html__( 'School selected successfully.', 'school-management-system' );
			} else {
				throw new Exception( esc_html__( 'Unable to find your role.', 'school-management-system' ) );
			}

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}

			update_user_meta( $user_id, 'wlsm_school_id', $school_id );

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message, 'url' => $url ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function set_session() {
		$session_id = isset( $_POST['session'] ) ? absint( $_POST['session'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'set-session-' . $session_id ], 'set-session-' . $session_id ) ) {
			die();
		}

		$session = WLSM_M_Session::get_session( $session_id );
		if ( ! $session ) {
			die();
		}

		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'wlsm_current_session', $session_id );

		$message = esc_html__( 'Session changed.', 'school-management-system' );

		wp_send_json_success( array( 'message' => $message ) );
	}
}
