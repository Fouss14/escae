<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Admin.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_User.php';

class WLSM_School {
	public static function save() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'edit-school-' . $school_id ], 'edit-school-' . $school_id ) ) {
				die();
			}

			// Checks if school exists.
			$school = WLSM_M_School::get_school( $school_id );

			if ( ! $school ) {
				throw new Exception( esc_html__( 'School not found.', 'school-management-system' ) );
			}

			$label   = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';
			$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
			$email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			$address = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';

			// Start validation.
			$errors = array();

			if ( empty( $label ) ) {
				$errors['label'] = esc_html__( 'Please provide school name.', 'school-management-system' );
			}

			if ( strlen( $label ) > 191 ) {
				$errors['label'] = esc_html__( 'Maximum length cannot exceed 191 characters.', 'school-management-system' );
			}

			if ( strlen( $phone ) > 255 ) {
				$errors['phone'] = esc_html__( 'Maximum length cannot exceed 255 characters.', 'school-management-system' );
			}

			if ( strlen( $email ) > 255 ) {
				$errors['email'] = esc_html__( 'Maximum length cannot exceed 255 characters.', 'school-management-system' );
			}

			if ( ! empty( $email ) && ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$errors['email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
			}

			// Checks if school already exists with this label.
			$school_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . WLSM_SCHOOLS . ' as s WHERE s.label = %s AND s.ID != %d', $label, $school_id ) );

			if ( $school_exist ) {
				$errors['label'] = esc_html__( 'School name already exists.', 'school-management-system' );
			}
			// End validation.

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				// Data to update or insert.
				$data = array(
					'label'   => $label,
					'phone'   => $phone,
					'email'   => $email,
					'address' => $address
				);

				// Checks if update.
				$data['updated_at'] = date( 'Y-m-d H:i:s' );

				$success = $wpdb->update( WLSM_SCHOOLS, $data, array( 'ID' => $school_id ) );
				$message = esc_html__( 'School updated successfully.', 'school-management-system' );
				$reset   = false;

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => $message, 'reset' => $reset ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function fetch_school_classes() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		global $wpdb;

		$school_id = isset( $_POST['school'] ) ? absint( $_POST['school'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'school-classes-' . $school_id ], 'school-classes-' . $school_id ) ) {
			die();
		}

		$page_url = WLSM_M_School::get_page_url();

		$query = WLSM_M_School::fetch_classes_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' GROUP BY c.ID';

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(c.label LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'c.label' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY c.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_School::fetch_classes_query_count( $school_id );

		// Total rows count.
		$total_rows_count = $wpdb->get_var( $rows_query );

		// Filtered rows count.
		if ( $condition ) {
			$filter_rows_count = $wpdb->get_var( $rows_query . ' WHERE (' . $condition . ')' );
		} else {
			$filter_rows_count = $total_rows_count;
		}

		// Filtered limit rows.
		$filter_rows_limit = $wpdb->get_results( $query_filter . $limit );

		$data = array();
		if ( count( $filter_rows_limit ) ) {
			foreach ( $filter_rows_limit as $row ) {
				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Class::get_label_text( $row->label ) ),
					'<a class="text-danger wlsm-delete-school-class" data-nonce="' . esc_attr( wp_create_nonce( 'delete-school-class-' . $row->ID ) ) . '" data-school="' . esc_attr( $school_id ) . '" data-class="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will remove this class from the school. All student records associated with this class will be removed.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
				);
			}
		}

		$output = array(
			'draw'            => intval( $_POST['draw'] ),
			'recordsTotal'    => $total_rows_count,
			'recordsFiltered' => $filter_rows_count,
			'data'            => $data,
		);

		echo json_encode( $output );
		die;
	}

	public static function delete_school_class() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : 0;
			$class_id  = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-school-class-' . $class_id ], 'delete-school-class-' . $class_id ) ) {
				die();
			}

			// Checks if school exists.
			$school = WLSM_M_School::get_school( $school_id );

			if ( ! $school ) {
				throw new Exception( esc_html__( 'School not found.', 'school-management-system' ) );
			}

			// Checks if class exists in the school
			$class_school = WLSM_M_School::get_class_school( $class_id, $school->ID );

			if ( ! $class_school ) {
				throw new Exception( esc_html__( 'Class not found in the school.', 'school-management-system' ) );
			}

			$class_school_id = $class_school->ID;

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

			$success = $wpdb->delete( WLSM_CLASS_SCHOOL, array( 'ID' => $class_school_id ) );
			$message = esc_html__( 'Class removed from the school successfully.', 'school-management-system' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function get_keyword_classes() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

		$classes = WLSM_M_School::get_keyword_classes( $keyword );

		$classes = array_map( function( $class ) {
			$class->label = esc_html( stripcslashes( $class->label ) );
			return $class;
		}, $classes );

		wp_send_json_success( $classes );
	}

	public static function assign_classes() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'assign-classes-' . $school_id ], 'assign-classes-' . $school_id ) ) {
				die();
			}

			$classes = ( isset( $_POST['classes'] ) && is_array( $_POST['classes'] ) ) ? $_POST['classes'] : array();

			// Checks if school exists.
			$school = WLSM_M_School::get_school( $school_id );

			if ( ! $school ) {
				throw new Exception( esc_html__( 'School not found.', 'school-management-system' ) );
			}

			// Start validation.
			$errors = array();

			if ( ! count( $classes ) ) {
				$errors['keyword'] = esc_html__( 'Please select atleast one class to assign.', 'school-management-system' );
			}
			// End validation.

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$values              = array();
				$place_holders       = array();

				foreach ( $classes as $class_id ) {
					array_push( $values, $class_id, $school_id );
					array_push( $place_holders, '(%d, %d)' );
				}

				// Insert class_school records.
				$sql     = 'INSERT IGNORE INTO ' . WLSM_CLASS_SCHOOL . ' (class_id, school_id) VALUES ';
				$sql     .= implode( ', ', $place_holders );
				$success = $wpdb->query( $wpdb->prepare( "$sql ", $values ) );

				$message = esc_html__( 'Classes assigned successfully.', 'school-management-system' );

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				WLSM_M_School::create_default_sections( $school_id );

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => $message ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function fetch_school_admins() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		global $wpdb;

		$school_id = isset( $_POST['school'] ) ? absint( $_POST['school'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'school-admins-' . $school_id ], 'school-admins-' . $school_id ) ) {
			die();
		}

		$page_url = WLSM_M_School::get_page_url();

		$query = WLSM_M_School::fetch_admins_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' GROUP BY sf.ID';

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(a.name LIKE "%' . $search_value . '%") OR ' .
				'(u.user_login LIKE "%' . $search_value . '%") OR ' .
				'(u.user_email LIKE "%' . $search_value . '%")';

				$search_value_lowercase = strtolower( $search_value );
				if ( preg_match( '/^sch(|scho|schoo|school|school a|school ad|school adm|school admi|school admin)$/', $search_value_lowercase ) ) {
					$assigned_by_manager = 0;
				} else if ( preg_match( '/^mul(|t|ti|ti-|ti-s|ti-sc|ti-sch|ti-scho|ti-schoo|ti-school|ti-school a|ti-school ad|ti-school adm|ti-school admi|ti-school admin)$/', $search_value_lowercase ) ) {
					$assigned_by_manager = 1;
				}
				if ( isset( $assigned_by_manager ) ) {
					$condition .= ' OR (a.assigned_by_manager = ' . $assigned_by_manager . ')';
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'a.name', 'u.user_login', 'u.user_email', 'a.assigned_by_manager' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY a.name ASC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_School::fetch_admins_query_count( $school_id );

		// Total rows count.
		$total_rows_count = $wpdb->get_var( $rows_query );

		// Filtered rows count.
		if ( $condition ) {
			$filter_rows_count = $wpdb->get_var( $rows_query . ' AND (' . $condition . ')' );
		} else {
			$filter_rows_count = $total_rows_count;
		}

		// Filtered limit rows.
		$filter_rows_limit = $wpdb->get_results( $query_filter . $limit );

		$data = array();
		if ( count( $filter_rows_limit ) ) {
			foreach ( $filter_rows_limit as $row ) {
				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Admin::get_name_text( $row->name ) ),
					esc_html( WLSM_M_User::get_username_text( $row->username ) ),
					esc_html( WLSM_M_User::get_email_text( $row->email ) ),
					esc_html( WLSM_M_Admin::get_assigned_by_text( $row->assigned_by_manager ) ),
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=edit_admin&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;<a class="text-danger wlsm-delete-school-admin" data-nonce="' . esc_attr( wp_create_nonce( 'delete-school-admin-' . $row->ID ) ) . '" data-admin="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will remove this admin from the school.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
				);
			}
		}

		$output = array(
			'draw'            => intval( $_POST['draw'] ),
			'recordsTotal'    => $total_rows_count,
			'recordsFiltered' => $filter_rows_count,
			'data'            => $data,
		);

		echo json_encode( $output );
		die;
	}

	public static function assign_admin() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'assign-admin-' . $school_id ], 'assign-admin-' . $school_id ) ) {
				die();
			}

			// Checks if school exists.
			$school = WLSM_M_School::get_school( $school_id );

			if ( ! $school ) {
				throw new Exception( esc_html__( 'School not found.', 'school-management-system' ) );
			}

			$new_or_existing = isset( $_POST['new_or_existing'] ) ? sanitize_text_field( $_POST['new_or_existing'] ) : '';
			$name            = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

			if ( strlen( $name ) > 255 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', 'school-management-system' );
			}

			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please specify name.', 'school-management-system' );
				wp_send_json_error( $errors );
			}

			// Start validation.
			$errors = array();

			if ( 'existing_user' === $new_or_existing ) {
				$existing_username = isset( $_POST['existing_username'] ) ? sanitize_text_field( $_POST['existing_username'] ) : '';

				if ( empty( $existing_username ) ) {
					$errors['existing_username'] = esc_html__( 'Please provide existing username.', 'school-management-system' );
				}

			} else if ( 'new_user' === $new_or_existing ) {
				$new_username = isset( $_POST['new_username'] ) ? sanitize_text_field( $_POST['new_username'] ) : '';
				$new_email    = isset( $_POST['new_email'] ) ? sanitize_text_field( $_POST['new_email'] ) : '';
				$new_password = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';

				if ( empty( $new_username ) ) {
					$errors['new_username'] = esc_html__( 'Please provide username.', 'school-management-system' );
				}

				if ( empty( $new_email ) ) {
					$errors['new_email'] = esc_html__( 'Please provide email address.', 'school-management-system' );
				}

				if ( ! empty( $new_email ) && ! filter_var( $new_email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['new_email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				}

			} else {
				throw new Exception( esc_html__( 'Please select an option.', 'school-management-system' ) );
			}

			// End validation.

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( 'existing_user' === $new_or_existing ) {
					// Existing user.
					$user = get_user_by( 'login', $existing_username );
					if ( ! $user ) {
						throw new Exception( esc_html__( 'Username does not exist.', 'school-management-system' ) );
					}

					$user_id = $user->ID;

					$user_info = get_userdata( $user_id );

					$staff = WLSM_M_Admin::staff_in_school( $school_id, $user_id );

					if ( $staff ) {
						throw new Exception(
							/* translators: %s: role */
							sprintf( esc_html__( 'User already exists with this username having a role of "%s".', 'school-management-system' ), WLSM_M_Role::get_role_text( $staff->role ) )
						);
					}

				} else {
					// New user.
					$user_data = array(
						'user_email' => $new_email,
						'user_login' => $new_username,
						'user_pass'  => $new_password,
					);

					$user_id = wp_insert_user( $user_data );
					if ( is_wp_error( $user_id ) ) {
						throw new Exception( $user_id->get_error_message() );
					}
				}

				if ( user_can( $user_id, WLSM_ADMIN_CAPABILITY ) ) {
					throw new Exception( esc_html__( 'User is already a multi-school administrator.', 'school-management-system' ) );
				}

				// Data to insert.
				$staff_data = array(
					'school_id' => $school_id,
					'user_id'   => $user_id,
					'role'      => WLSM_M_Role::get_admin_key(),
				);

				$success = $wpdb->insert( WLSM_STAFF, $staff_data );
				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$staff_id = $wpdb->insert_id;

				$admin_data = array(
					'name'     => $name,
					'staff_id' => $staff_id,
				);
				$admin_data['assigned_by_manager'] = 1;

				$success = $wpdb->insert( WLSM_ADMINS, $admin_data );

				$message = esc_html__( 'Admin assigned successfully.', 'school-management-system' );
				$reset   = true;

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$current_school_exists = false;
				if ( $current_school_id = get_user_meta( $user_id, 'wlsm_school_id', true ) ) {
					$staff_in_school = WLSM_M_Admin::user_in_school( $current_school_id, $user_id );

					if ( $staff_in_school ) {
						$current_school_exists = true;
					}
				}

				if ( ! $current_school_exists ) {
					update_user_meta( $user_id, 'wlsm_school_id', $school_id );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => $message, 'reset' => $reset ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function delete_school_admin() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-school-admin-' . $staff_id ], 'delete-school-admin-' . $staff_id ) ) {
				die();
			}

			// Checks if staff exists.
			$staff = WLSM_M_Admin::get_admin( $staff_id );

			if ( ! $staff ) {
				throw new Exception( esc_html__( 'Admin not found.', 'school-management-system' ) );
			}

			$admin_id  = $staff->admin_id;
			$school_id = $staff->school_id;
			$user_id   = $staff->user_id;

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

			$success = $wpdb->delete( WLSM_STAFF, array( 'ID' => $staff_id ) );

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$success = $wpdb->delete( WLSM_ADMINS, array( 'ID' => $admin_id ) );

			$message = esc_html__( 'Admin removed from the school successfully.', 'school-management-system' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$current_school_id = get_user_meta( $user_id, 'wlsm_school_id', true );
			if ( $current_school_id == $school_id ) {
				delete_user_meta( $user_id, 'wlsm_school_id' );
			}

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function save_school_admin() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'edit-school-admin-' . $staff_id ], 'edit-school-admin-' . $staff_id ) ) {
				die();
			}

			// Checks if staff exists.
			if ( $staff_id ) {
				$staff = WLSM_M_Admin::get_admin( $staff_id );

				if ( ! $staff ) {
					throw new Exception( esc_html__( 'Admin not found.', 'school-management-system' ) );
				}
			}

			$school_id = $staff->school_id;
			$user_id   = $staff->user_id;
			$admin_id  = $staff->admin_id;

			$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';

			// Start validation.
			$errors = array();

			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please specify name.', 'school-management-system' );
				wp_send_json_error( $errors );
			}

			if ( strlen( $name ) > 255 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', 'school-management-system' );
			}

			if ( ! $user_id ) {
				$new_or_existing = isset( $_POST['save_new_or_existing'] ) ? sanitize_text_field( $_POST['save_new_or_existing'] ) : '';

				if ( 'existing_user' === $new_or_existing ) {
					$existing_username = isset( $_POST['existing_username'] ) ? sanitize_text_field( $_POST['existing_username'] ) : '';

					if ( empty( $existing_username ) ) {
						$errors['existing_username'] = esc_html__( 'Please provide existing username.', 'school-management-system' );
					}

				} else if ( 'new_user' === $new_or_existing ) {
					$new_username = isset( $_POST['new_username'] ) ? sanitize_text_field( $_POST['new_username'] ) : '';
					$new_email    = isset( $_POST['new_email'] ) ? sanitize_text_field( $_POST['new_email'] ) : '';
					$new_password = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';

					if ( empty( $new_username ) ) {
						$errors['new_username'] = esc_html__( 'Please provide username.', 'school-management-system' );
					}

					if ( empty( $new_email ) ) {
						$errors['new_email'] = esc_html__( 'Please provide email address.', 'school-management-system' );
					}

					if ( ! empty( $new_email ) && ! filter_var( $new_email, FILTER_VALIDATE_EMAIL ) ) {
						$errors['new_email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
					}

				} else {
					throw new Exception( esc_html__( 'Please select an option.', 'school-management-system' ) );
				}
			} else {
				$email    = isset( $_POST['new_email'] ) ? sanitize_text_field( $_POST['new_email'] ) : '';
				$password = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';

				if ( empty( $email ) ) {
					$errors['email'] = esc_html__( 'Please provide email address.', 'school-management-system' );
				}

				if ( ! empty( $email ) && ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				}
			}
			// End validation.

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$reload = false;

				$update_staff_user_id = false;

				if ( ! $user_id ) {
					$reload = true;

					// If there is no user account.
					if ( 'existing_user' === $new_or_existing ) {
						// Existing user.
						$user = get_user_by( 'login', $existing_username );
						if ( ! $user ) {
							throw new Exception( esc_html__( 'Username does not exist.', 'school-management-system' ) );
						}

						$user_id = $user->ID;

						$user_info = get_userdata( $user_id );

						$staff = WLSM_M_Admin::staff_in_school( $school_id, $user_id );

						if ( $staff ) {
							throw new Exception(
								/* translators: %s: role */
								sprintf( esc_html__( 'User already exists with this username having a role of "%s".', 'school-management-system' ), WLSM_M_Role::get_role_text( $staff->role ) )
							);
						}

						if ( user_can( $user_id, WLSM_ADMIN_CAPABILITY ) ) {
							throw new Exception( esc_html__( 'User is already a multi-school administrator.', 'school-management-system' ) );
						}

					} else {
						// New user.
						$user_data = array(
							'user_email' => $new_email,
							'user_login' => $new_username,
							'user_pass'  => $new_password,
						);

						$user_id = wp_insert_user( $user_data );
						if ( is_wp_error( $user_id ) ) {
							throw new Exception( $user_id->get_error_message() );
						}
					}

					$update_staff_user_id = $user_id;
				} else {
					// If there is a user account.
					$user_data = array(
						'ID'         => $user_id,
						'user_email' => $email,
					);

					if ( ! empty( $password ) ) {
						$user_data['user_pass'] = $password;
						if ( get_current_user_id() == $user_id ) {
							$reload = true;
						}
					}

					$user_id = wp_update_user( $user_data );
					if ( is_wp_error( $user_id ) ) {
						throw new Exception( esc_html( $user_id->get_error_message() ) );
					}
				}

				if ( $update_staff_user_id ) {
					// Data to update.
					$staff_data = array(
						'user_id' => $update_staff_user_id,
					);

					$staff_data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_STAFF, $staff_data, array( 'ID' => $staff_id ) );

					if ( false === $success ) {
						throw new Exception( $wpdb->last_error );
					}
				}

				// Data to update.
				$admin_data = array(
					'name' => $name,
				);

				$admin_data['updated_at'] = date( 'Y-m-d H:i:s' );

				$success = $wpdb->update( WLSM_ADMINS, $admin_data, array( 'ID' => $admin_id ) );
				$message = esc_html__( 'Admin updated successfully.', 'school-management-system' );

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$current_school_exists = false;
				if ( $current_school_id = get_user_meta( $user_id, 'wlsm_school_id', true ) ) {
					$staff_in_school = WLSM_M_Admin::user_in_school( $current_school_id, $user_id );

					if ( $staff_in_school ) {
						$current_school_exists = true;
					}
				}

				if ( ! $current_school_exists ) {
					update_user_meta( $user_id, 'wlsm_school_id', $school_id );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => $message, 'reload' => $reload ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function set_school() {
		if ( ! current_user_can( WLSM_ADMIN_CAPABILITY ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id = isset( $_POST['school'] ) ? absint( $_POST['school'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'set-school-' . $school_id ], 'set-school-' . $school_id ) ) {
				die();
			}

			// Checks if school exists.
			if ( $school_id ) {
				$school = WLSM_M_School::get_school( $school_id );

				if ( ! $school ) {
					throw new Exception( esc_html__( 'School not found.', 'school-management-system' ) );
				}
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

			$user_id = get_current_user_id();

			// Data to update or insert.
			$data = array(
				'school_id' => $school_id,
				'user_id'   => $user_id,
				'role'      => WLSM_M_Role::get_admin_key(),
			);

			$staff = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID FROM ' . WLSM_STAFF . ' as sf WHERE sf.school_id = %d AND sf.user_id = %d', $school_id, $user_id ) );

			// Checks if update or insert.
			if ( $staff ) {
				$data['updated_at'] = date( 'Y-m-d H:i:s' );

				$success = $wpdb->update( WLSM_STAFF, $data, array( 'ID' => $staff->ID ) );
			} else {
				$success = $wpdb->insert( WLSM_STAFF, $data );
			}

			$url     = admin_url() . 'admin.php?page=' . WLSM_MENU_STAFF_SCHOOL;
			$message = esc_html__( 'School selected successfully.', 'school-management-system' );

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			update_user_meta( $user_id, 'wlsm_school_id', $school_id );

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message, 'url' => $url ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}
}
