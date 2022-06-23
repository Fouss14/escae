<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Accountant.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Notify.php';

class WLSM_Staff_Accountant {
	public static function get_invoices() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id     = $current_user['school']['id'];
		$session_id    = $current_user['session']['ID'];
		$session_label = $current_user['session']['label'];

		if ( ! wp_verify_nonce( $_POST['get-invoices'], 'get-invoices' ) ) {
			die();
		}

		$from_table = isset( $_POST['from_table'] ) ? (bool) ( $_POST['from_table'] ) : 0;

		$output = array(
			'draw'            => 1,
			'recordsTotal'    => 0,
			'recordsFiltered' => 0,
			'data'            => [],
		);

		$search_students_by = isset( $_POST['search_students_by'] ) ? sanitize_text_field( $_POST['search_students_by'] ) : '';

		$search_field   = isset( $_POST['search_field'] ) ? sanitize_text_field( $_POST['search_field'] ) : '';
		$search_keyword = isset( $_POST['search_keyword'] ) ? sanitize_text_field( $_POST['search_keyword'] ) : '';

		$class_id   = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
		$section_id = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;

		try {
			ob_start();
			global $wpdb;

			// Start validation.
			$errors = array();

			if ( ! in_array( $search_students_by, array( 'search_by_keyword', 'search_by_class' ) ) ) {
				throw new Exception( esc_html__( 'Please specify search criteria.', 'school-management-system' ) );
			}

			if ( 'search_by_keyword' === $search_students_by ) {
				if ( ! empty( $search_field ) && empty( $search_keyword ) ) {
					$errors['search_keyword'] = esc_html__( 'Please enter search keyword.', 'school-management-system' );
				} else if ( ! empty( $search_keyword ) && empty( $search_field ) ) {
					$errors['search_field'] = esc_html__( 'Please specify search field.', 'school-management-system' );
				}

				$filter = array(
					'search_field'   => $search_field,
					'search_keyword' => $search_keyword,
				);

			} else {
				if ( empty( $class_id ) ) {
					$errors['class_id'] = esc_html__( 'Please select a class.', 'school-management-system' );
				}

				$filter = array(
					'class_id'   => $class_id,
					'section_id' => $section_id,
				);
			}

		} catch ( Exception $exception ) {
			if ( $from_table ) {
				echo json_encode( $output );
				die();
			}
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			if ( ! $from_table ) {
				wp_send_json_success();
			}
			try {
				$filter['search_by'] = $search_students_by;

				$page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

				$query = WLSM_M_Staff_Accountant::fetch_invoices_query( $school_id, $session_id, $filter );

				$query_filter = $query;

				// Grouping.
				$group_by = ' ' . WLSM_M_Staff_Accountant::fetch_invoices_query_group_by();

				$query        .= $group_by;
				$query_filter .= $group_by;

				// Searching.
				$condition = '';
				if ( isset( $_POST['search']['value'] ) ) {
					$search_value = sanitize_text_field( $_POST['search']['value'] );
					if ( '' !== $search_value ) {
						$condition .= '' .
						'(i.invoice_number LIKE "%' . $search_value . '%") OR ' .
						'(i.label LIKE "%' . $search_value . '%") OR ' .
						'(sr.name LIKE "%' . $search_value . '%") OR ' .
						'(sr.admission_number LIKE "%' . $search_value . '%") OR ' .
						'(sr.enrollment_number LIKE "%' . $search_value . '%") OR ' .
						'(sr.phone LIKE "%' . $search_value . '%") OR ' .
						'(c.label LIKE "%' . $search_value . '%") OR ' .
						'(se.label LIKE "%' . $search_value . '%")';

						$search_value_lowercase = strtolower( $search_value );
						if ( preg_match( '/^paid$/', $search_value_lowercase ) ) {
							$status = WLSM_M_Invoice::get_paid_key();
						} else if ( preg_match( '/^unpa(|i|id)$/', $search_value_lowercase ) ) {
							$status = WLSM_M_Invoice::get_unpaid_key();
						} else if ( preg_match( '/^partially(| p| pa| pai| paid)$/', $search_value_lowercase ) ) {
							$status = WLSM_M_Invoice::get_partially_paid_key();
						}

						if ( isset( $status ) ) {
							$condition .= ' OR (i.status = "' . $status . '")';
						}

						$date_issued = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

						if ( $date_issued ) {
							$format_date_issued = 'Y-m-d';
						} else {
							if ( 'd-m-Y' === WLSM_Config::date_format() ) {
								if ( ! $date_issued ) {
									$date_issued        = DateTime::createFromFormat( 'm-Y', $search_value );
									$format_date_issued = 'Y-m';
								}
							} else if ( 'd/m/Y' === WLSM_Config::date_format() ) {
								if ( ! $date_issued ) {
									$date_issued        = DateTime::createFromFormat( 'm/Y', $search_value );
									$format_date_issued = 'Y-m';
								}
							} else if ( 'Y-m-d' === WLSM_Config::date_format() ) {
								if ( ! $date_issued ) {
									$date_issued        = DateTime::createFromFormat( 'Y-m', $search_value );
									$format_date_issued = 'Y-m';
								}
							} else if ( 'Y/m/d' === WLSM_Config::date_format() ) {
								if ( ! $date_issued ) {
									$date_issued        = DateTime::createFromFormat( 'Y/m', $search_value );
									$format_date_issued = 'Y-m';
								}
							}

							if ( ! $date_issued ) {
								$date_issued        = DateTime::createFromFormat( 'Y', $search_value );
								$format_date_issued = 'Y';
							}
						}

						if ( $date_issued && isset( $format_date_issued ) ) {
							$date_issued = $date_issued->format( $format_date_issued );
							$date_issued = ' OR (i.date_issued LIKE "%' . $date_issued . '%")';

							$condition .= $date_issued;
						}

						$due_date = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

						if ( $due_date ) {
							$format_due_date = 'Y-m-d';
						} else {
							if ( 'd-m-Y' === WLSM_Config::date_format() ) {
								if ( ! $due_date ) {
									$due_date        = DateTime::createFromFormat( 'm-Y', $search_value );
									$format_due_date = 'Y-m';
								}
							} else if ( 'd/m/Y' === WLSM_Config::date_format() ) {
								if ( ! $due_date ) {
									$due_date        = DateTime::createFromFormat( 'm/Y', $search_value );
									$format_due_date = 'Y-m';
								}
							} else if ( 'Y-m-d' === WLSM_Config::date_format() ) {
								if ( ! $due_date ) {
									$due_date        = DateTime::createFromFormat( 'Y-m', $search_value );
									$format_due_date = 'Y-m';
								}
							} else if ( 'Y/m/d' === WLSM_Config::date_format() ) {
								if ( ! $due_date ) {
									$due_date        = DateTime::createFromFormat( 'Y/m', $search_value );
									$format_due_date = 'Y-m';
								}
							}

							if ( ! $due_date ) {
								$due_date        = DateTime::createFromFormat( 'Y', $search_value );
								$format_due_date = 'Y';
							}
						}

						if ( $due_date && isset( $format_due_date ) ) {
							$due_date = $due_date->format( $format_due_date );
							$due_date = ' OR (i.due_date LIKE "%' . $due_date . '%")';

							$condition .= $due_date;
						}

						$query_filter .= ( ' HAVING ' . $condition );
					}
				}

				// Ordering.
				$columns = array( 'sr.name', 'sr.admission_number', 'i.invoice_number', 'i.label', 'payable', 'paid', 'due', 'i.status', 'i.date_issued', 'i.due_date', 'sr.phone', 'c.label', 'se.label', 'sr.enrollment_number' );
				if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
					$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
					$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

					$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
				} else {
					$query_filter .= ' ORDER BY i.ID DESC';
				}

				// Limiting.
				$limit = '';
				if ( -1 != $_POST['length'] ) {
					$start  = absint( $_POST['start'] );
					$length = absint( $_POST['length'] );

					$limit  = ' LIMIT ' . $start . ', ' . $length;
				}

				// Total query.
				$rows_query = WLSM_M_Staff_Accountant::fetch_invoices_query_count( $school_id, $session_id, $filter );

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
						$due = $row->payable - $row->paid;

						if ( WLSM_M_Invoice::get_paid_key() !== $row->status ) {
							$collect_payment = '<br><a href="' . esc_url( $page_url . '&action=collect_payment&id=' . $row->ID . '#wlsm-fee-invoice-status' ) . '" class="btn wlsm-btn-xs btn-success">' . esc_html__( 'Collect Payment', 'school-management-system' ) . '</a>';
						} else {
							$collect_payment = '';
						}

						// Table columns.
						$data[] = array(
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->student_name ) ),
							esc_html( WLSM_M_Staff_Class::get_admission_no_text( $row->admission_number ) ),
							esc_html( $row->invoice_number ),
							esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_title ) ),
							esc_html( WLSM_Config::get_money_text( $row->payable ) ),
							esc_html( WLSM_Config::get_money_text( $row->paid ) ),
							'<span class="wlsm-font-bold">' . esc_html( WLSM_Config::get_money_text( $due ) ) . '</span>',
							wp_kses(
								WLSM_M_Invoice::get_status_text( $row->status ),
								array( 'span' => array( 'class' => array() ) )
							) . $collect_payment,
							esc_html( WLSM_Config::get_date_text( $row->date_issued ) ),
							esc_html( WLSM_Config::get_date_text( $row->due_date ) ),
							esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
							esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
							esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ),
							esc_html( $row->enrollment_number ),
							'<a class="text-success wlsm-print-invoice" data-nonce="' . esc_attr( wp_create_nonce( 'print-invoice-' . $row->ID ) ) . '" data-invoice="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Print Invoice', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><i class="fas fa-print"></i></a>&nbsp;&nbsp;
							<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
							<a class="text-danger wlsm-delete-invoice" data-nonce="' . esc_attr( wp_create_nonce( 'delete-invoice-' . $row->ID ) ) . '" data-invoice="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . sprintf( esc_attr__( 'This will delete the invoice.', 'school-management-system' ), esc_html( WLSM_M_Session::get_label_text( $session_label ) ) ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
						);
					}
				}

				$output = array(
					'draw'            => intval( $_POST['draw'] ),
					'recordsTotal'    => $total_rows_count,
					'recordsFiltered' => $filter_rows_count,
					'data'            => $data,
					'export'          => array(
						'nonce'  => wp_create_nonce( 'export-staff-invoices-table' ),
						'action' => 'wlsm-export-staff-invoices-table'
					)
				);

				echo json_encode( $output );
				die();
			} catch ( Exception $exception ) {
				if ( $from_table ) {
					echo json_encode( $output );
					die();
				}
				wp_send_json_error( $exception->getMessage() );
			}
		}

		if ( $from_table ) {
			echo json_encode( $output );
			die();
		}
		wp_send_json_error( $errors );
	}

	public static function save_invoice() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

			if ( $invoice_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-invoice-' . $invoice_id ], 'edit-invoice-' . $invoice_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-invoice'], 'add-invoice' ) ) {
					die();
				}
			}

			// Checks if invoice exists.
			if ( $invoice_id ) {
				$invoice = WLSM_M_Staff_Accountant::fetch_invoice( $school_id, $session_id, $invoice_id );

				if ( ! $invoice ) {
					throw new Exception( esc_html__( 'Invoice not found.', 'school-management-system' ) );
				}
			}

			$invoice_title       = isset( $_POST['invoice_label'] ) ? sanitize_text_field( $_POST['invoice_label'] ) : '';
			$invoice_description = isset( $_POST['invoice_description'] ) ? sanitize_text_field( $_POST['invoice_description'] ) : '';
			$invoice_amount      = isset( $_POST['invoice_amount'] ) ? WLSM_Config::sanitize_money( $_POST['invoice_amount'] ) : 0;
			$invoice_discount    = isset( $_POST['invoice_discount'] ) ? WLSM_Config::sanitize_money( $_POST['invoice_discount'] ) : 0;
			$invoice_date_issued = isset( $_POST['invoice_date_issued'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['invoice_date_issued'] ) ) : NULL;
			$invoice_due_date    = isset( $_POST['invoice_due_date'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['invoice_due_date'] ) ) : NULL;
			$partial_payment     = isset( $_POST['partial_payment'] ) ? (bool) $_POST['partial_payment'] : 0;

			if ( ! $invoice_id ) {
				$invoice_type = isset( $_POST['invoice_type'] ) ? sanitize_text_field( $_POST['invoice_type'] ) : '';
			}

			// Start validation.
			$errors = array();

			if ( empty( $invoice_title ) ) {
				$errors['invoice_label'] = esc_html__( 'Please provide invoice title.', 'school-management-system' );
			} else {
				if ( strlen( $invoice_title ) > 50 ) {
					$errors['invoice_label'] = esc_html__( 'Maximum length cannot exceed 100 characters.', 'school-management-system' );
				}
			}

			if ( $invoice_amount <= 0 ) {
				$errors['invoice_amount'] = esc_html__( 'Please specify a valid invoice amount.', 'school-management-system' );
			}

			if ( $invoice_discount > $invoice_amount ) {
				$errors['invoice_discount'] = esc_html__( 'Discount must be lower or equal to invoice amount.', 'school-management-system' );
			}

			if ( $invoice_date_issued > $invoice_due_date ) {
				$errors['invoice_due_date'] = esc_html__( 'Invoice due date must be greater than issued date.', 'school-management-system' );
			}

			if ( empty( $invoice_date_issued ) ) {
				$errors['invoice_date_issued'] = esc_html__( 'Please provide date issued.', 'school-management-system' );
			} else {
				$invoice_date_issued = $invoice_date_issued->format( 'Y-m-d' );
			}

			if ( empty( $invoice_due_date ) ) {
				$invoice_due_date = NULL;
			} else {
				$invoice_due_date = $invoice_due_date->format( 'Y-m-d' );
			}

			if ( ! $invoice_id ) {
				if ( ! in_array( $invoice_type, array( 'single_invoice', 'bulk_invoice' ) ) ) {
					throw new Exception( esc_html__( 'Please select either single invoice or bulk invoice option.', 'school-management-system' ) );
				}

				if ( 'single_invoice' === $invoice_type ) {
					$student_id = isset( $_POST['student'] ) ? absint( $_POST['student'] ) : 0;

					$collect_invoice_payment = isset( $_POST['collect_invoice_payment'] ) ? (bool) $_POST['collect_invoice_payment'] : 0;

					if ( empty( $student_id ) ) {
						$errors['student'] = esc_html__( 'Please select a student.', 'school-management-system' );
						wp_send_json_error( $errors );
					}

					// Checks if student exists.
					$student = WLSM_M_Staff_General::get_student( $school_id, $session_id, $student_id, true );

					if ( ! $student ) {
						throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
					}

					if ( $collect_invoice_payment ) {
						$payment_amount = isset( $_POST['payment_amount'] ) ? WLSM_Config::sanitize_money( $_POST['payment_amount'] ) : 0;
						$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
						$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
						$payment_note   = isset( $_POST['payment_note'] ) ? sanitize_text_field( $_POST['payment_note'] ) : '';

						$due = WLSM_M_Invoice::get_due_amount(
							array(
								'total'    => $invoice_amount,
								'discount' => $invoice_discount,
							)
						);

						$errors = self::validate_invoice_payment( $errors, $partial_payment, $due, $payment_amount, $payment_method );
					}

				} else {
					$student_ids = ( isset( $_POST['student'] ) && is_array( $_POST['student'] ) ) ? $_POST['student'] : array();

					if ( ! count( $student_ids ) ) {
						$errors['student[]'] = esc_html__( 'Please select students.', 'school-management-system' );
					}

					// Checks if students exists.
					$students_count = WLSM_M_Staff_General::get_students_count( $school_id, $session_id, $student_ids, true );

					if ( $students_count != count( $student_ids ) ) {
						throw new Exception( esc_html__( 'Student(s) not found.', 'school-management-system' ) );
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

				// Invoice data.
				$invoice_data = array(
					'label'           => $invoice_title,
					'description'     => $invoice_description,
					'amount'          => $invoice_amount,
					'discount'        => $invoice_discount,
					'date_issued'     => $invoice_date_issued,
					'due_date'        => $invoice_due_date,
					'partial_payment' => $partial_payment,
				);

				// Checks if update or insert.
				if ( $invoice_id ) {
					$message = esc_html__( 'Invoice updated successfully.', 'school-management-system' );
					$reset   = false;

					$invoice_data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_INVOICES, $invoice_data, array( 'ID' => $invoice_id ) );

					$buffer = ob_get_clean();
					if ( ! empty( $buffer ) ) {
						throw new Exception( $buffer );
					}

					WLSM_M_Staff_Accountant::refresh_invoice_status( $invoice_id );
				} else {
					$message = esc_html__( 'Invoice added successfully.', 'school-management-system' );
					$reset   = true;

					if ( 'bulk_invoice' === $invoice_type ) {
						$bulk_invoice_ids = array();
						foreach ( $student_ids as $student_id ) {
							$invoice_number = WLSM_M_Invoice::get_invoice_number( $school_id );

							$invoice_data['invoice_number']    = $invoice_number;
							$invoice_data['student_record_id'] = $student_id;

							$success = $wpdb->insert( WLSM_INVOICES, $invoice_data );

							$bulk_invoice_id = $wpdb->insert_id;
							array_push( $bulk_invoice_ids, $bulk_invoice_id );

							$buffer = ob_get_clean();
							if ( ! empty( $buffer ) ) {
								throw new Exception( $buffer );
							}
						}
					} else if ( ( 'single_invoice' === $invoice_type ) ) {
						$invoice_number = WLSM_M_Invoice::get_invoice_number( $school_id );

						$invoice_data['invoice_number']    = $invoice_number;
						$invoice_data['student_record_id'] = $student_id;

						$success = $wpdb->insert( WLSM_INVOICES, $invoice_data );

						$single_invoice_id = $wpdb->insert_id;

						if ( $collect_invoice_payment ) {
							$invoice_id = $wpdb->insert_id;

							$receipt_number = WLSM_M_Invoice::get_receipt_number( $school_id );

							// Payment data.
							$payment_data = array(
								'receipt_number'    => $receipt_number,
								'amount'            => $payment_amount,
								'payment_method'    => $payment_method,
								'transaction_id'    => $transaction_id,
								'note'              => $payment_note,
								'invoice_label'     => $invoice_title,
								'invoice_payable'   => $due,
								'student_record_id' => $student_id,
								'invoice_id'        => $invoice_id,
								'school_id'         => $school_id,
							);

							$success = $wpdb->insert( WLSM_PAYMENTS, $payment_data );

							$new_payment_id = $wpdb->insert_id;

							$buffer = ob_get_clean();
							if ( ! empty( $buffer ) ) {
								throw new Exception( $buffer );
							}

							WLSM_M_Staff_Accountant::refresh_invoice_status( $invoice_id );
						}
					}
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$wpdb->query( 'COMMIT;' );

				if ( isset( $bulk_invoice_ids ) && count( $bulk_invoice_ids ) > 0 ) {
					foreach ( $bulk_invoice_ids as $bulk_invoice_id ) {
						// Notify for invoice generated.
						$data = array(
							'school_id'  => $school_id,
							'session_id' => $session_id,
							'invoice_id' => $bulk_invoice_id,
						);

						wp_schedule_single_event( time() + 30, 'wlsm_notify_for_invoice_generated', $data );
					}
				} else if ( isset( $single_invoice_id ) ) {
					// Notify for invoice generated.
					$data = array(
						'school_id'  => $school_id,
						'session_id' => $session_id,
						'invoice_id' => $single_invoice_id,
					);

					wp_schedule_single_event( time() + 30, 'wlsm_notify_for_invoice_generated', $data );
				}

				if ( isset( $new_payment_id ) ) {
					// Notify for offline fee submission.
					$data = array(
						'school_id'  => $school_id,
						'session_id' => $session_id,
						'payment_id' => $new_payment_id,
					);

					wp_schedule_single_event( time() + 30, 'wlsm_notify_for_offline_fee_submission', $data );
				}

				wp_send_json_success( array( 'message' => $message, 'reset' => $reset ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function delete_invoice() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-invoice-' . $invoice_id ], 'delete-invoice-' . $invoice_id ) ) {
				die();
			}

			// Checks if invoice exists.
			$invoice = WLSM_M_Staff_Accountant::get_invoice( $school_id, $session_id, $invoice_id );

			if ( ! $invoice ) {
				throw new Exception( esc_html__( 'Invoice not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_INVOICES, array( 'ID' => $invoice_id ) );
			$message = esc_html__( 'Invoice deleted successfully.', 'school-management-system' );

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

	public static function print_invoice() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'print-invoice-' . $invoice_id ], 'print-invoice-' . $invoice_id ) ) {
				die();
			}

			// Checks if invoice exists.
			$invoice = WLSM_M_Staff_Accountant::fetch_invoice( $school_id, $session_id, $invoice_id );

			if ( ! $invoice ) {
				throw new Exception( esc_html__( 'Invoice not found.', 'school-management-system' ) );
			}

			$payments = WLSM_M_Staff_Accountant::get_invoice_payments( $invoice_id );

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		ob_start();
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/invoice.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function print_invoice_fee_structure() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'print-invoice-fee-structure' ], 'print-invoice-fee-structure' ) ) {
				die();
			}

			// Checks if student exists.
			$student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
			}

			$invoices = WLSM_M_Staff_Accountant::get_student_invoices( $student_id );
			$payments = WLSM_M_Staff_Accountant::get_student_payments( $student_id );

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		ob_start();
		require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/student_invoices.php';
		require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/student_payments.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function fetch_invoice_payments() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$current_school = $current_user['school'];

		$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		$invoice_id = isset( $_POST['invoice'] ) ? absint( $_POST['invoice'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'invoice-payments-' . $invoice_id ], 'invoice-payments-' . $invoice_id ) ) {
			die();
		}

		$query = WLSM_M_Staff_Accountant::fetch_invoice_payments_query( $school_id, $session_id, $invoice_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Accountant::fetch_payments_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(p.receipt_number LIKE "%' . $search_value . '%") OR ' .
				'(p.amount LIKE "%' . $search_value . '%") OR ' .
				'(p.transaction_id LIKE "%' . $search_value . '%") OR ' .
				'(p.note LIKE "%' . $search_value . '%")';

				$payment_method = strtolower( preg_replace( '/[^A-Za-z0-9-]+/', '-', $search_value ) );
				if ( isset( $payment_method ) ) {
					$condition .= ' OR (p.payment_method LIKE "%' . $payment_method . '%")';
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
					$created_at = ' OR (p.created_at LIKE "%' . $created_at . '%")';

					$condition .= $created_at;
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'p.receipt_number', 'p.amount', 'p.payment_method', 'p.transaction_id', 'p.created_at', 'p.note' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY p.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Accountant::fetch_invoice_payments_query_count( $school_id, $session_id, $invoice_id );

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
				if ( $row->note ) {
					$view_note = '<a class="text-primary wlsm-view-payment-note" data-nonce="' . esc_attr( wp_create_nonce( 'view-payment-note-' . $row->ID ) ) . '" data-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Payment Note', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><span class="dashicons dashicons-search"></span></a>';
				} else {
					$view_note = '-';
				}

				// Table columns.
				$columns = array(
					esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ),
					esc_html( WLSM_Config::get_money_text( $row->amount ) ),
					esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ),
					esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ),
					esc_html( WLSM_Config::get_date_text( $row->created_at ) ),
					$view_note,
				);

				$columns[] = '<a class="text-success wlsm-print-invoice-payment" data-nonce="' . esc_attr( wp_create_nonce( 'print-invoice-payment-' . $row->ID ) ) . '" data-invoice-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Print Payment Receipt', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><i class="fas fa-print"></i></a>';

				if ( $can_delete_payments ) {
					$columns[] = '<a class="text-danger wlsm-delete-invoice-payment" data-nonce="' . esc_attr( wp_create_nonce( 'delete-payment-' . $row->ID ) ) . '" data-invoice="' . esc_attr( $invoice_id ) . '" data-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the payment from invoice.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>';
				}

				$data[] = $columns;
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

	public static function collect_invoice_payment() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

		if ( ! wp_verify_nonce( $_POST[ 'collect-invoice-payment-' . $invoice_id ], 'collect-invoice-payment-' . $invoice_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			// Checks if invoice exists.
			$invoice = WLSM_M_Staff_Accountant::fetch_invoice( $school_id, $session_id, $invoice_id );

			if ( ! $invoice ) {
				throw new Exception( esc_html__( 'Invoice not found.', 'school-management-system' ) );
			}

			$invoice_id = $invoice->ID;

			$partial_payment = $invoice->partial_payment;

			$payment_amount = isset( $_POST['payment_amount'] ) ? WLSM_Config::sanitize_money( $_POST['payment_amount'] ) : 0;
			$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
			$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
			$payment_note   = isset( $_POST['payment_note'] ) ? sanitize_text_field( $_POST['payment_note'] ) : '';

			// Start validation.
			$errors = array();

			if ( strlen( $payment_method ) > 50 ) {
				$errors['payment_method'] = esc_html__( 'Maximum length cannot exceed 50 characters.', 'school-management-system' );
			}

			$due = $invoice->payable - $invoice->paid;

			$errors = self::validate_invoice_payment( $errors, $partial_payment, $due, $payment_amount, $payment_method );

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

				$message = esc_html__( 'Payment added successfully.', 'school-management-system' );
				$reset   = true;

				$receipt_number = WLSM_M_Invoice::get_receipt_number( $school_id );

				// Payment data.
				$payment_data = array(
					'receipt_number'    => $receipt_number,
					'amount'            => $payment_amount,
					'transaction_id'    => $transaction_id,
					'payment_method'    => $payment_method,
					'note'              => $payment_note,
					'invoice_label'     => $invoice->invoice_title,
					'invoice_payable'   => $invoice->payable,
					'student_record_id' => $invoice->student_id,
					'invoice_id'        => $invoice_id,
					'school_id'         => $school_id,
				);

				$success = $wpdb->insert( WLSM_PAYMENTS, $payment_data );

				$new_payment_id = $wpdb->insert_id;

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$invoice_status = WLSM_M_Staff_Accountant::refresh_invoice_status( $invoice_id );

				if ( WLSM_M_Invoice::get_paid_key() === $invoice_status && ( $invoice_status !== $invoice->status ) ) {
					$reload = true;
				} else {
					$reload = false;
				}

				$wpdb->query( 'COMMIT;' );


				if ( isset( $new_payment_id ) ) {
					// Notify for offline fee submission.
					$data = array(
						'school_id'  => $school_id,
						'session_id' => $session_id,
						'payment_id' => $new_payment_id,
					);

					wp_schedule_single_event( time() + 30, 'wlsm_notify_for_offline_fee_submission', $data );
				}

				wp_send_json_success( array( 'message' => $message, 'reset' => $reset, 'reload' => $reload ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function delete_invoice_payment() {
		$current_user = WLSM_M_Role::can( 'delete_payments' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$payment_id = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-payment-' . $payment_id ], 'delete-payment-' . $payment_id ) ) {
				die();
			}

			$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

			// Checks if invoice exists.
			$invoice = WLSM_M_Staff_Accountant::get_invoice( $school_id, $session_id, $invoice_id );

			if ( ! $invoice ) {
				throw new Exception( esc_html__( 'Invoice not found.', 'school-management-system' ) );
			}

			$invoice_id = $invoice->ID;

			// Checks if payment exists.
			$payment = WLSM_M_Staff_Accountant::get_invoice_payment( $invoice_id, $payment_id );

			if ( ! $payment ) {
				throw new Exception( esc_html__( 'Payment not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_PAYMENTS, array( 'ID' => $payment_id ) );
			$message = esc_html__( 'Payment deleted successfully.', 'school-management-system' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$invoice_status = WLSM_M_Staff_Accountant::refresh_invoice_status( $invoice_id );

			if ( WLSM_M_Invoice::get_paid_key() === $invoice->status && ( $invoice_status !== $invoice->status ) ) {
				$reload = true;
			} else {
				$reload = false;
			}

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message, 'reload' => $reload ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function validate_invoice_payment( $errors, $partial_payment, $due, $payment_amount, $payment_method ) {
		if ( $payment_amount <= 0 ) {
			$errors['payment_amount'] = esc_html__( 'Please provide a valid amount.', 'school-management-system' );
		} else {
			if ( $payment_amount > $due ) {
				$errors['payment_amount'] = sprintf(
					/* translators: %s: payable amount */
					__( 'Amount cannot exceed payable amount: %s', 'school-management-system' ),
					WLSM_Config::get_money_text( $due )
				);
			}

			if ( ! $partial_payment && ( $payment_amount != $due ) ) {
				$errors['payment_amount'] = sprintf(
					/* translators: %s: payable amount */
					__( 'Partial payment is not allowed. Amount must be equal to payable amount: %s', 'school-management-system' ),
					WLSM_Config::get_money_text( $due )
				);
			}
		}

		if ( strlen( $payment_method ) > 50 ) {
			$errors['payment_method'] = esc_html__( 'Maximum length cannot exceed 50 characters.', 'school-management-system' );
		}

		if ( ! in_array( $payment_method, array_keys( WLSM_M_Invoice::collect_payment_methods() ) ) ) {
			$errors['payment_method'] = esc_html__( 'Please select a valid payment method.', 'school-management-system' );
		}

		return $errors;
	}

	public static function fetch_payments() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$current_school = $current_user['school'];

		$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		$page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

		$query = WLSM_M_Staff_Accountant::fetch_payments_query( $school_id, $session_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_Accountant::fetch_payments_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(p.receipt_number LIKE "%' . $search_value . '%") OR ' .
				'(p.amount LIKE "%' . $search_value . '%") OR ' .
				'(p.transaction_id LIKE "%' . $search_value . '%") OR ' .
				'(p.note LIKE "%' . $search_value . '%") OR ' .
				'(sr.name LIKE "%' . $search_value . '%") OR ' .
				'(sr.admission_number LIKE "%' . $search_value . '%") OR ' .
				'(sr.father_name LIKE "%' . $search_value . '%") OR ' .
				'(sr.father_phone LIKE "%' . $search_value . '%") OR ' .
				'(sr.enrollment_number LIKE "%' . $search_value . '%") OR ' .
				'(i.label LIKE "%' . $search_value . '%") OR ' .
				'(c.label LIKE "%' . $search_value . '%") OR ' .
				'(se.label LIKE "%' . $search_value . '%")';

				$payment_method = strtolower( preg_replace( '/[^A-Za-z0-9-]+/', '-', $search_value ) );
				if ( isset( $payment_method ) ) {
					$condition .= ' OR (p.payment_method LIKE "%' . $payment_method . '%")';
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
					$created_at = ' OR (p.created_at LIKE "%' . $created_at . '%")';

					$condition .= $created_at;
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'p.receipt_number', 'p.amount', 'p.payment_method', 'p.transaction_id', 'p.created_at', 'p.note', 'i.label', 'sr.name', 'sr.admission_number', 'c.label', 'se.label', 'sr.enrollment_number', 'sr.phone', 'sr.father_name', 'sr.father_phone' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY p.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Accountant::fetch_payments_query_count( $school_id, $session_id );

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
				if ( $row->invoice_id ) {
					$invoice_title = '<a target="_blank" href="' . esc_url( $page_url . '&action=save&id=' . $row->invoice_id ) . '">' . esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_title ) ) . '</a>';
				} else {
					$invoice_title = '<span class="text-danger">' . esc_html__( 'Deleted', 'school-management-system' ) . '<br><span class="text-secondary">' . esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_label ) ) . '<br><small>' . esc_html( WLSM_Config::get_money_text( $row->invoice_payable ) )  . ' ' . esc_html__( 'Payable', 'school-management-system' ) . '</small></span></span>';
				}

				if ( $row->note ) {
					$view_note = '<a class="text-primary wlsm-view-payment-note" data-nonce="' . esc_attr( wp_create_nonce( 'view-payment-note-' . $row->ID ) ) . '" data-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Payment Note', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><span class="dashicons dashicons-search"></span></a>';
				} else {
					$view_note = '-';
				}

				// Table columns.
				$columns = array(
					esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ),
					esc_html( WLSM_Config::get_money_text( $row->amount ) ),
					esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ),
					esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ),
					esc_html( WLSM_Config::get_date_text( $row->created_at ) ),
					$view_note,
					$invoice_title,
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->student_name ) ),
					esc_html( WLSM_M_Staff_Class::get_admission_no_text( $row->admission_number ) ),
					esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
					esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ),
					esc_html( $row->enrollment_number ),
					esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->father_name ) ),
					esc_html( WLSM_M_Staff_Class::get_phone_text( $row->father_phone ) ),
					'<a class="text-success wlsm-print-invoice-payment" data-nonce="' . esc_attr( wp_create_nonce( 'print-invoice-payment-' . $row->ID ) ) . '" data-invoice-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Print Payment Receipt', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><i class="fas fa-print"></i></a>'
				);

				if ( $can_delete_payments ) {
					$columns[] = '<a class="text-danger wlsm-delete-payment" data-nonce="' . esc_attr( wp_create_nonce( 'delete-payment-' . $row->ID ) ) . '" data-payment="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the payment.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>';
				}

				$data[] = $columns;
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

	public static function delete_payment() {
		$current_user = WLSM_M_Role::can( 'delete_payments' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$payment_id = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-payment-' . $payment_id ], 'delete-payment-' . $payment_id ) ) {
				die();
			}

			// Checks if payment exists.
			$payment = WLSM_M_Staff_Accountant::get_payment( $school_id, $session_id, $payment_id );

			if ( ! $payment ) {
				throw new Exception( esc_html__( 'Payment not found.', 'school-management-system' ) );
			}

			$invoice_id = $payment->invoice_id;

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

			$success = $wpdb->delete( WLSM_PAYMENTS, array( 'ID' => $payment_id ) );
			$message = esc_html__( 'Payment deleted successfully.', 'school-management-system' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}

			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			if ( $invoice_id ) {
				$invoice_status = WLSM_M_Staff_Accountant::refresh_invoice_status( $invoice_id );
			}

			$wpdb->query( 'COMMIT;' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function view_payment_note() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$payment_id = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'view-payment-note-' . $payment_id ], 'view-payment-note-' . $payment_id ) ) {
				die();
			}

			// Checks if payment exists.
			$payment = WLSM_M_Staff_Accountant::get_payment_note( $school_id, $session_id, $payment_id );

			if ( ! $payment ) {
				throw new Exception( esc_html__( 'Payment not found.', 'school-management-system' ) );
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

		wp_send_json_success( esc_html( WLSM_Config::get_note_text( $payment->note ) ) );
	}

	public static function print_payment() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$payment_id = isset( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'print-invoice-payment-' . $payment_id ], 'print-invoice-payment-' . $payment_id ) ) {
				die();
			}

			// Checks if payment exists.
			$payment = WLSM_M_Staff_Accountant::fetch_payment( $school_id, $session_id, $payment_id );

			if ( ! $payment ) {
				throw new Exception( esc_html__( 'Payment not found.', 'school-management-system' ) );
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

		ob_start();
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/payment.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
