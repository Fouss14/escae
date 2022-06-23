<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

class WLSM_Staff_Class {
	public static function fetch_classes() {
		$current_user = WLSM_M_Role::can( 'manage_classes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		$page_url = WLSM_M_Staff_Class::get_sections_page_url();

		$query = WLSM_M_Staff_Class::fetch_classes_query( $school_id, $session_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Class::fetch_classes_query_group_by();

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
		$columns = array( 'c.label', 'sections_count', 'students_count' );
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
		$rows_query = WLSM_M_Staff_Class::fetch_classes_query_count( $school_id );

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
				$sections_count = absint( $row->sections_count );
				if ( ! $sections_count ) {
					$sections_count = '<a class="text-primary wlsm-font-bold" href="' . esc_url( $page_url . "&action=sections&id=" . $row->ID ) . '">' . esc_html__( 'Add Sections', 'school-management-system' ) . '</a>';
				} else {
					$sections_count = '<a class="text-primary wlsm-font-bold" href="' . esc_url( $page_url . "&action=sections&id=" . $row->ID ) . '">' . $sections_count . '<button type="button" class="btn btn-primary btn-sm float-right">Add More Sections</button></a>';
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Class::get_label_text( $row->label ) ),
					$sections_count,
					absint( $row->students_count ),
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

	public static function fetch_class_sections() {
		$current_user = WLSM_M_Role::can( 'manage_classes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		$class_school_id = isset( $_POST['class_school'] ) ? absint( $_POST['class_school'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'class-sections-' . $class_school_id ], 'class-sections-' . $class_school_id ) ) {
			die();
		}

		$page_url = WLSM_M_Staff_Class::get_sections_page_url();

		$query = WLSM_M_Staff_Class::fetch_sections_query( $school_id, $session_id, $class_school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Class::fetch_sections_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(se.label LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'se.label', 'students_count' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY se.label ASC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Class::fetch_sections_query_count( $school_id, $class_school_id );

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
				$default_section_text = '';

				if ( $row->ID !== $row->default_section_id ) {
					$default_section_text = '';
					$delete_section = '<a class="text-danger wlsm-delete-section" data-nonce="' . esc_attr( wp_create_nonce( 'delete-section-' . $row->ID ) ) . '" data-class="' . esc_attr( $row->class_id ) . '" data-section="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will remove section from the class. All student records in this section of all sessions will be moved to the default section.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>';
				} else {
					$default_section_text = ' <small class="text-secondary"> - ' . WLSM_M_STAFF_CLASS::get_default_section_text() . '</small>';
					$delete_section = '';
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->label ) ) . $default_section_text,
					absint( $row->students_count ),
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=sections&id=" . $row->class_id ) . '&section_id=' . $row->ID . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;' . $delete_section
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

	public static function save_section() {
		$current_user = WLSM_M_Role::can( 'manage_classes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		try {
			ob_start();
			global $wpdb;

			$section_id = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;

			if ( $section_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-section-' . $section_id ], 'edit-section-' . $section_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-section'], 'add-section' ) ) {
					die();
				}
			}

			$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			// Checks if class exists in the school.
			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

			if ( ! $class_school ) {
				throw new Exception( esc_html__( 'Class not found.', 'school-management-system' ) );
			}

			$class_school_id = $class_school->ID;

			// Checks if section exists.
			if ( $section_id ) {
				$section = WLSM_M_Staff_Class::get_section( $school_id, $section_id, $class_school_id );

				if ( ! $section ) {
					throw new Exception( esc_html__( 'Section not found.', 'school-management-system' ) );
				}
			}

			$label = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';

			$is_default = isset( $_POST['is_default'] ) ? (bool) ( $_POST['is_default'] ) : 0;

			// Start validation.
			$errors = array();

			if ( empty( $label ) ) {
				$errors['label'] = esc_html__( 'Please provide section label.', 'school-management-system' );
			}

			if ( strlen( $label ) > 191 ) {
				$errors['label'] = esc_html__( 'Maximum length cannot exceed 191 characters.', 'school-management-system' );
			}

			// Checks if section already exists in the class with this label.
			if ( $section_id ) {
				$section_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . WLSM_SECTIONS . ' as se WHERE se.label = %s AND se.ID != %d AND se.class_school_id = %d', $label, $section_id, $class_school_id ) );
			} else {
				$section_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . WLSM_SECTIONS . ' as se WHERE se.label = %s AND se.class_school_id = %d', $label, $class_school_id ) );
			}

			if ( $section_exist ) {
				$errors['label'] = esc_html__( 'Section already exists.', 'school-management-system' );
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
					'label'           => $label,
					'class_school_id' => $class_school_id,
				);

				// Checks if update or insert.
				if ( $section_id ) {
					$data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_SECTIONS, $data, array( 'ID' => $section_id ) );
					$message = esc_html__( 'Section updated successfully.', 'school-management-system' );
					$reset   = false;
				} else {
					$success = $wpdb->insert( WLSM_SECTIONS, $data );
					$message = esc_html__( 'Section added successfully.', 'school-management-system' );
					$reset   = true;

					$section_id = $wpdb->insert_id;
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( $is_default ) {
					$success = $wpdb->update(
						WLSM_CLASS_SCHOOL,
						array( 'default_section_id' => $section_id, 'updated_at' => date( 'Y-m-d H:i:s' ) ),
						array( 'ID' => $class_school_id )
					);

					$buffer = ob_get_clean();
					if ( ! empty( $buffer ) ) {
						throw new Exception( $buffer );
					}

					if ( false === $success ) {
						throw new Exception( $wpdb->last_error );
					}
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

	public static function delete_section() {
		$current_user = WLSM_M_Role::can( 'manage_classes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$section_id = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-section-' . $section_id ], 'delete-section-' . $section_id ) ) {
				die();
			}

			$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			// Checks if class exists in the school.
			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

			if ( ! $class_school ) {
				throw new Exception( esc_html__( 'Class not found.', 'school-management-system' ) );
			}

			$class_school_id = $class_school->ID;

			$default_section_id = $class_school->default_section_id;

			// Checks if section exists.
			$section = WLSM_M_Staff_Class::get_section( $school_id, $section_id, $class_school_id );

			if ( ! $section ) {
				throw new Exception( esc_html__( 'Section not found.', 'school-management-system' ) );
			}

			if ( $section->ID === $default_section_id ) {
				throw new Exception( esc_html__( 'Default section can\'t be deleted.', 'school-management-system' ) );
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

			$success = $wpdb->update(
				WLSM_STUDENT_RECORDS,
				array( 'section_id' => $default_section_id, 'updated_at' => date( 'Y-m-d H:i:s' ) ),
				array( 'section_id' => $section_id )
			);

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$success = $wpdb->delete( WLSM_SECTIONS, array( 'ID' => $section_id ) );
			$message = esc_html__( 'Section deleted successfully.', 'school-management-system' );

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

	public static function fetch_notices() {
		$current_user = WLSM_M_Role::can( 'manage_notices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		global $wpdb;

		$page_url = WLSM_M_Staff_Class::get_notices_page_url();

		$query = WLSM_M_Staff_Class::fetch_notice_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Class::fetch_notice_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(n.title LIKE "%' . $search_value . '%") OR ' .
				'(n.link_to LIKE "%' . $search_value . '%") OR ' .
				'(u.user_login LIKE "%' . $search_value . '%")';

				$search_value_lowercase = strtolower( $search_value );
				if ( preg_match( '/^none$/', $search_value_lowercase ) ) {
					$link_to = '';
				}

				if ( isset( $link_to ) ) {
					$condition .= ' OR (n.link_to = "' . $link_to . '")';
				}

				if ( preg_match( '/^inac(|t|ti|tiv|tive)$/', $search_value_lowercase ) ) {
					$is_active = 0;
				} else if ( preg_match( '/^acti(|v|ve)$/', $search_value_lowercase ) ) {
					$is_active = 1;
				}
				if ( isset( $is_active ) ) {
					$condition .= ' OR (n.is_active = ' . $is_active . ')';
				}

				$created_at = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

				if ( $created_at ) {
					$format_created_at = 'Y-m-d';
				} else {
					if ( 'd-m-Y' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'm-Y', $search_value );
							$format_created_at = 'Y-m';
						}
					} else if ( 'd/m/Y' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'm/Y', $search_value );
							$format_created_at = 'Y-m';
						}
					} else if ( 'Y-m-d' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'Y-m', $search_value );
							$format_created_at = 'Y-m';
						}
					} else if ( 'Y/m/d' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'Y/m', $search_value );
							$format_created_at = 'Y-m';
						}
					}

					if ( ! $created_at ) {
						$created_at        = DateTime::createFromFormat( 'Y', $search_value );
						$format_created_at = 'Y';
					}
				}

				if ( $created_at && isset( $format_created_at ) ) {
					$created_at = $created_at->format( $format_created_at );
					$created_at = ' OR (n.created_at LIKE "%' . $created_at . '%")';

					$condition .= $created_at;
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'n.title', 'n.link_to', 'n.is_active', 'n.created_at', 'u.user_login' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY n.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Class::fetch_notice_query_count( $school_id );

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
				$link_to = $row->link_to;

				if ( 'url' === $link_to ) {
					$link_to = '<a target="_blank" href="' . esc_url( $row->url ) . '">' . esc_html( WLSM_M_Staff_Class::get_link_to_text( $link_to ) ) . '</a>';
				} else if ( 'attachment' === $link_to ) {
					$link_to = esc_html( WLSM_M_Staff_Class::get_link_to_text( $link_to ) );
					if ( ! empty ( $row->attachment ) ) {
						$attachment = $row->attachment;
						$link_to .= '<br><a target="_blank" href="' . esc_url( wp_get_attachment_url( $attachment ) ) . '"><i class="fas fa-search"></i></a>';
					}
				} else {
					$link_to = esc_html( WLSM_M_Staff_Class::get_none_text() );
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_Config::limit_string( WLSM_M_Staff_Class::get_name_text( $row->title ) ) ),
					$link_to,
					esc_html( WLSM_M_Staff_Class::get_status_text( $row->is_active ) ),
					esc_html( WLSM_Config::get_date_text( $row->created_at ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->username ) ),
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
					<a class="text-danger wlsm-delete-notice" data-nonce="' . esc_attr( wp_create_nonce( 'delete-notice-' . $row->ID ) ) . '" data-notice="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the notice.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
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
		die();
	}

	public static function save_notice() {
		$current_user = WLSM_M_Role::can( 'manage_notices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$notice_id = isset( $_POST['notice_id'] ) ? absint( $_POST['notice_id'] ) : 0;

			if ( $notice_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-notice-' . $notice_id ], 'edit-notice-' . $notice_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-notice'], 'add-notice' ) ) {
					die();
				}
			}

			// Checks if notice exists.
			if ( $notice_id ) {
				$notice = WLSM_M_Staff_Class::get_notice( $school_id, $notice_id );

				if ( ! $notice ) {
					throw new Exception( esc_html__( 'Notice not found.', 'school-management-system' ) );
				}
			}

			$title      = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
			$link_to    = isset( $_POST['link_to'] ) ? sanitize_text_field( $_POST['link_to'] ) : '';
			$attachment = ( isset( $_FILES['attachment'] ) && is_array( $_FILES['attachment'] ) ) ? $_FILES['attachment'] : NULL;
			$url        = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';
			$is_active  = isset( $_POST['is_active'] ) ? (bool) $_POST['is_active'] : 1;

			// Start validation.
			$errors = array();

			if ( empty( $title ) ) {
				$errors['title'] = esc_html__( 'Please provide notice text.', 'school-management-system' );
			}

			if ( ! in_array( $link_to, array( 'url', 'attachment' ) ) ) {
				$link_to = '';
			}

			if ( 'attachment' === $link_to ) {
				if ( isset( $attachment['tmp_name'] ) && ! empty( $attachment['tmp_name'] ) ) {
					$finfo = finfo_open( FILEINFO_MIME_TYPE );
					$mime  = finfo_file( $finfo, $attachment['tmp_name'] );
					finfo_close($finfo);

					if ( ! in_array( $mime, WLSM_Helper::get_attachment_mime() ) ) {
						$errors['attachment'] = esc_html__( 'This file type is not allowed.', 'school-management-system' );
					}
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

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( $notice_id ) {
					$message = esc_html__( 'Notice updated successfully.', 'school-management-system' );
					$reset   = false;
				} else {
					$message = esc_html__( 'Notice added successfully.', 'school-management-system' );
					$reset   = true;
				}

				// Notice data.
				$data = array(
					'title'     => $title,
					'link_to'   => $link_to,
					'url'       => $url,
					'is_active' => $is_active,
					'added_by'  => get_current_user_id(),
				);

				if ( ! empty( $attachment ) ) {
					$attachment = media_handle_upload( 'attachment', 0 );
					if ( is_wp_error( $attachment ) ) {
						throw new Exception( $attachment->get_error_message() );
					}
					$data['attachment'] = $attachment;

					if ( $notice_id && $notice->attachment ) {
						$attachment_id_to_delete = $notice->attachment;
					}
				}

				if ( $notice_id ) {
					$data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_NOTICES, $data, array( 'ID' => $notice_id, 'school_id' => $school_id ) );
				} else {
					$data['school_id'] = $school_id;

					$success = $wpdb->insert( WLSM_NOTICES, $data );
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( isset( $attachment_id_to_delete ) ) {
					wp_delete_attachment( $attachment_id_to_delete, true );
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

	public static function delete_notice() {
		$current_user = WLSM_M_Role::can( 'manage_notices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$notice_id = isset( $_POST['notice_id'] ) ? absint( $_POST['notice_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-notice-' . $notice_id ], 'delete-notice-' . $notice_id ) ) {
				die();
			}

			// Checks if notice exists.
			$notice = WLSM_M_Staff_Class::get_notice( $school_id, $notice_id );

			if ( ! $notice ) {
				throw new Exception( esc_html__( 'Notice not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_NOTICES, array( 'ID' => $notice_id ) );
			$message = esc_html__( 'Notice deleted successfully.', 'school-management-system' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			if ( $notice->attachment ) {
				$attachment_id_to_delete = $notice->attachment;
				wp_delete_attachment( $attachment_id_to_delete, true );
			}

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function fetch_subjects() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		global $wpdb;

		$page_url = WLSM_M_Staff_Class::get_subjects_page_url();

		$query = WLSM_M_Staff_Class::fetch_subject_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Class::fetch_subject_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(sj.label LIKE "%' . $search_value . '%") OR ' .
				'(sj.code LIKE "%' . $search_value . '%") OR ' .
				'(sj.type LIKE "%' . $search_value . '%") OR ' .
				'(c.label LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'sj.label', 'sj.code', 'sj.type', 'c.label', 'admins_count' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY sj.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Class::fetch_subject_query_count( $school_id );

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
				$admins_count = absint( $row->admins_count );
				if ( ! $admins_count ) {
					$admins_count = '<a class="text-primary wlsm-font-bold" href="' . esc_url( $page_url . "&action=teachers&id=" . $row->ID ) . '">' . esc_html__( 'Assign Teachers', 'school-management-system' ) . '</a>';
				} else {
					$admins_count = '<a class="text-primary wlsm-font-bold" href="' . esc_url( $page_url . "&action=teachers&id=" . $row->ID ) . '">' . $admins_count . '</a>';
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Staff_Class::get_subject_label_text( $row->subject_name ) ),
					esc_html( WLSM_M_Staff_Class::get_subject_code_text( $row->code ) ),
					esc_html( WLSM_M_Staff_Class::get_subject_type_text( $row->type ) ),
					esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
					$admins_count,
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
					<a class="text-danger wlsm-delete-subject" data-nonce="' . esc_attr( wp_create_nonce( 'delete-subject-' . $row->ID ) ) . '" data-subject="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the subject.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
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
		die();
	}

	public static function save_subject() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$subject_id = isset( $_POST['subject_id'] ) ? absint( $_POST['subject_id'] ) : 0;

			if ( $subject_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-subject-' . $subject_id ], 'edit-subject-' . $subject_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-subject'], 'add-subject' ) ) {
					die();
				}
			}

			// Checks if subject exists.
			if ( $subject_id ) {
				$subject = WLSM_M_Staff_Class::get_subject( $school_id, $subject_id );

				if ( ! $subject ) {
					throw new Exception( esc_html__( 'Subject not found.', 'school-management-system' ) );
				}
			}

			$label    = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';
			$code     = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';
			$type     = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
			$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			// Start validation.
			$errors = array();

			if ( empty( $label ) ) {
				$errors['label'] = esc_html__( 'Please provide subject name.', 'school-management-system' );
			} else {
				if ( strlen( $label ) > 100 ) {
					$errors['label'] = esc_html__( 'Maximum length cannot exceed 100 characters.', 'school-management-system' );
				}
			}

			if ( strlen( $code ) > 40 ) {
				$errors['code'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}

			if ( ! in_array( $type, array_keys( WLSM_Helper::subject_type_list() ) ) ) {
				$errors['type'] = esc_html__( 'Please select subject type.', 'school-management-system' );
			}

			if ( empty( $class_id ) ) {
				$errors['class_id'] = esc_html__( 'Please select a class.', 'school-management-system' );
			} else {
				$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );
				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
				} else {
					$class_school_id = $class_school->ID;
				}
			}

			if ( count( $errors ) ) {
				wp_send_json_error( $errors );
			}

			if ( isset( $class_school_id ) ) {
				// Checks if subject name already exists for this class.
				if ( $subject_id ) {
					$subject_exists = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID FROM ' . WLSM_SUBJECTS . ' as sj WHERE sj.class_school_id = %d AND sj.ID != %d AND sj.label = "%s"', $class_school_id, $subject_id, $label ) );
				} else {
					$subject_exists = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID FROM ' . WLSM_SUBJECTS . ' as sj WHERE sj.class_school_id = %d AND sj.label = "%s"', $class_school_id, $label ) );
				}

				if ( $subject_exists ) {
					$errors['label'] = esc_html__( 'Subject name already exists.', 'school-management-system' );
				}

				if ( ! empty( $code ) ) {
					// Checks if subject code already exists for this class.
					if ( $subject_id ) {
						$subject_exists = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID FROM ' . WLSM_SUBJECTS . ' as sj WHERE sj.class_school_id = %d AND sj.ID != %d AND sj.code = "%s"', $class_school_id, $subject_id, $code ) );
					} else {
						$subject_exists = $wpdb->get_row( $wpdb->prepare( 'SELECT sj.ID FROM ' . WLSM_SUBJECTS . ' as sj WHERE sj.class_school_id = %d AND sj.code = "%s"', $class_school_id, $code ) );
					}

					if ( $subject_exists ) {
						$errors['code'] = esc_html__( 'Subject code already exists.', 'school-management-system' );
					}
				}
			} else {
				$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
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

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( $subject_id ) {
					$message = esc_html__( 'Subject updated successfully.', 'school-management-system' );
					$reset   = false;
				} else {
					$message = esc_html__( 'Subject added successfully.', 'school-management-system' );
					$reset   = true;
				}

				// Subject data.
				$data = array(
					'label'            => $label,
					'code'             => $code,
					'type'             => $type,
					'class_school_id'  => $class_school_id,
				);

				if ( $subject_id ) {
					$data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_SUBJECTS, $data, array( 'ID' => $subject_id ) );
				} else {
					$success = $wpdb->insert( WLSM_SUBJECTS, $data );
				}

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

	public static function delete_subject() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$subject_id = isset( $_POST['subject_id'] ) ? absint( $_POST['subject_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-subject-' . $subject_id ], 'delete-subject-' . $subject_id ) ) {
				die();
			}

			// Checks if subject exists.
			$subject = WLSM_M_Staff_Class::get_subject( $school_id, $subject_id );

			if ( ! $subject ) {
				throw new Exception( esc_html__( 'Subject not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_SUBJECTS, array( 'ID' => $subject_id ) );
			$message = esc_html__( 'Subject deleted successfully.', 'school-management-system' );

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

	public static function fetch_subject_admins() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		global $wpdb;

		$subject_id = isset( $_POST['subject'] ) ? absint( $_POST['subject'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'subject-admins-' . $subject_id ], 'subject-admins-' . $subject_id ) ) {
			die();
		}

		$page_url = WLSM_M_Staff_Class::get_subjects_page_url();

		$query = WLSM_M_Staff_Class::fetch_subject_admins_query( $school_id, $subject_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' GROUP BY a.ID';

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(a.name LIKE "%' . $search_value . '%") OR ' .
				'(a.phone LIKE "%' . $search_value . '%") OR ' .
				'(u.user_login LIKE "%' . $search_value . '%") OR ' .
				'(a.is_active LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'a.name', 'a.phone', 'u.user_login', 'a.is_active' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY a.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Class::fetch_subject_admins_query_count( $school_id, $subject_id );

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
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ),
					esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->username ) ),
					esc_html( WLSM_M_Staff_Class::get_status_text( $row->is_active ) ),
					'<a class="text-danger wlsm-delete-subject-admin" data-nonce="' . esc_attr( wp_create_nonce( 'delete-subject-admin-' . $row->ID ) ) . '" data-subject="' . esc_attr( $subject_id ) . '" data-admin="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will remove this teacher from the subject.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
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

	public static function delete_subject_admin() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$subject_id = isset( $_POST['subject_id'] ) ? absint( $_POST['subject_id'] ) : 0;
			$admin_id   = isset( $_POST['admin_id'] ) ? absint( $_POST['admin_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-subject-admin-' . $admin_id ], 'delete-subject-admin-' . $admin_id ) ) {
				die();
			}

			// Checks if subject exists.
			$subject = WLSM_M_Staff_Class::get_subject( $school_id, $subject_id );

			if ( ! $subject ) {
				throw new Exception( esc_html__( 'Subject not found.', 'school-management-system' ) );
			}

			// Checks if admin exists in the subject
			$admin_subject = WLSM_M_Staff_Class::get_admin_subject( $school_id, $subject_id, $admin_id );

			if ( ! $admin_subject ) {
				throw new Exception( esc_html__( 'Teacher is not assigned to this subject.', 'school-management-system' ) );
			}

			$admin_subject_id = $admin_subject->ID;

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

			$success = $wpdb->delete( WLSM_ADMIN_SUBJECT, array( 'ID' => $admin_subject_id ) );
			$message = esc_html__( 'Teacher removed from the subject successfully.', 'school-management-system' );

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

	public static function get_keyword_admins() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

		$admins = WLSM_M_Staff_Class::get_keyword_active_admins( $school_id, $keyword );

		$admins = array_map( function( $admin ) {
			$admin->label = esc_html( stripcslashes( $admin->label ) );

			if ( $admin->phone ) {
				$admin->label .= ' (' . esc_html( $admin->phone ) . ')';
			}
			unset( $admin->phone );

			if ( $admin->username ) {
				$admin->label .= ' (' . esc_html( $admin->username ) . ')';
			}

			return $admin;
		}, $admins );

		wp_send_json_success( $admins );
	}

	public static function assign_subject_admins() {
		$current_user = WLSM_M_Role::can( 'manage_subjects' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$subject_id = isset( $_POST['subject_id'] ) ? absint( $_POST['subject_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'assign-admins-' . $subject_id ], 'assign-admins-' . $subject_id ) ) {
				die();
			}

			$admins = ( isset( $_POST['admins'] ) && is_array( $_POST['admins'] ) ) ? $_POST['admins'] : array();

			// Checks if subject exists.
			$subject = WLSM_M_Staff_Class::get_subject( $school_id, $subject_id );

			if ( ! $subject ) {
				throw new Exception( esc_html__( 'Subject not found.', 'school-management-system' ) );
			}

			// Start validation.
			$errors = array();

			if ( ! count( $admins ) ) {
				$errors['keyword'] = esc_html__( 'Please select atleast one teacher to assign.', 'school-management-system' );
			} else {
				$admins = WLSM_M_Staff_Class::get_active_admins_ids_in_school( $school_id, $admins );
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

				foreach ( $admins as $admin_id ) {
					array_push( $values, $admin_id, $subject_id );
					array_push( $place_holders, '(%d, %d)' );
				}

				// Insert admin_subject records.
				$sql     = 'INSERT IGNORE INTO ' . WLSM_ADMIN_SUBJECT . ' (admin_id, subject_id) VALUES ';
				$sql     .= implode( ', ', $place_holders );
				$success = $wpdb->query( $wpdb->prepare( "$sql ", $values ) );

				$message = esc_html__( 'Teachers assigned successfully.', 'school-management-system' );

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
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
		wp_send_json_error( $errors );
	}
}
