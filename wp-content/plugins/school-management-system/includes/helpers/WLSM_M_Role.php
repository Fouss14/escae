<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

class WLSM_M_Role {
	private static $admin    = 'admin';
	private static $employee = 'employee';

	public static function get_user_info() {
		global $wpdb;

		$user_id = get_current_user_id();

		$current_school_id = get_user_meta( $user_id, 'wlsm_school_id', true );

		$schools = array();

		$staff_in_school = false;

		$staff = $wpdb->get_results( $wpdb->prepare( 'SELECT sf.role, sf.permissions, sf.school_id, s.label as school_name FROM ' . WLSM_STAFF . ' as sf JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = sf.school_id WHERE sf.user_id = %d', $user_id ) );

		if ( count( $staff ) ) {
			foreach ( $staff as $user ) {
				if ( $user->school_id === $current_school_id ) {
					$staff_in_school = true;

					$school_id   = $user->school_id;
					$role        = $user->role;
					$permissions = $user->permissions ? unserialize( $user->permissions ) : array();
					$school_name = $user->school_name;
				}

				array_push( $schools, array( 'id' => $user->school_id, 'name' => $user->school_name ) );
			}
		}

		$data = array(
			'schools_assigned' => $schools,
		);

		if ( $staff_in_school ) {
			if ( self::get_admin_key() == $role ) {
				$permissions = array_keys( self::get_permissions() );
			}

			$data['current_school'] = array(
				'id'          => $school_id,
				'role'        => $role,
				'permissions' => $permissions,
				'name'        => $school_name,
			);
		} else {
			$data['current_school'] = false;

			if ( 1 === count( $staff ) ) {
				update_user_meta( $user_id, 'wlsm_school_id', $staff[0]->school_id );
			}
		}

		return $data;
	}

	public static function get_roles() {
		return array(
			self::$admin    => esc_html__( 'Admin', 'school-management-system' ),
			self::$employee => esc_html__( 'Staff', 'school-management-system' ),
		);
	}

	public static function get_staff_roles( $school_id ) {
		global $wpdb;

		$staff_roles = $wpdb->get_results( $wpdb->prepare( 'SELECT r.ID, r.name FROM ' . WLSM_ROLES . ' as r 
		WHERE r.school_id = %d', $school_id ), OBJECT_K );

		return $staff_roles;
	}

	public static function get_role_text( $role ) {
		if ( array_key_exists( $role, self::get_roles() ) ) {
			return self::get_roles()[ $role ];
		}

		return '';
	}

	public static function get_admin_key() {
		return self::$admin;
	}

	public static function get_employee_key() {
		return self::$employee;
	}

	public static function get_permissions( $skip = array() ) {
		$permissions = array(
			'manage_inquiries'  => esc_html__( 'Manage Inquiries', 'school-management-system' ),
			'manage_admissions' => esc_html__( 'Manage Admissions', 'school-management-system' ),
			'manage_students'   => esc_html__( 'Manage Students', 'school-management-system' ),
			'manage_admins'     => esc_html__( 'Add/Remove Admins', 'school-management-system' ),
			'manage_roles'      => esc_html__( 'Manage Roles', 'school-management-system' ),
			'manage_employees'  => esc_html__( 'Add/Remove Staff', 'school-management-system' ),
			'manage_promote'    => esc_html__( 'Student Promotion', 'school-management-system' ),
			'manage_classes'    => esc_html__( 'Manage Classes & Sections', 'school-management-system' ),
			'manage_subjects'   => esc_html__( 'Manage Subjects', 'school-management-system' ),
			'manage_notices'    => esc_html__( 'Manage Noticeboard', 'school-management-system' ),
			'manage_invoices'   => esc_html__( 'Manage Invoices', 'school-management-system' ),
			'delete_payments'   => esc_html__( 'Delete Payments', 'school-management-system' ),
			'manage_settings'   => esc_html__( 'Manage Settings', 'school-management-system' ),
		);

		if ( count( $skip ) > 0 ) {
			foreach ( $skip as $key ) {
				if ( isset( $permissions[ $key ] ) ) {
					unset( $permissions[ $key ] );
				}
			}
		}

		return $permissions;
	}

	public static function check_permission( $permissions_to_check, $user_permissions ) {
		return ! empty ( array_intersect( $permissions_to_check, $user_permissions ) );
	}

	public static function get_role_permissions( $role, $permissions ) {
		$permissions_keys = array_keys( self::get_permissions( array( 'manage_admins' ) ) );

		if ( self::get_admin_key() == $role ) {
			$permissions = $permissions_keys;
		} else {
			if ( is_serialized( $permissions ) ) {
				$permissions = unserialize( $permissions );
			}
			return array_intersect( $permissions, $permissions_keys );
		}

		return $permissions;
	}

	public static function can( $permission ) {
		$user_info      = self::get_user_info();
		$current_school = $user_info['current_school'];

		if ( ! $current_school ) {
			return false;
		}

		$role = $current_school['role'];
		if ( in_array( $role, array_keys( self::get_roles() ) ) ) {
			$permissions = $current_school['permissions'];
			if ( ! is_array( $permission ) ) {
				$permission = array( $permission );
			}
			if ( self::check_permission( $permission, $permissions ) ) {
				$current_session = WLSM_Config::current_session();
				return array(
					'school'  => $current_school,
					'session' => $current_session,
				);
			}
		}

		return false;
	}

	public static function get_permission_text( $permission ) {
		if ( isset( self::get_permissions()[ $permission ] ) ) {
			return self::get_permissions()[ $permission ];
		}

		return '';
	}

	public static function get_admin_text() {
		return self::get_roles()[ self::$admin ];
	}

	public static function get_employee_text() {
		return self::get_roles()[ self::$employee ];
	}
}
