<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Notify.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Admin.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Email.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

class WLSM_Staff_General {
	public static function get_class_sections() {
		$current_user = WLSM_M_Role::can( array( 'manage_admissions', 'manage_students', 'manage_invoices' ) );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['nonce'], 'get-class-sections' ) ) {
				die();
			}

			$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			$all_sections = isset( $_POST['all_sections'] ) ? absint( $_POST['all_sections'] ) : 0;

			// Checks if class exists in the school.
			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

			if ( ! $class_school ) {
				throw new Exception( esc_html__( 'Class not found.', 'school-management-system' ) );
			}

			$class_school_id = $class_school->ID;

			$sections = WLSM_M_Staff_General::fetch_class_sections( $class_school_id );

			if ( $all_sections ) {
				$all_sections = (object) array( 'ID' => '', 'label' => esc_html__( 'All Sections', 'school-management-system' ) );
				array_unshift( $sections , $all_sections );
			}

			$sections = array_map( function( $section ) {
				$section->label = WLSM_M_Staff_Class::get_section_label_text( $section->label );
				return $section;
			}, $sections );

			wp_send_json( $sections );
		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json( array() );
		}
	}

	public static function get_section_students() {
		$current_user = WLSM_M_Role::can( array( 'manage_invoices' ) );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['nonce'], 'get-section-students' ) ) {
				die();
			}

			$class_id   = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
			$section_id = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;

			$only_active = isset( $_POST['only_active'] ) ? (bool) $_POST['only_active'] : 1;

			// Checks if class exists in the school.
			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

			if ( ! $class_school ) {
				throw new Exception( esc_html__( 'Class not found.', 'school-management-system' ) );
			}

			$class_school_id = $class_school->ID;

			if ( $section_id ) {
				// Checks if section exists.
				$section = WLSM_M_Staff_Class::get_section( $school_id, $section_id, $class_school_id );

				if ( ! $section ) {
					throw new Exception( esc_html__( 'Section not found.', 'school-management-system' ) );
				}

				$students = WLSM_M_Staff_General::fetch_section_students( $session_id, $section->ID, $only_active );

			} else {
				$students = WLSM_M_Staff_General::fetch_class_students( $session_id, $class_school_id, $only_active );
			}

			wp_send_json( $students );
		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json( array() );
		}
	}

	public static function add_admission() {
		$current_user = WLSM_M_Role::can( 'manage_admissions' );
		self::save_student( $current_user );
	}

	public static function edit_student() {
		$current_user = WLSM_M_Role::can( 'manage_students' );
		self::save_student( $current_user );
	}

	public static function save_student( $current_user ) {
		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$page_url = WLSM_M_Staff_General::get_students_page_url();

		try {
			ob_start();
			global $wpdb;

			$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

			if ( $student_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-student-' . $student_id ], 'edit-student-' . $student_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-admission'], 'add-admission' ) ) {
					die();
				}
			}

			$user_id = NULL;

			// Checks if student exists.
			if ( $student_id ) {
				$student = WLSM_M_Staff_General::get_student( $school_id, $session_id, $student_id );

				if ( ! $student ) {
					throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
				}

				$user_id = $student->user_id;
			}

			// Personal Detail.
			$name          = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$gender        = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
			$dob           = isset( $_POST['dob'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['dob'] ) ) : NULL;
			$address       = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
			$email         = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			$phone         = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
			$religion      = isset( $_POST['religion'] ) ? sanitize_text_field( $_POST['religion'] ) : '';
			$caste         = isset( $_POST['caste'] ) ? sanitize_text_field( $_POST['caste'] ) : '';
			$blood_group   = isset( $_POST['blood_group'] ) ? sanitize_text_field( $_POST['blood_group'] ) : '';

			// Admission Detail.
			$admission_date    = isset( $_POST['admission_date'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['admission_date'] ) ) : NULL;
			$class_id          = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
			$section_id        = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;
			$admission_number  = isset( $_POST['admission_number'] ) ? sanitize_text_field( $_POST['admission_number'] ) : '';
			$roll_number       = isset( $_POST['roll_number'] ) ? sanitize_text_field( $_POST['roll_number'] ) : '';
			$photo             = ( isset( $_FILES['photo'] ) && is_array( $_FILES['photo'] ) ) ? $_FILES['photo'] : NULL;

			// Parent Detail.
			$father_name       = isset( $_POST['father_name'] ) ? sanitize_text_field( $_POST['father_name'] ) : '';
			$father_phone      = isset( $_POST['father_phone'] ) ? sanitize_text_field( $_POST['father_phone'] ) : '';
			$father_occupation = isset( $_POST['father_occupation'] ) ? sanitize_text_field( $_POST['father_occupation'] ) : '';
			$mother_name       = isset( $_POST['mother_name'] ) ? sanitize_text_field( $_POST['mother_name'] ) : '';
			$mother_phone      = isset( $_POST['mother_phone'] ) ? sanitize_text_field( $_POST['mother_phone'] ) : '';
			$mother_occupation = isset( $_POST['mother_occupation'] ) ? sanitize_text_field( $_POST['mother_occupation'] ) : '';

			// Student Login Detail.
			$new_or_existing   = isset( $_POST['student_new_or_existing'] ) ? sanitize_text_field( $_POST['student_new_or_existing'] ) : '';
			$existing_username = isset( $_POST['existing_username'] ) ? sanitize_text_field( $_POST['existing_username'] ) : '';
			$new_login_email   = isset( $_POST['new_login_email'] ) ? sanitize_text_field( $_POST['new_login_email'] ) : '';
			$new_password      = isset( $_POST['new_password'] ) ? sanitize_text_field( $_POST['new_password'] ) : '';
			$username          = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
			$login_email       = isset( $_POST['login_email'] ) ? sanitize_text_field( $_POST['login_email'] ) : '';
			$password          = isset( $_POST['password'] ) ? $_POST['password'] : '';

			// Status.
			$is_active = isset( $_POST['is_active'] ) ? (bool) $_POST['is_active'] : 1;

			if ( $student_id ) {
				$class_id = $student->class_id;
			} else {
				$inquiry_id = isset( $_POST['inquiry_id'] ) ? absint( $_POST['inquiry_id'] ) : 0;
			}

			// Start validation.
			$errors = array();

			// Personal Detail.
			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please specify student name.', 'school-management-system' );
			}
			if ( strlen( $name ) > 60 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $religion ) && strlen( $religion ) > 40 ) {
				$errors['religion'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $caste ) && strlen( $caste ) > 40 ) {
				$errors['caste'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $phone ) && strlen( $phone ) > 40 ) {
				$errors['phone'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $email ) ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				} elseif ( strlen( $email ) > 60 ) {
					$errors['email'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
				}
			}
			if ( ! in_array( $gender, array_keys( WLSM_Helper::gender_list() ) ) ) {
				throw new Exception( esc_html__( 'Please specify gender.', 'school-management-system' ) );
			}
			if ( ! empty( $blood_group ) && ! in_array( $blood_group, array_keys( WLSM_Helper::blood_group_list() ) ) ) {
				throw new Exception( esc_html__( 'Please specify blood group.', 'school-management-system' ) );
			}
			if ( ! empty( $dob ) ) {
				$dob = $dob->format( 'Y-m-d' );
			} else {
				$dob = NULL;
			}

			// Admission Detail.
			if ( empty( $admission_date ) ) {
				$errors['admission_date'] = esc_html__( 'Please provide admission date.', 'school-management-system' );
			} else {
				$admission_date = $admission_date->format( 'Y-m-d' );
			}
			if ( empty( $admission_number ) ) {
				$errors['admission_number'] = esc_html__( 'Please provide admission number.', 'school-management-system' );
			}
			if ( strlen( $admission_number ) > 60 ) {
				$errors['admission_number'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $roll_number ) && strlen( $roll_number ) > 30 ) {
				$errors['roll_number'] = esc_html__( 'Maximum length cannot exceed 30 characters.', 'school-management-system' );
			}
			if ( ! $student_id ) {
				if ( empty( $class_id ) ) {
					$errors['class_id'] = esc_html__( 'Please select a class.', 'school-management-system' );
					wp_send_json_error( $errors );
				}
			}
			if ( empty( $section_id ) ) {
				$errors['section_id'] = esc_html__( 'Please select section.', 'school-management-system' );
				wp_send_json_error( $errors );
			}
			if ( isset( $photo['tmp_name'] ) && ! empty( $photo['tmp_name'] ) ) {
				$finfo = finfo_open( FILEINFO_MIME_TYPE );
				$mime  = finfo_file( $finfo, $photo['tmp_name'] );
				finfo_close($finfo);

				if ( ! in_array( $mime, WLSM_Helper::get_image_mime() ) ) {
					$errors['photo'] = esc_html__( 'Please provide photo in JPG, JPEG or PNG format.', 'school-management-system' );
				}
			}

			// Parent Detail.
			if ( ! empty( $father_name ) && strlen( $father_name ) > 60 ) {
				$errors['father_name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $father_phone ) && strlen( $father_phone ) > 40 ) {
				$errors['father_phone'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $father_occupation ) && strlen( $father_occupation ) > 60 ) {
				$errors['father_occupation'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $mother_name ) && strlen( $mother_name ) > 60 ) {
				$errors['mother_name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $mother_phone ) && strlen( $mother_phone ) > 40 ) {
				$errors['mother_phone'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $mother_occupation ) && strlen( $mother_occupation ) > 60 ) {
				$errors['mother_occupation'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}

			// Checks if class exists in the school.
			if ( $student_id ) {
				$class_school_id = $student->class_school_id;
			} else {
				$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );
				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
					wp_send_json_error( $errors );
				}

				$class_school_id = $class_school->ID;
			}

			// Checks if section exists.
			$section = WLSM_M_Staff_Class::get_section( $school_id, $section_id, $class_school_id );
			if ( ! $section ) {
				$errors['section_id'] = esc_html__( 'Section not found.', 'school-management-system' );
				wp_send_json_error( $errors );
			}

			// Checks if admission number already exists for this session.
			if ( ! $student_id ) {
				$student_exists = WLSM_M_Staff_General::get_admitted_student_id( $school_id, $session_id, $admission_number );

				if ( $student_exists ) {
					$errors['admission_number'] = esc_html__( 'Admission number already exists in this session.', 'school-management-system' );
				}
			}

			// Checks if roll number already exists in the class for this session.
			if ( ! empty( $roll_number ) ) {
				if ( $student_id ) {
					$student_exists = WLSM_M_Staff_General::get_student_with_roll_number( $school_id, $session_id, $class_id, $roll_number, $student_id );
				} else {
					$student_exists = WLSM_M_Staff_General::get_student_with_roll_number( $school_id, $session_id, $class_id, $roll_number );
				}

				if ( $student_exists ) {
					$errors['roll_number'] = esc_html__( 'Roll number already exists in this class.', 'school-management-system' );
				}
			}

			// Student Login Detail.
			if ( 'existing_user' === $new_or_existing ) {
				if ( ! $user_id ) {
					if ( empty( $existing_username ) ) {
						$errors['existing_username'] = esc_html__( 'Please provide existing username.', 'school-management-system' );
					}
				} else {
					if ( empty( $new_login_email ) ) {
						$errors['new_login_email'] = esc_html__( 'Please provide login email.', 'school-management-system' );
					}
				}
			} elseif ( 'new_user' === $new_or_existing ) {
				if ( empty( $username ) ) {
					$errors['username'] = esc_html__( 'Please provide username.', 'school-management-system' );
				}
				if ( empty( $login_email ) ) {
					$errors['login_email'] = esc_html__( 'Please provide login email.', 'school-management-system' );
				}
				if ( ! filter_var( $login_email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['login_email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				}
				if ( empty( $password ) ) {
					$errors['password'] = esc_html__( 'Please provide login password.', 'school-management-system' );
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

				if ( $student_id ) {
					$message = esc_html__( 'Student updated successfully.', 'school-management-system' );
				} else {
					$message = esc_html__( 'Admission added successfully.', 'school-management-system' );
				}

				// Student user data.
				$update_student_user_id = NULL;
				if ( 'existing_user' === $new_or_existing ) {
					if ( ! $user_id ) {
						// Existing user.
						$user = get_user_by( 'login', $existing_username );
						if ( ! $user ) {
							throw new Exception( esc_html__( 'Username does not exist.', 'school-management-system' ) );
						}

						$user_id = $user->ID;

						// Check if user already has a student record.
						if ( $student_id ) {
							$user_has_student_record = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID FROM ' . WLSM_STUDENT_RECORDS . ' as sr WHERE sr.user_id = %d AND sr.ID != %d', $user_id, $student_id ) );
						} else {
							$user_has_student_record = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID FROM ' . WLSM_STUDENT_RECORDS . ' as sr WHERE sr.user_id = %d', $user_id ) );
						}

						if ( $user_has_student_record ) {
							throw new Exception( esc_html__( 'The user already has a student record.', 'school-management-system' ) );
						}

						if ( ! $student_id ) {
							$staff = WLSM_M_Admin::staff_in_school( $school_id, $user_id );

							if ( $staff ) {
								throw new Exception(
									/* translators: %s: role */
									sprintf( esc_html__( 'User already exists with this username having a role of "%s".', 'school-management-system' ), WLSM_M_Role::get_role_text( $staff->role ) )
								);
							}

							if ( user_can( $user_id, WLSM_ADMIN_CAPABILITY ) ) {
								throw new Exception( esc_html__( 'User is a multi-school administrator.', 'school-management-system' ) );
							}
						}
					} else {
						// Update email and password of existing user.
						$user_data = array(
							'ID'         => $user_id,
							'user_email' => $new_login_email,
						);

						if ( ! empty( $new_password ) ) {
							$user_data['user_pass'] = $new_password;
						}

						$user_id = wp_update_user( $user_data );
						if ( is_wp_error( $user_id ) ) {
							throw new Exception( $user_id->get_error_message() );
						}
					}

				} elseif ( 'new_user' === $new_or_existing ) {
					// New user.
					$user_data = array(
						'user_email' => $login_email,
						'user_login' => $username,
						'user_pass'  => $password,
					);

					$user_id = wp_insert_user( $user_data );
					if ( is_wp_error( $user_id ) ) {
						throw new Exception( $user_id->get_error_message() );
					}
				} else {
					$user_id = NULL;
				}

				$update_student_user_id = $user_id;

				// Student record data.
				$student_record_data = array(
					'admission_number'  => $admission_number,
					'name'              => $name,
					'gender'            => $gender,
					'dob'               => $dob,
					'phone'             => $phone,
					'email'             => $email,
					'address'           => $address,
					'religion'          => $religion,
					'caste'             => $caste,
					'blood_group'       => $blood_group,
					'father_name'       => $father_name,
					'father_phone'      => $father_phone,
					'father_occupation' => $father_occupation,
					'mother_name'       => $mother_name,
					'mother_phone'      => $mother_phone,
					'mother_occupation' => $mother_occupation,
					'admission_date'    => $admission_date,
					'roll_number'       => $roll_number,
					'section_id'        => $section_id,
					'user_id'           => $update_student_user_id,
					'is_active'         => $is_active,
				);

				if ( $student_id ) {
					$student_record_data['photo_id'] = $student->photo_id;
				}

				if ( ! empty( $photo ) ) {
					$photo = media_handle_upload( 'photo', 0 );
					if ( is_wp_error( $photo ) ) {
						throw new Exception( $photo->get_error_message() );
					}
					$student_record_data['photo_id'] = $photo;
				}

				if ( $student_id ) {
					$student_record_data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_STUDENT_RECORDS, $student_record_data, array( 'ID' => $student_id ) );

					$is_insert = false;
				} else {
					$student_record_data['session_id'] = $session_id;

					$enrollment_number = WLSM_M_Staff_General::get_enrollment_number( $school_id );

					$student_record_data['enrollment_number'] = $enrollment_number;

					$success = $wpdb->insert( WLSM_STUDENT_RECORDS, $student_record_data );

					$new_student_id = $wpdb->insert_id;
					$student_id     = $new_student_id;

					$is_insert = true;

					$message .= '&nbsp;<a href="' . esc_url( $page_url ) . '&action=save&id=' . $student_id . '">' . esc_html__( 'Edit Student', 'school-management-system' ) . '</a>';

					if ( $inquiry_id ) {
						// Update inquiry status to inactive.
						$inquiry_data = array(
							'is_active'  => 0,
							'updated_at' => date( 'Y-m-d H:i:s' )
						);

						$wpdb->update(
							WLSM_INQUIRIES,
							$inquiry_data,
							array( 'ID' => $inquiry_id, 'school_id' => $school_id )
						);
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

				if ( isset( $new_student_id ) ) {
					// Notify for student admission.
					$data = array(
						'school_id'  => $school_id,
						'session_id' => $session_id,
						'student_id' => $new_student_id,
						'password'   => $password,
					);

					wp_schedule_single_event( time() + 30, 'wlsm_notify_for_student_admission', $data );
				}

				wp_send_json_success( array( 'message' => $message ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function get_students() {
		$current_user = WLSM_M_Role::can( 'manage_students' );

		if ( ! $current_user ) {
			die();
		}

		$school_id     = $current_user['school']['id'];
		$session_id    = $current_user['session']['ID'];
		$session_label = $current_user['session']['label'];

		if ( ! wp_verify_nonce( $_POST['get-students'], 'get-students' ) ) {
			die();
		}

		$from_table = isset( $_POST['from_table'] ) ? (bool) ( $_POST['from_table'] ) : false;

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
				} elseif ( ! empty( $search_keyword ) && empty( $search_field ) ) {
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

				$page_url = WLSM_M_Staff_General::get_students_page_url();

				$query = WLSM_M_Staff_General::fetch_students_query( $school_id, $session_id, $filter );

				$query_filter = $query;

				// Grouping.
				$group_by = ' ' . WLSM_M_Staff_General::fetch_students_query_group_by();

				$query        .= $group_by;
				$query_filter .= $group_by;

				// Searching.
				$condition = '';
				if ( isset( $_POST['search']['value'] ) ) {
					$search_value = sanitize_text_field( $_POST['search']['value'] );
					if ( '' !== $search_value ) {
						$condition .= '' .
						'(sr.name LIKE "%' . $search_value . '%") OR ' .
						'(sr.admission_number LIKE "%' . $search_value . '%") OR ' .
						'(sr.enrollment_number LIKE "%' . $search_value . '%") OR ' .
						'(sr.phone LIKE "%' . $search_value . '%") OR ' .
						'(sr.email LIKE "%' . $search_value . '%") OR ' .
						'(sr.father_name LIKE "%' . $search_value . '%") OR ' .
						'(sr.father_phone LIKE "%' . $search_value . '%") OR ' .
						'(u.user_email LIKE "%' . $search_value . '%") OR ' .
						'(u.user_login LIKE "%' . $search_value . '%") OR ' .
						'(c.label LIKE "%' . $search_value . '%") OR ' .
						'(se.label LIKE "%' . $search_value . '%") OR ' .
						'(sr.roll_number LIKE "%' . $search_value . '%")';

						$search_value_lowercase = strtolower( $search_value );
						if ( preg_match( '/^inac(|t|ti|tiv|tive)$/', $search_value_lowercase ) ) {
							$is_active = 0;
						} elseif ( preg_match( '/^acti(|v|ve)$/', $search_value_lowercase ) ) {
							$is_active = 1;
						}
						if ( isset( $is_active ) ) {
							$condition .= ' OR (sr.is_active = ' . $is_active . ')';
						}

						$admission_date = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

						if ( $admission_date ) {
							$format_admission_date = 'Y-m-d';
						} else {
							if ( 'd-m-Y' === WLSM_Config::date_format() ) {
								if ( ! $admission_date ) {
									$admission_date        = DateTime::createFromFormat( 'm-Y', $search_value );
									$format_admission_date = 'Y-m';
								}
							} elseif ( 'd/m/Y' === WLSM_Config::date_format() ) {
								if ( ! $admission_date ) {
									$admission_date        = DateTime::createFromFormat( 'm/Y', $search_value );
									$format_admission_date = 'Y-m';
								}
							} elseif ( 'Y-m-d' === WLSM_Config::date_format() ) {
								if ( ! $admission_date ) {
									$admission_date        = DateTime::createFromFormat( 'Y-m', $search_value );
									$format_admission_date = 'Y-m';
								}
							} elseif ( 'Y/m/d' === WLSM_Config::date_format() ) {
								if ( ! $admission_date ) {
									$admission_date        = DateTime::createFromFormat( 'Y/m', $search_value );
									$format_admission_date = 'Y-m';
								}
							}

							if ( ! $admission_date ) {
								$admission_date        = DateTime::createFromFormat( 'Y', $search_value );
								$format_admission_date = 'Y';
							}
						}

						if ( $admission_date && isset( $format_admission_date ) ) {
							$admission_date = $admission_date->format( $format_admission_date );
							$admission_date = ' OR (sr.admission_date LIKE "%' . $admission_date . '%")';

							$condition .= $admission_date;
						}

						$query_filter .= ( ' HAVING ' . $condition );
					}
				}

				// Ordering.
				$columns = array( 'sr.name', 'sr.admission_number', 'sr.phone', 'sr.email', 'c.label', 'se.label', 'sr.roll_number', 'sr.father_name', 'sr.father_phone', 'u.user_email', 'u.user_login', 'sr.admission_date', 'sr.enrollment_number', 'sr.is_active' );
				if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
					$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
					$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

					$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
				} else {
					$query_filter .= ' ORDER BY sr.ID DESC';
				}

				// Limiting.
				$limit = '';
				if ( -1 != $_POST['length'] ) {
					$start  = absint( $_POST['start'] );
					$length = absint( $_POST['length'] );

					$limit  = ' LIMIT ' . $start . ', ' . $length;
				}

				// Total query.
				$rows_query = WLSM_M_Staff_General::fetch_students_query_count( $school_id, $session_id, $filter );

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
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->student_name ) ),
							esc_html( WLSM_M_Staff_Class::get_admission_no_text( $row->admission_number ) ),
							esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->email ) ),
							esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
							esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ),
							esc_html( WLSM_M_Staff_Class::get_roll_no_text( $row->roll_number ) ),
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->father_name ) ),
							esc_html( WLSM_M_Staff_Class::get_phone_text( $row->father_phone ) ),
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->login_email ) ),
							esc_html( WLSM_M_Staff_Class::get_name_text( $row->username ) ),
							esc_html( WLSM_Config::get_date_text( $row->admission_date ) ),
							esc_html( $row->enrollment_number ),
							esc_html( WLSM_M_Staff_Class::get_status_text( $row->is_active ) ),
							'<a class="text-primary wlsm-view-session-records" data-nonce="' . esc_attr( wp_create_nonce( 'view-session-records-' . $row->ID ) ) . '" data-student="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Session Records', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><span class="dashicons dashicons-search"></span></a>',
							'<a class="text-success wlsm-print-id-card" data-nonce="' . esc_attr( wp_create_nonce( 'print-id-card-' . $row->ID ) ) . '" data-id-card="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Print ID Card', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><i class="fas fa-print"></i></a>',
							'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
							<a class="text-danger wlsm-delete-student" data-nonce="' . esc_attr( wp_create_nonce( 'delete-student-' . $row->ID ) ) . '" data-student="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' .
								sprintf(
									/* translators: %s: session label */
									esc_attr__( 'This will delete student record for the session %s.', 'school-management-system' ),
									esc_html( WLSM_M_Session::get_label_text( $session_label ) )
								) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
						);
					}
				}

				$output = array(
					'draw'            => intval( $_POST['draw'] ),
					'recordsTotal'    => $total_rows_count,
					'recordsFiltered' => $filter_rows_count,
					'data'            => $data,
					'export'          => array(
						'nonce'  => wp_create_nonce( 'export-staff-students-table' ),
						'action' => 'wlsm-export-staff-students-table'
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

	public static function delete_student() {
		$current_user = WLSM_M_Role::can( 'manage_students' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-student-' . $student_id ], 'delete-student-' . $student_id ) ) {
				die();
			}

			// Checks if student exists.
			$student = WLSM_M_Staff_General::get_student( $school_id, $session_id, $student_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_STUDENT_RECORDS, array( 'ID' => $student_id ) );
			$message = esc_html__( 'Student record deleted successfully.', 'school-management-system' );

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

	public static function view_session_records() {
		$current_user = WLSM_M_Role::can( 'manage_students' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$session_label = $current_user['session']['label'];

		try {
			ob_start();
			global $wpdb;

			$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'view-session-records-' . $student_id ], 'view-session-records-' . $student_id ) ) {
				die();
			}

			// Checks if student exists.
			$student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
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

		$student_old_records = array();
		$student_new_records = array();

		$student_current_session_record = array(
			array(
				'is_current'        => true,
				'session_label'     => $session_label,
				'enrollment_number' => $student->enrollment_number,
				'student_name'      => WLSM_M_Staff_Class::get_name_text( $student->student_name ),
				'class_label'       => WLSM_M_Class::get_label_text( $student->class_label ),
				'section_label'     => WLSM_M_Staff_Class::get_section_label_text( $student->section_label ),
				'roll_number'       => WLSM_M_Staff_Class::get_roll_no_text( $student->roll_number ),
			),
		);

		$to_student_record   = $student->ID;
		$from_student_record = $student->ID;

		while ( $to_student_record = self::student_old_record_exists( $to_student_record ) ) {
			$student = WLSM_M_Staff_General::get_student_record( $school_id, $to_student_record );
			if ( $student ) {
				$student_old_record_data = array(
					'is_current'        => false,
					'session_label'     => $student->session_label,
					'enrollment_number' => $student->enrollment_number,
					'student_name'      => WLSM_M_Staff_Class::get_name_text( $student->student_name ),
					'class_label'       => WLSM_M_Class::get_label_text( $student->class_label ),
					'section_label'     => WLSM_M_Staff_Class::get_section_label_text( $student->section_label ),
					'roll_number'       => WLSM_M_Staff_Class::get_roll_no_text( $student->roll_number ),
				);
				array_push( $student_old_records, $student_old_record_data );
			}
		}

		while ( $from_student_record = self::student_new_record_exists( $from_student_record ) ) {
			$student = WLSM_M_Staff_General::get_student_record( $school_id, $from_student_record );
			if ( $student ) {
				$student_new_record_data = array(
					'is_current'        => false,
					'session_label'     => $student->session_label,
					'enrollment_number' => $student->enrollment_number,
					'student_name'      => WLSM_M_Staff_Class::get_name_text( $student->student_name ),
					'class_label'       => WLSM_M_Class::get_label_text( $student->class_label ),
					'section_label'     => WLSM_M_Staff_Class::get_section_label_text( $student->section_label ),
					'roll_number'       => WLSM_M_Staff_Class::get_roll_no_text( $student->roll_number ),
				);
				array_push( $student_new_records, $student_new_record_data );
			}
		}

		$student_records = array_merge( array_reverse( $student_old_records ), $student_current_session_record, $student_new_records );

		ob_start();
		?>
		<div class="wlsm">
			<?php
			foreach ( $student_records as $student_record ) {
			?>
			<ul class="border-bottom">
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Session', 'school-management-system' ); ?></span>:
					<span <?php if ( $student_record['is_current'] ) { echo esc_attr('class="wlsm-font-bold text-primary"'); } ?>>
						<?php echo esc_html( $student_record['session_label'] ); ?>
					</span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></span>:
					<span><?php echo esc_html( $student_record['enrollment_number'] ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></span>:
					<span><?php echo esc_html( $student_record['student_name'] ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management-system' ); ?></span>:
					<span><?php echo esc_html( $student_record['class_label'] ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?></span>:
					<span><?php echo esc_html( $student_record['section_label'] ); ?></span>
				</li>
				<li>
					<span class="wlsm-font-bold"><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></span>:
					<span><?php echo esc_html( $student_record['roll_number'] ); ?></span>
				</li>
			</ul>
			<?php
			}
			?>
		</div>

		<?php
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function print_id_card() {
		$current_user = WLSM_M_Role::can( 'manage_students' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'print-id-card-' . $student_id ], 'print-id-card-' . $student_id ) ) {
				die();
			}

			// Checks if student exists.
			$student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found.', 'school-management-system' ) );
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
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/id_card.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function print_id_cards() {
		$current_user = WLSM_M_Role::can( 'manage_students' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$session_label = $current_user['session']['label'];

		if ( ! wp_verify_nonce( $_POST['print-id-cards'], 'print-id-cards' ) ) {
			die();
		}

		$class_id    = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
		$section_id  = isset( $_POST['section_id'] ) ? absint( $_POST['section_id'] ) : 0;
		$only_active = isset( $_POST['only_active'] ) ? (bool) ( $_POST['only_active'] ) : 1;

		try {
			ob_start();
			global $wpdb;

			// Start validation.
			$errors = array();

			if ( empty( $class_id ) ) {
				$errors['class_id'] = esc_html__( 'Please select a class.', 'school-management-system' );
			} else {
				// Checks if class exists in the school.
				$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
				} else {
					$class_school_id = $class_school->ID;

					if ( $section_id ) {
						$section = WLSM_M_Staff_Class::fetch_section( $school_id, $section_id, $class_school_id );
						if ( ! $section ) {
							$errors['section_id'] = esc_html__( 'Section not found.', 'school-management-system' );
						} else {
							$section_label = WLSM_M_Staff_Class::get_section_label_text( $section->label );
						}
					} else {
						$section_label = esc_html__( 'All', 'school-management-system' );
					}

					$class       = WLSM_M_Class::fetch_class( $class_id );
					$class_label = WLSM_M_Class::get_label_text( $class->label );
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
			$filter = array(
				'class_id'   => $class_id,
				'section_id' => $section_id,
				'search_by'  => 'search_by_class'
			);

			$query = WLSM_M_Staff_General::fetch_students_query( $school_id, $session_id, $filter );

			// Grouping.
			$group_by = ' ' . WLSM_M_Staff_General::fetch_students_query_group_by();

			$query .= $group_by;
			$query .= ' ORDER BY sr.roll_number ASC, sr.ID ASC';

			$students = $wpdb->get_results( $query );

			$students = array_filter( $students, function( $student ) use ( $only_active ) {
				if ( $only_active && ! $student->is_active ) {
					return false;
				}
				return true;
			} );

			ob_start();
			require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/id_cards.php';
			$html = ob_get_clean();

			$json = json_encode( array(
				'message_title' => esc_html__( 'Print ID Cards', 'school-management-system' ),
			) );

			wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
		}

		wp_send_json_error( $errors );
	}

	public static function student_old_record_exists( $to_student_record ) {
		global $wpdb;
		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT pm.from_student_record FROM ' . WLSM_PROMOTIONS . ' as pm WHERE pm.to_student_record = %d', $to_student_record ) );
		if ( $student ) {
			return $student->from_student_record;
		}
		return false;
	}

	public static function student_new_record_exists( $from_student_record ) {
		global $wpdb;
		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT pm.to_student_record FROM ' . WLSM_PROMOTIONS . ' as pm WHERE pm.from_student_record = %d', $from_student_record ) );
		if ( $student ) {
			return $student->to_student_record;
		}
		return false;
	}

	public static function fetch_employees() {
		$current_user = WLSM_M_Role::can( 'manage_employees' );
		self::fetch_staff_records( $current_user, WLSM_M_Role::get_employee_key() );
	}

	public static function fetch_staff_records( $current_user, $role ) {
		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		if ( WLSM_M_Role::get_admin_key() === $role ) {
			$page_url = WLSM_M_Staff_General::get_admins_page_url();
		} else {
			$page_url = WLSM_M_Staff_General::get_employees_page_url();
		}

		$query = WLSM_M_Staff_General::fetch_staff_query( $school_id, $role );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_General::fetch_staff_query_group_by();

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
				'(a.email LIKE "%' . $search_value . '%") OR ' .
				'(a.salary LIKE "%' . $search_value . '%") OR ' .
				'(a.designation LIKE "%' . $search_value . '%") OR ' .
				'(r.name LIKE "%' . $search_value . '%") OR ' .
				'(u.user_email LIKE "%' . $search_value . '%") OR ' .
				'(u.user_login LIKE "%' . $search_value . '%")';

				$search_value_lowercase = strtolower( $search_value );
				if ( preg_match( '/^inac(|t|ti|tiv|tive)$/', $search_value_lowercase ) ) {
					$is_active = 0;
				} elseif ( preg_match( '/^acti(|v|ve)$/', $search_value_lowercase ) ) {
					$is_active = 1;
				}
				if ( isset( $is_active ) ) {
					$condition .= ' OR (a.is_active = ' . $is_active . ')';
				}

				$joining_date = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

				if ( $joining_date ) {
					$format_joining_date = 'Y-m-d';
				} else {
					if ( 'd-m-Y' === WLSM_Config::date_format() ) {
						if ( ! $joining_date ) {
							$joining_date        = DateTime::createFromFormat( 'm-Y', $search_value );
							$format_joining_date = 'Y-m';
						}
					} elseif ( 'd/m/Y' === WLSM_Config::date_format() ) {
						if ( ! $joining_date ) {
							$joining_date        = DateTime::createFromFormat( 'm/Y', $search_value );
							$format_joining_date = 'Y-m';
						}
					} elseif ( 'Y-m-d' === WLSM_Config::date_format() ) {
						if ( ! $joining_date ) {
							$joining_date        = DateTime::createFromFormat( 'Y-m', $search_value );
							$format_joining_date = 'Y-m';
						}
					} elseif ( 'Y/m/d' === WLSM_Config::date_format() ) {
						if ( ! $joining_date ) {
							$joining_date        = DateTime::createFromFormat( 'Y/m', $search_value );
							$format_joining_date = 'Y-m';
						}
					}

					if ( ! $joining_date ) {
						$joining_date        = DateTime::createFromFormat( 'Y', $search_value );
						$format_joining_date = 'Y';
					}
				}

				if ( $joining_date && isset( $format_joining_date ) ) {
					$joining_date = $joining_date->format( $format_joining_date );
					$joining_date = ' OR (a.joining_date LIKE "%' . $joining_date . '%")';

					$condition .= $joining_date;
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'a.name', 'a.phone', 'a.email', 'a.salary', 'a.designation', 'r.name', 'u.user_email', 'u.user_login', 'a.joining_date', 'a.is_active' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY a.joining_date DESC, a.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_General::fetch_staff_query_count( $school_id, $role );

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
				if ( $row->assigned_by_manager ) {
					$delete_staff = '';
				} else {
					/* translators: %s: staff role */
					$delete_staff = '<a class="text-danger wlsm-delete-staff" data-nonce="' . esc_attr( wp_create_nonce( 'delete-staff-' . $row->ID ) ) . '" data-staff="' . esc_attr( $row->ID ) . '" href="#" data-role="' . esc_attr( $role ) . '" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . sprintf( esc_attr__( 'This will delete the %s.', 'school-management-system' ), strtolower( WLSM_M_Role::get_role_text( $role ) ) ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>';
				}

				if ( WLSM_M_Role::get_admin_key() === $role ) {
					$role_name = WLSM_M_Role::get_admin_text();
				} else {
					$role_name = $row->role_name;
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ),
					esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->email ) ),
					esc_html( WLSM_Config::get_money_text( $row->salary ) ),
					esc_html( WLSM_M_Staff_Class::get_designation_text( $row->designation ) ),
					esc_html( $role_name ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->login_email ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->username ) ),
					esc_html( WLSM_Config::get_date_text( $row->joining_date ) ),
					esc_html( WLSM_M_Staff_Class::get_status_text( $row->is_active ) ),
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;' . $delete_staff
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

	public static function save_employee() {
		$current_user = WLSM_M_Role::can( 'manage_employees' );
		self::save_staff( $current_user, WLSM_M_Role::get_employee_key() );
	}

	public static function save_staff( $current_user, $role ) {
		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		if ( WLSM_M_Role::get_admin_key() === $role ) {
			$page_url = WLSM_M_Staff_General::get_admins_page_url();
		} else {
			$page_url = WLSM_M_Staff_General::get_employees_page_url();
		}

		try {
			ob_start();
			global $wpdb;

			$admin_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

			if ( $admin_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-' . $role . '-' . $admin_id ], 'edit-' . $role . '-' . $admin_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-' . $role . ''], 'add-' . $role . '' ) ) {
					die();
				}
			}

			$user_id = NULL;

			$assigned_by_manager = 0;

			// Checks if staff exists.
			if ( $admin_id ) {
				$admin = WLSM_M_Staff_General::get_staff( $school_id, $role, $admin_id );

				if ( ! $admin ) {
					/* translators: %s: staff role */
					throw new Exception( sprintf( esc_html__( '%s not found.', 'school-management-system' ), WLSM_M_Role::get_role_text( $role ) ) );
				}

				$assigned_by_manager = $admin->assigned_by_manager;

				$user_id  = $admin->user_id;
				$staff_id = $admin->staff_id;
			}

			// Personal Detail.
			$name          = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$gender        = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
			$dob           = isset( $_POST['dob'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['dob'] ) ) : NULL;
			$address       = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
			$email         = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
			$phone         = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
			$salary        = isset( $_POST['salary'] ) ? WLSM_Config::sanitize_money( $_POST['salary'] ) : 0;

			// Joining Detail.
			$joining_date = isset( $_POST['joining_date'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['joining_date'] ) ) : NULL;
			$staff_role   = isset( $_POST['role'] ) ? absint( $_POST['role'] ) : 0;
			$designation  = isset( $_POST['designation'] ) ? sanitize_text_field( $_POST['designation'] ) : '';

			// Permissions
			$permissions = ( isset( $_POST['permission'] ) && is_array( $_POST['permission'] ) ) ? $_POST['permission'] : array();

			// Login Detail.
			$new_or_existing   = isset( $_POST['staff_new_or_existing'] ) ? sanitize_text_field( $_POST['staff_new_or_existing'] ) : '';
			$existing_username = isset( $_POST['existing_username'] ) ? sanitize_text_field( $_POST['existing_username'] ) : '';
			$new_login_email   = isset( $_POST['new_login_email'] ) ? sanitize_text_field( $_POST['new_login_email'] ) : '';
			$new_password      = isset( $_POST['new_password'] ) ? sanitize_text_field( $_POST['new_password'] ) : '';
			$username          = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
			$login_email       = isset( $_POST['login_email'] ) ? sanitize_text_field( $_POST['login_email'] ) : '';
			$password          = isset( $_POST['password'] ) ? $_POST['password'] : '';

			// Status.
			$is_active = isset( $_POST['is_active'] ) ? (bool) $_POST['is_active'] : 1;

			// Start validation.
			$errors = array();

			// Personal Detail.
			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please specify name.', 'school-management-system' );
			}
			if ( strlen( $name ) > 60 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}
			if ( ! empty( $phone ) && strlen( $phone ) > 40 ) {
				$errors['phone'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}
			if ( ! empty( $email ) ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				} elseif ( strlen( $email ) > 60 ) {
					$errors['email'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
				}
			}
			if ( ! empty( $designation ) && strlen( $designation ) > 80 ) {
				$errors['designation'] = esc_html__( 'Maximum length cannot exceed 80 characters.', 'school-management-system' );
			}
			if ( ! in_array( $gender, array_keys( WLSM_Helper::gender_list() ) ) ) {
				$gender = NULL;
			}
			if ( ! empty( $dob ) ) {
				$dob = $dob->format( 'Y-m-d' );
			} else {
				$dob = NULL;
			}

			// Joining Detail.
			if ( ! empty( $joining_date ) ) {
				$joining_date = $joining_date->format( 'Y-m-d' );
			} else {
				$joining_date = NULL;
			}

			if ( WLSM_M_Role::get_admin_key() === $role ) {
				$staff_role = NULL;
			}

			// Permissions.
			if ( empty( $staff_role ) ) {
				$staff_role = NULL;
			} else {
				$staff_role_exists = WLSM_M_Staff_General::fetch_role( $school_id, $staff_role );
				if ( ! $staff_role_exists ) {
					$errors['role'] = esc_html__( 'Please select valid staff role.', 'school-management-system' );
				} else {
					$role_permissions = $staff_role_exists->permissions;
					if ( is_serialized( $role_permissions ) ) {
						$role_permissions = unserialize( $role_permissions );
						$permissions      = array_unique( array_merge( $role_permissions, $permissions ) );
					}
				}
			}

			$permissions = WLSM_M_Role::get_role_permissions( $role, $permissions );

			// Login Detail.
			if ( 'existing_user' === $new_or_existing ) {
				if ( ! $user_id ) {
					if ( empty( $existing_username ) ) {
						$errors['existing_username'] = esc_html__( 'Please provide existing username.', 'school-management-system' );
					}
				} else {
					if ( empty( $new_login_email ) ) {
						$errors['new_login_email'] = esc_html__( 'Please provide login email.', 'school-management-system' );
					}
				}
			} elseif ( 'new_user' === $new_or_existing ) {
				if ( empty( $username ) ) {
					$errors['username'] = esc_html__( 'Please provide username.', 'school-management-system' );
				}
				if ( empty( $login_email ) ) {
					$errors['login_email'] = esc_html__( 'Please provide login email.', 'school-management-system' );
				}
				if ( ! filter_var( $login_email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['login_email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				}
				if ( empty( $password ) ) {
					$errors['password'] = esc_html__( 'Please provide login password.', 'school-management-system' );
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

				if ( $admin_id ) {
					/* translators: %s: staff role */
					$message = sprintf( esc_html__( '%s updated successfully.', 'school-management-system' ), WLSM_M_Role::get_role_text( $role ) );
				} else {
					/* translators: %s: staff role */
					$message = sprintf( esc_html__( '%s added successfully.', 'school-management-system' ), WLSM_M_Role::get_role_text( $role ) );
				}

				// Staff user data.
				$update_staff_user_id = NULL;
				if ( 'existing_user' === $new_or_existing ) {
					if ( ! $user_id ) {
						if ( ! $assigned_by_manager ) {
							// Existing user.
							$user = get_user_by( 'login', $existing_username );
							if ( ! $user ) {
								throw new Exception( esc_html__( 'Username does not exist.', 'school-management-system' ) );
							}

							$user_id = $user->ID;

							if ( user_can( $user_id, WLSM_ADMIN_CAPABILITY ) ) {
								throw new Exception( esc_html__( 'User is a multi-school administrator.', 'school-management-system' ) );
							}

							// Check if user already has a staff record in the school.
							if ( $admin_id ) {
								$user_has_staff_record = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID FROM ' . WLSM_STAFF . ' as sf WHERE sf.user_id = %d AND sf.school_id = %d AND sf.ID != %d', $user_id, $school_id, $staff_id ) );
							} else {
								$user_has_staff_record = $wpdb->get_row( $wpdb->prepare( 'SELECT sf.ID FROM ' . WLSM_STAFF . ' as sf WHERE sf.user_id = %d AND sf.school_id = %d', $user_id, $school_id ) );
							}

							if ( $user_has_staff_record ) {
								throw new Exception( esc_html__( 'The user already has a staff record.', 'school-management-system' ) );
							}
						}

					} else {
						$user_data = array(
							'ID'         => $user_id,
							'user_email' => $new_login_email,
						);

						if ( ! empty( $new_password ) ) {
							$user_data['user_pass'] = $new_password;
						}

						$user_id = wp_update_user( $user_data );
						if ( is_wp_error( $user_id ) ) {
							throw new Exception( $user_id->get_error_message() );
						}
					}

				} elseif ( 'new_user' === $new_or_existing ) {
					// New user.
					$user_data = array(
						'user_email' => $login_email,
						'user_login' => $username,
						'user_pass'  => $password,
					);

					$user_id = wp_insert_user( $user_data );
					if ( is_wp_error( $user_id ) ) {
						throw new Exception( $user_id->get_error_message() );
					}
				} else {
					if ( ! $assigned_by_manager ) {
						$user_id = NULL;
					}
				}

				$update_staff_user_id = $user_id;

				// Admin data.
				$admin_data = array(
					'name'         => $name,
					'gender'       => $gender,
					'dob'          => $dob,
					'phone'        => $phone,
					'email'        => $email,
					'address'      => $address,
					'salary'       => $salary,
					'designation'  => $designation,
					'joining_date' => $joining_date,
					'role_id'      => $staff_role,
					'is_active'    => $is_active,
				);

				$staff_data = array(
					'role'        => $role,
					'permissions' => serialize( $permissions ),
					'user_id'     => $user_id,
				);

				if ( $admin_id ) {
					// Update staff.
					$staff_data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_STAFF, $staff_data, array( 'ID' => $staff_id ) );
				} else {
					// Add staff.
					$staff_data['school_id'] = $school_id;
					$success = $wpdb->insert( WLSM_STAFF, $staff_data );
					$staff_id = $wpdb->insert_id;
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( $admin_id ) {
					// Update admin.
					$admin_data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_ADMINS, $admin_data, array( 'ID' => $admin_id, 'staff_id' => $staff_id ) );
				} else {
					// Add admin.
					$admin_data['staff_id'] = $staff_id;

					$success = $wpdb->insert( WLSM_ADMINS, $admin_data );
				}

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

				$reload = false;
				if ( $admin_id ) {
					$reload = true;
				}

				wp_send_json_success( array( 'message' => $message, 'reload' => $reload ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function delete_employee() {
		$current_user = WLSM_M_Role::can( 'manage_employees' );
		self::delete_staff( $current_user, WLSM_M_Role::get_employee_key() );
	}

	public static function delete_staff( $current_user, $role ) {
		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$admin_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-staff-' . $admin_id ], 'delete-staff-' . $admin_id ) ) {
				die();
			}

			// Checks if staff exists.
			$admin = WLSM_M_Staff_General::get_staff( $school_id, $role, $admin_id );

			if ( ! $admin ) {
				/* translators: %s: staff role */
				throw new Exception( sprintf( esc_html__( '%s not found.', 'school-management-system' ), WLSM_M_Role::get_role_text( $role ) ) );
			}

			if ( $admin->assigned_by_manager ) {
				die();
			}

			$staff_id = $admin->staff_id;

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
			/* translators: %s: staff role */
			$message = sprintf( esc_html__( '%s deleted successfully.', 'school-management-system' ), WLSM_M_Role::get_role_text( $role ) );

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

	public static function fetch_roles() {
		$current_user = WLSM_M_Role::can( 'manage_roles' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		global $wpdb;

		$page_url = WLSM_M_Staff_General::get_roles_page_url();

		$query = WLSM_M_Staff_General::fetch_role_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_General::fetch_role_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(r.name LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'r.name' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY r.name ASC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_General::fetch_role_query_count( $school_id );

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
					esc_html( $row->name ),
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
					<a class="text-danger wlsm-delete-role" data-nonce="' . esc_attr( wp_create_nonce( 'delete-role-' . $row->ID ) ) . '" data-role="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the role.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
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

	public static function save_role() {
		$current_user = WLSM_M_Role::can( 'manage_roles' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$role_id = isset( $_POST['role_id'] ) ? absint( $_POST['role_id'] ) : 0;

			if ( $role_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-role-' . $role_id ], 'edit-role-' . $role_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-role'], 'add-role' ) ) {
					die();
				}
			}

			// Checks if role exists.
			if ( $role_id ) {
				$role = WLSM_M_Staff_General::get_role( $school_id, $role_id );

				if ( ! $role ) {
					throw new Exception( esc_html__( 'Role not found.', 'school-management-system' ) );
				}
			}

			$name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$permissions = ( isset( $_POST['permission'] ) && is_array( $_POST['permission'] ) ) ? $_POST['permission'] : array();

			// Start validation.
			$errors = array();

			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please provide role name.', 'school-management-system' );
			}
			if ( strlen( $name ) > 60 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}

			if ( WLSM_M_Role::get_admin_key() === strtolower( trim( $name ) ) ) {
				$errors['name'] = esc_html__( 'This role is reserved for school administrators.', 'school-management-system' );
				wp_send_json_error( $errors );
			}

			// Checks if role already exists with this name.
			if ( $role_id ) {
				$role_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . WLSM_ROLES . ' as r WHERE r.name = %s AND r.school_id = %d AND r.ID != %d', $name, $school_id, $role_id ) );
			} else {
				$role_exist = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . WLSM_ROLES . ' as r WHERE r.name = %s AND r.school_id = %d', $name, $school_id ) );
			}

			if ( $role_exist ) {
				$errors['name'] = esc_html__( 'Role already exists with this name.', 'school-management-system' );
			}

			$permissions = array_intersect( $permissions, array_keys( WLSM_M_Role::get_permissions( array( 'manage_admins' ) ) ) );

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

				if ( $role_id ) {
					$message = esc_html__( 'Role updated successfully.', 'school-management-system' );
					$reset   = false;
				} else {
					$message = esc_html__( 'Role added successfully.', 'school-management-system' );
					$reset   = true;
				}

				$permissions = serialize( $permissions );

				// Role data.
				$data = array(
					'name'        => $name,
					'permissions' => $permissions,
				);

				if ( $role_id ) {
					$data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_ROLES, $data, array( 'ID' => $role_id, 'school_id' => $school_id ) );
				} else {
					$data['school_id'] = $school_id;

					$success = $wpdb->insert( WLSM_ROLES, $data );
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

	public static function delete_role() {
		$current_user = WLSM_M_Role::can( 'manage_roles' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$role_id = isset( $_POST['role_id'] ) ? absint( $_POST['role_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-role-' . $role_id ], 'delete-role-' . $role_id ) ) {
				die();
			}

			// Checks if role exists.
			$role = WLSM_M_Staff_General::get_role( $school_id, $role_id );

			if ( ! $role ) {
				throw new Exception( esc_html__( 'Role not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_ROLES, array( 'ID' => $role_id ) );
			$message = esc_html__( 'Role deleted successfully.', 'school-management-system' );

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

	public static function get_role_permissions() {
		$current_user = WLSM_M_Role::can( 'manage_employees' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['nonce'], 'get-role-permissions' ) ) {
				die();
			}

			$role_id = isset( $_POST['role_id'] ) ? absint( $_POST['role_id'] ) : 0;

			// Checks if role exists in the school.
			$role = WLSM_M_Staff_General::fetch_role( $school_id, $role_id );

			if ( ! $role ) {
				throw new Exception( esc_html__( 'Role not found.', 'school-management-system' ) );
			}

			$permissions = array();

			if ( $role->permissions ) {
				$permissions = $role->permissions;
				if ( is_serialized( $permissions ) ) {
					$permissions = unserialize( $permissions );
				}
			}

			wp_send_json( $permissions );
		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = $buffer;
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json( array() );
		}
	}

	public static function fetch_inquiries() {
		$current_user = WLSM_M_Role::can( 'manage_inquiries' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		global $wpdb;

		$page_url = WLSM_M_Staff_General::get_inquiries_page_url();

		$query = WLSM_M_Staff_General::fetch_inquiry_query( $school_id );

		$query_filter = $query;

		// Grouping.
		$group_by = ' ' . WLSM_M_Staff_General::fetch_inquiry_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition .= '' .
				'(iq.name LIKE "%' . $search_value . '%") OR ' .
				'(iq.phone LIKE "%' . $search_value . '%") OR ' .
				'(iq.email LIKE "%' . $search_value . '%") OR ' .
				'(iq.message LIKE "%' . $search_value . '%") OR ' .
				'(iq.note LIKE "%' . $search_value . '%") OR ' .
				'(c.label LIKE "%' . $search_value . '%")';

				$search_value_lowercase = strtolower( $search_value );
				if ( preg_match( '/^inac(|t|ti|tiv|tive)$/', $search_value_lowercase ) ) {
					$is_active = 0;
				} elseif ( preg_match( '/^acti(|v|ve)$/', $search_value_lowercase ) ) {
					$is_active = 1;
				}
				if ( isset( $is_active ) ) {
					$condition .= ' OR (iq.is_active = ' . $is_active . ')';
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
					} elseif ( 'd/m/Y' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'm/Y', $search_value );
							$format_created_at = 'Y-m';
						}
					} elseif ( 'Y-m-d' === WLSM_Config::date_format() ) {
						if ( ! $created_at ) {
							$created_at        = DateTime::createFromFormat( 'Y-m', $search_value );
							$format_created_at = 'Y-m';
						}
					} elseif ( 'Y/m/d' === WLSM_Config::date_format() ) {
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
					$created_at = ' OR (iq.created_at LIKE "%' . $created_at . '%")';

					$condition .= $created_at;
				}

				$next_follow_up = DateTime::createFromFormat( WLSM_Config::date_format(), $search_value );

				if ( $next_follow_up ) {
					$format_next_follow_up = 'Y-m-d';
				} else {
					if ( 'd-m-Y' === WLSM_Config::date_format() ) {
						if ( ! $next_follow_up ) {
							$next_follow_up        = DateTime::createFromFormat( 'm-Y', $search_value );
							$format_next_follow_up = 'Y-m';
						}
					} elseif ( 'd/m/Y' === WLSM_Config::date_format() ) {
						if ( ! $next_follow_up ) {
							$next_follow_up        = DateTime::createFromFormat( 'm/Y', $search_value );
							$format_next_follow_up = 'Y-m';
						}
					} elseif ( 'Y-m-d' === WLSM_Config::date_format() ) {
						if ( ! $next_follow_up ) {
							$next_follow_up        = DateTime::createFromFormat( 'Y-m', $search_value );
							$format_next_follow_up = 'Y-m';
						}
					} elseif ( 'Y/m/d' === WLSM_Config::date_format() ) {
						if ( ! $next_follow_up ) {
							$next_follow_up        = DateTime::createFromFormat( 'Y/m', $search_value );
							$format_next_follow_up = 'Y-m';
						}
					}

					if ( ! $next_follow_up ) {
						$next_follow_up        = DateTime::createFromFormat( 'Y', $search_value );
						$format_next_follow_up = 'Y';
					}
				}

				if ( $next_follow_up && isset( $format_next_follow_up ) ) {
					$next_follow_up = $next_follow_up->format( $format_next_follow_up );
					$next_follow_up = ' OR (iq.next_follow_up LIKE "%' . $next_follow_up . '%")';

					$condition .= $next_follow_up;
				}

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'c.label', 'iq.name', 'iq.phone', 'iq.email', 'iq.message', 'iq.created_at', 'iq.next_follow_up', 'iq.is_active' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );

			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY iq.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );

			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_General::fetch_inquiry_query_count( $school_id );

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
			$students_page_url = WLSM_M_Staff_General::get_students_page_url();
			foreach ( $filter_rows_limit as $row ) {
				if ( $row->message ) {
					$view_message = '<a class="text-primary wlsm-view-inquiry-message" data-nonce="' . esc_attr( wp_create_nonce( 'view-inquiry-message-' . $row->ID ) ) . '" data-inquiry="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Inquiry Message', 'school-management-system' ) . '" data-close="' . esc_attr__( 'Close', 'school-management-system' ) . '"><span class="dashicons dashicons-search"></span></a>';
				} else {
					$view_message = '-';
				}

				if ( $row->is_active ) {
					$add_admission = '<br><small class="wlsm-font-bold"><a href="' . esc_url( $students_page_url . '&action=save&inquiry_id=' . $row->ID ) . '">' . esc_html__( 'Add Admission', 'school-management-system' ) . '</small></a>';
				} else {
					$add_admission = '';
				}

				// Table columns.
				$data[] = array(
					esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ),
					esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
					esc_html( WLSM_M_Staff_Class::get_name_text( $row->email ) ),
					$view_message,
					esc_html( WLSM_Config::get_date_text( $row->created_at ) ),
					esc_html( $row->next_follow_up ? WLSM_Config::get_date_text( $row->next_follow_up ) : '-' ),
					esc_html( WLSM_M_Staff_General::get_inquiry_status_text( $row->is_active ) ) . $add_admission,
					'<a class="text-primary" href="' . esc_url( $page_url . "&action=save&id=" . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;
					<a class="text-danger wlsm-delete-inquiry" data-nonce="' . esc_attr( wp_create_nonce( 'delete-inquiry-' . $row->ID ) ) . '" data-inquiry="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management-system' ) . '" data-message-content="' . esc_attr__( 'This will delete the inquiry.', 'school-management-system' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management-system' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management-system' ) . '"><span class="dashicons dashicons-trash"></span></a>'
				);
			}
		}

		$output = array(
			'draw'            => intval( $_POST['draw'] ),
			'recordsTotal'    => $total_rows_count,
			'recordsFiltered' => $filter_rows_count,
			'data'            => $data,
			'export'          => array(
				'nonce'  => wp_create_nonce( 'export-staff-inquiries-table' ),
				'action' => 'wlsm-export-staff-inquiries-table'
			)
		);

		echo json_encode( $output );
		die();
	}

	public static function save_inquiry() {
		$current_user = WLSM_M_Role::can( 'manage_inquiries' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$inquiry_id = isset( $_POST['inquiry_id'] ) ? absint( $_POST['inquiry_id'] ) : 0;

			if ( $inquiry_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-inquiry-' . $inquiry_id ], 'edit-inquiry-' . $inquiry_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-inquiry'], 'add-inquiry' ) ) {
					die();
				}
			}

			// Checks if inquiry exists.
			if ( $inquiry_id ) {
				$inquiry = WLSM_M_Staff_General::get_inquiry( $school_id, $inquiry_id );

				if ( ! $inquiry ) {
					throw new Exception( esc_html__( 'Inquiry not found.', 'school-management-system' ) );
				}
			}

			// Inquiry.
			$name     = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$phone    = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
			$email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			$message  = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';
			$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			// Status.
			$is_active      = isset( $_POST['is_active'] ) ? (bool) $_POST['is_active'] : 1;
			$next_follow_up = isset( $_POST['next_follow_up'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['next_follow_up'] ) ) : NULL;
			$note           = isset( $_POST['note'] ) ? sanitize_text_field( $_POST['note'] ) : '';

			// Start validation.
			$errors = array();

			if ( empty( $name ) ) {
				$errors['name'] = esc_html__( 'Please specify name.', 'school-management-system' );
			}
			if ( strlen( $name ) > 60 ) {
				$errors['name'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
			}

			if ( ! empty( $phone ) && strlen( $phone ) > 40 ) {
				$errors['phone'] = esc_html__( 'Maximum length cannot exceed 40 characters.', 'school-management-system' );
			}

			if ( ! empty( $email ) ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$errors['email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
				} elseif ( strlen( $email ) > 60 ) {
					$errors['email'] = esc_html__( 'Maximum length cannot exceed 60 characters.', 'school-management-system' );
				}
			}

			if ( ! empty( $next_follow_up ) ) {
				$next_follow_up = $next_follow_up->format( 'Y-m-d' );
			} else {
				$next_follow_up = NULL;
			}

			if ( ! empty( $class_id ) ) {
				$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );
				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
				} else {
					$class_school_id = $class_school->ID;
				}
			} else {
				$class_school_id = NULL;
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

				// Inquiry data.
				$data = array(
					'name'            => $name,
					'phone'           => $phone,
					'email'           => $email,
					'message'         => $message,
					'note'            => $note,
					'is_active'       => $is_active,
					'next_follow_up'  => $next_follow_up,
					'class_school_id' => $class_school_id,
				);

				if ( $inquiry_id ) {
					$data['updated_at'] = date( 'Y-m-d H:i:s' );

					$success = $wpdb->update( WLSM_INQUIRIES, $data, array( 'ID' => $inquiry_id, 'school_id' => $school_id ) );
				} else {
					$data['school_id'] = $school_id;

					$success = $wpdb->insert( WLSM_INQUIRIES, $data );
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( $inquiry_id ) {
					$message = esc_html__( 'Inquiry updated successfully.', 'school-management-system' );
					$reset   = false;
				} else {
					$message = esc_html__( 'Inquiry added successfully.', 'school-management-system' );
					$reset   = true;
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

	public static function delete_inquiry() {
		$current_user = WLSM_M_Role::can( 'manage_inquiries' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			$inquiry_id = isset( $_POST['inquiry_id'] ) ? absint( $_POST['inquiry_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-inquiry-' . $inquiry_id ], 'delete-inquiry-' . $inquiry_id ) ) {
				die();
			}

			// Checks if inquiry exists.
			$inquiry = WLSM_M_Staff_General::get_inquiry( $school_id, $inquiry_id );

			if ( ! $inquiry ) {
				throw new Exception( esc_html__( 'Inquiry not found.', 'school-management-system' ) );
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

			$success = $wpdb->delete( WLSM_INQUIRIES, array( 'ID' => $inquiry_id ) );
			$message = esc_html__( 'Inquiry deleted successfully.', 'school-management-system' );

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

	public static function view_inquiry_message() {
		$current_user = WLSM_M_Role::can( 'manage_inquiries' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$inquiry_id = isset( $_POST['inquiry_id'] ) ? absint( $_POST['inquiry_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'view-inquiry-message-' . $inquiry_id ], 'view-inquiry-message-' . $inquiry_id ) ) {
				die();
			}

			// Checks if inquiry exists.
			$inquiry = WLSM_M_Staff_General::get_inquiry_message( $school_id, $inquiry_id );

			if ( ! $inquiry ) {
				throw new Exception( esc_html__( 'Inquiry not found.', 'school-management-system' ) );
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

		wp_send_json_success( esc_html( WLSM_Config::get_note_text( $inquiry->message ) ) );
	}

	public static function manage_promotion() {
		$current_user = WLSM_M_Role::can( 'manage_promote' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$session_label = $current_user['session']['label'];

		if ( ! wp_verify_nonce( $_POST[ 'nonce' ], 'manage-promotion' ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$new_session_id = isset( $_POST['promote_to_session'] ) ? absint( $_POST['promote_to_session'] ) : 0;
			$from_class_id  = isset( $_POST['from_class'] ) ? absint( $_POST['from_class'] ) : 0;
			$to_class_id    = isset( $_POST['to_class'] ) ? absint( $_POST['to_class'] ) : 0;

			// Start validation.
			$errors = array();

			if ( empty( $new_session_id ) ) {
				$errors['promote_to_session'] = esc_html__( 'Please select new session.', 'school-management-system' );
			} elseif ( $session_id == $new_session_id ) {
				$errors['promote_to_session'] = esc_html__( 'New session must be different from current session.', 'school-management-system' );
			} elseif ( ! WLSM_M_Staff_General::is_next_session( $session_id, $new_session_id ) ) {
				$errors['promote_to_session'] = esc_html__( 'Start date of new session must be after end date of current session.', 'school-management-system' );
			} else {
				// Check if new session exists.
				$new_session = WLSM_M_Session::fetch_session( $new_session_id );
				if ( ! $new_session ) {
					$errors['promote_to_session'] = esc_html__( 'Session not found.', 'school-management-system' );
				}
			}

			if ( empty( $from_class_id ) ) {
				$errors['from_class'] = esc_html__( 'Please select promotion from class.', 'school-management-system' );
			} else {
				// Check if old class exists in the school.
				$from_class = WLSM_M_Staff_General::get_class_school( $school_id, $from_class_id );

				if ( ! $from_class ) {
					$errors['from_class'] = esc_html__( 'Class not found.', 'school-management-system' );
				} else {
					// Get sections of old class.
					$from_sections = WLSM_M_Staff_General::fetch_class_sections( $from_class->ID );
				}
			}

			if ( empty( $to_class_id ) ) {
				$errors['to_class'] = esc_html__( 'Please select promotion to class.', 'school-management-system' );
			} else {
				if ( $from_class_id == $to_class_id ) {
					$errors['to_class'] = esc_html__( 'Promotion to class can\'t be the same.', 'school-management-system' );
				} else {
					// Check if new class exists in the school.
					$to_class = WLSM_M_Staff_General::get_class_school( $school_id, $to_class_id );

					if ( ! $to_class ) {
						$errors['to_class'] = esc_html__( 'Class not found.', 'school-management-system' );
					} else {
						// Get sections of new class.
						$to_sections = WLSM_M_Staff_General::fetch_class_sections( $to_class->ID );
					}
				}
			}

			// Get class students in current session.
			$students = WLSM_M_Staff_General::get_class_students( $school_id, $session_id, $from_class_id );

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
				ob_start();

				if ( count( $students ) ) {
				?>
				<input type="hidden" name="promote_to_session_final" value="<?php echo esc_attr( $new_session_id ); ?>">
				<input type="hidden" name="from_class_final" value="<?php echo esc_attr( $from_class_id ); ?>">
				<input type="hidden" name="to_class_final" value="<?php echo esc_attr( $to_class_id ); ?>">

				<!-- Map sections. -->
				<div class="wlsm-form-section">
					<div class="row">
						<div class="col-md-12">
							<div class="wlsm-form-sub-heading wlsm-font-bold">
								<?php esc_html_e( 'Map Class Sections', 'school-management-system' ); ?>
								<br>
								<small class="text-dark">
									<em><?php esc_html_e( 'Select sections mapping of old class to the new class.', 'school-management-system' ); ?></em>
								</small>
							</div>
						</div>
					</div>

					<?php if ( count( $from_sections ) ) { ?>
					<div class="row mt-2 mb-1">
						<div class="col-md-6">
							<div class="wlsm-font-bold h6">
								<?php
								/* translators: %s: class name */
								printf( esc_html__( 'From Class: %s', 'school-management-system' ), esc_html( WLSM_M_Class::get_label_text( $from_class->label ) ) );
								?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="wlsm-font-bold h6">
								<?php
									/* translators: %s: class name */
								printf( esc_html__( 'To Class: %s', 'school-management-system' ), esc_html( WLSM_M_Class::get_label_text( $to_class->label ) ) );
								?>
							</div>
						</div>
					</div>
					<?php } ?>

					<?php
					foreach ( $from_sections as $key => $from_section ) {
					?>
					<hr>
					<div class="form-row mt-1">
						<div class="form-group col-md-6">
							<label class="wlsm-font-bold" for="wlsm-from-section-<?php echo esc_attr( $key ); ?>">
								<span class="wlsm-important">*</span> <?php esc_html_e( 'Students From Section', 'school-management-system' ); ?>:
							</label>
							<input type="hidden" name="from_section[]" value="<?php echo esc_attr( $from_section->ID ); ?>">
							<div class="ml-2">
								<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $from_section->label ) ); ?>
							</div>
						</div>

						<div class="form-group col-md-6">
							<label class="wlsm-font-bold" for="wlsm-to-section-<?php echo esc_attr( $key ); ?>">
								<span class="wlsm-important">*</span> <?php esc_html_e( 'Assign to Section', 'school-management-system' ); ?>:
							</label>
							<select name="to_section[]" id="wlsm-to-section-<?php echo esc_attr( $key ); ?>" class="form-control">
								<?php
								foreach ( $to_sections as $key => $to_section ) {
								?>
								<option value="<?php echo esc_attr( $to_section->ID ); ?>">
									<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $to_section->label ) ); ?>
								</option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
					<?php
					}
					?>
				</div>

				<!-- Students to be promoted. -->
				<div class="wlsm-form-section">
					<div class="row">
						<div class="col-md-12">
							<div class="wlsm-form-sub-heading wlsm-font-bold">
								<?php
								printf(
									wp_kses(
										/* translators: %s: class name */
										__( 'Students of Class: <span class="text-secondary">%s</span>', 'school-management-system' ),
										array( 'span' => array( 'class' => array() ) )
									),
									esc_html( WLSM_M_Class::get_label_text( $from_class->label ) )
								);
								?>
								<br>
								<small class="text-dark">
									<em><?php esc_html_e( 'Select students to enroll in next session.', 'school-management-system' ); ?></em>
								</small>
							</div>
						</div>
					</div>
					<div class="table-responsive w-100">
						<table class="table table-bordered wlsm-students-to-promote-table">
							<thead>
								<tr class="bg-primary text-white">
									<th><input type="checkbox" name="select_all" id="wlsm-select-all" value="1"></th>
									<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Options', 'school-management-system' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $students as $row ) { ?>
								<tr>
									<td>
										<input type="checkbox" class="wlsm-select-single" name="student[<?php echo esc_attr( $row->ID ); ?>]" value="<?php echo esc_attr( $row->ID ); ?>">
									</td>
									<td>
										<?php echo esc_html( $row->enrollment_number ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $row->roll_number ) ); ?>
									</td>
									<td>
										<select name="new_session_class[<?php echo esc_attr( $row->ID ); ?>]">
											<option value="<?php echo esc_attr( $to_class_id ); ?>">
												<?php
												/* translators: %s: class name */
												printf( esc_html__( 'Enroll to Class - %s', 'school-management-system' ), esc_html( WLSM_M_Class::get_label_text( $to_class->label ) ) );
												?>
											</option>
											<option value="<?php echo esc_attr( $from_class_id ); ?>">
												<?php
												/* translators: %s: class name */
												printf( esc_html__( 'Enroll to Class - %s', 'school-management-system' ), esc_html( WLSM_M_Class::get_label_text( $from_class->label ) ) );
												?>
											</option>
										</select>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row mt-2 mb-2">
					<div class="col-md-12 text-center">
						<?php
						printf(
							wp_kses(
								/* translators: 1: current session, 2: new session */
								__( 'Session From <span class="wlsm-font-bold">%1$s</span> to <span class="wlsm-font-bold">%2$s</span>', 'school-management-system' ),
								array( 'span' => array( 'class' => array() ) )
							),
							esc_html( WLSM_M_Session::get_label_text( $session_label ) ),
							esc_html( WLSM_M_Session::get_label_text( $new_session->label ) )
						);
						?>
					</div>
				</div>

				<div class="row mt-2">
					<div class="col-md-12 text-center">
						<button type="submit" class="btn btn-sm btn-success" id="wlsm-promote-student-btn" data-message-title="<?php esc_attr_e( 'Confirm Promotion!', 'school-management-system' ); ?>" data-message-content="<?php esc_attr_e( 'Are you sure to enroll these selected students for the next session?', 'school-management-system' ); ?>" data-submit="<?php esc_attr_e( 'Promote', 'school-management-system' ); ?>" data-cancel="<?php esc_attr_e( 'Cancel', 'school-management-system' ); ?>">
							<?php
							echo esc_html( _n( 'Promote Student', 'Promote Students', $students, 'school-management-system' ) );
							?>
						</button>
					</div>
				</div>
				<?php
				} else {
				?>
				<div class="alert alert-warning wlsm-font-bold">
					<i class="fas fa-exclamation-triangle"></i>
					<?php esc_html_e( 'There is no student in this class or students were already promoted.', 'school-management-system' ); ?>
				</div>
				<?php
				}
				$html = ob_get_clean();

				wp_send_json_success( array( 'html' => $html ) );

			} catch ( Exception $exception ) {
				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					$response = $buffer;
				} else {
					$response = $exception->getMessage();
				}
				wp_send_json_error( $response );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function promote_student() {
		$current_user = WLSM_M_Role::can( 'manage_promote' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		if ( ! wp_verify_nonce( $_POST[ 'promote-student-' . $session_id ], 'promote-student-' . $session_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$new_session_id = isset( $_POST['promote_to_session_final'] ) ? absint( $_POST['promote_to_session_final'] ) : 0;
			$from_class_id  = isset( $_POST['from_class_final'] ) ? absint( $_POST['from_class_final'] ) : 0;
			$to_class_id    = isset( $_POST['to_class_final'] ) ? absint( $_POST['to_class_final'] ) : 0;

			// Start validation.
			if ( empty( $new_session_id ) ) {
				wp_send_json_error( esc_html__( 'Please select new session.', 'school-management-system' ) );
			} elseif ( $session_id == $new_session_id ) {
				wp_send_json_error( esc_html__( 'New session must be different from current session.', 'school-management-system' ) );
			} elseif ( ! WLSM_M_Staff_General::is_next_session( $session_id, $new_session_id ) ) {
				wp_send_json_error( esc_html__( 'Start date of new session must be after end date of current session.', 'school-management-system' ) );
			} else {
				// Check if new session exists.
				$new_session = WLSM_M_Session::fetch_session( $new_session_id );
				if ( ! $new_session ) {
					wp_send_json_error( esc_html__( 'Session not found.', 'school-management-system' ) );
				}
			}

			if ( empty( $from_class_id ) ) {
				wp_send_json_error( esc_html__( 'Please select promotion from class.', 'school-management-system' ) );
			} else {
				// Check if old class exists in the school.
				$from_class = WLSM_M_Staff_General::get_class_school( $school_id, $from_class_id );

				if ( ! $from_class ) {
					wp_send_json_error( esc_html__( 'Class not found.', 'school-management-system' ) );
				} else {
					// Get sections of old class.
					$from_sections = WLSM_M_Staff_General::fetch_class_sections( $from_class->ID );
				}
			}

			if ( empty( $to_class_id ) ) {
				wp_send_json_error( esc_html__( 'Please select promotion to class.', 'school-management-system' ) );
			} else {
				if ( $from_class_id == $to_class_id ) {
					wp_send_json_error( esc_html__( 'Promotion to class can\'t be the same.', 'school-management-system' ) );
				} else {
					// Check if new class exists in the school.
					$to_class = WLSM_M_Staff_General::get_class_school( $school_id, $to_class_id );

					if ( ! $to_class ) {
						wp_send_json_error( esc_html__( 'Class not found.', 'school-management-system' ) );
					} else {
						// Get sections of new class.
						$to_sections = WLSM_M_Staff_General::fetch_class_sections( $to_class->ID );
					}
				}
			}

			// Get class students in current session.
			$students = WLSM_M_Staff_General::get_class_students_data( $school_id, $session_id, $from_class_id );

			$all_student_ids = array_map( function( $student ) {
				return $student->ID;
			}, $students );

			$all_from_sections_ids = array_map( function( $section ) {
				return $section->ID;
			}, $from_sections );

			$all_to_sections_ids = array_map( function( $section ) {
				return $section->ID;
			}, $to_sections );

			$from_section_ids = ( isset( $_POST['from_section'] ) && is_array( $_POST['from_section'] ) ) ? $_POST['from_section'] : array();
			$to_section_ids   = ( isset( $_POST['to_section'] ) && is_array( $_POST['to_section'] ) ) ? $_POST['to_section'] : array();
			$student_ids      = ( isset( $_POST['student'] ) && is_array( $_POST['student'] ) ) ? $_POST['student'] : array();
			$new_class_ids    = ( isset( $_POST['new_session_class'] ) && is_array( $_POST['new_session_class'] ) ) ? $_POST['new_session_class'] : array();

			$student_ids_keys   = array_keys( $student_ids );
			$new_class_ids_keys = array_keys( $new_class_ids );

			if ( ! count( $student_ids ) ) {
				wp_send_json_error( esc_html__( 'Please select students.', 'school-management-system' ) );
			} elseif ( ( array_intersect( $student_ids, $all_student_ids ) != $student_ids ) || ( $student_ids_keys != array_values( $student_ids ) ) ) {
				wp_send_json_error( esc_html__( 'Please select valid students.', 'school-management-system' ) );
			} elseif ( array_intersect( $student_ids_keys, $new_class_ids_keys ) != $student_ids_keys ) {
				wp_send_json_error( esc_html__( 'Invalid selection of students or new classes.', 'school-management-system' ) );
			}

			if ( array_intersect( $new_class_ids, array( $from_class_id, $to_class_id ) ) != $new_class_ids ) {
				wp_send_json_error( esc_html__( 'Please select valid class for each student.', 'school-management-system' ) );
			}

			if ( count( $all_from_sections_ids ) != count ( $to_section_ids ) ) {
				wp_send_json_error( esc_html__( 'Please select corresponding new sections for mapping.', 'school-management-system' ) );
			} elseif ( array_intersect( $to_section_ids, $all_to_sections_ids ) != $to_section_ids ) {
				wp_send_json_error( esc_html__( 'Please select valid new sections for mapping.', 'school-management-system' ) );
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

			foreach ( $student_ids_keys as $student_id ) {
				if ( isset( $students[ $student_id ] ) ) {
					$student = $students[ $student_id ];

					// Update old record.
					$old_student_data = array(
						'user_id'    => NULL,
						'is_active'  => 0,
						'updated_at' => date( 'Y-m-d H:i:s' ),
					);

					$success = $wpdb->update( WLSM_STUDENT_RECORDS, $old_student_data, array( 'ID' => $student_id ) );

					$buffer = ob_get_clean();
					if ( ! empty( $buffer ) ) {
						throw new Exception( $buffer );
					}

					if ( false === $success ) {
						throw new Exception( $wpdb->last_error );
					}

					// Insert new record.
					$old_section_id = $student->section_id;
					$section_index  = array_search( $old_section_id, $from_section_ids );

					if ( $from_class_id == $new_class_ids[ $student_id ] ) {
						// Keep in the same class.
						$new_section_id = $old_section_id;

					} else {
						// Promote to a new class.
						$new_section_id = $to_section_ids[ $section_index ];
					}

					if ( ! $new_section_id ) {
						throw new Exception( esc_html__( 'Please select corresponding new sections for mapping.', 'school-management-system' ) );
					}

					// Student data.
					$student_data = array(
						'enrollment_number' => WLSM_M_Staff_General::get_enrollment_number( $school_id ),
						'admission_number'  => $student->admission_number,
						'name'              => $student->name,
						'gender'            => $student->gender,
						'dob'               => $student->dob,
						'phone'             => $student->phone,
						'email'             => $student->email,
						'address'           => $student->address,
						'religion'          => $student->religion,
						'caste'             => $student->caste,
						'blood_group'       => $student->blood_group,
						'father_name'       => $student->father_name,
						'father_phone'      => $student->father_phone,
						'father_occupation' => $student->father_occupation,
						'mother_name'       => $student->mother_name,
						'mother_phone'      => $student->mother_phone,
						'mother_occupation' => $student->mother_occupation,
						'admission_date'    => $student->admission_date,
						'roll_number'       => $student->roll_number,
						'photo_id'          => $student->photo_id,
						'section_id'        => $new_section_id,
						'session_id'        => $new_session_id,
						'user_id'           => $student->user_id,
						'is_active'         => 1,
					);

					$success = $wpdb->insert( WLSM_STUDENT_RECORDS, $student_data );

					$buffer = ob_get_clean();
					if ( ! empty( $buffer ) ) {
						throw new Exception( $buffer );
					}

					if ( false === $success ) {
						throw new Exception( $wpdb->last_error );
					}

					$old_student_id = $student_id;
					$new_student_id = $wpdb->insert_id;

					// Insert promotion record.
					$promotion_data = array(
						'from_student_record' => $old_student_id,
						'to_student_record'   => $new_student_id,
					);
					$success = $wpdb->insert( WLSM_PROMOTIONS, $promotion_data );

					$buffer = ob_get_clean();
					if ( ! empty( $buffer ) ) {
						throw new Exception( $buffer );
					}

					if ( false === $success ) {
						throw new Exception( $wpdb->last_error );
					}

				} else {
					throw new Exception( esc_html__( 'Please select valid students.', 'school-management-system' ) );
				}
			}

			$wpdb->query( 'COMMIT;' );

			$message = esc_html__( 'Students promoted successfully.', 'school-management-system' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function fetch_stats_payments() {
		$current_user = WLSM_M_Role::can( 'manage_invoices' );

		if ( ! $current_user ) {
			die();
		}

		if ( ! wp_verify_nonce( $_REQUEST[ 'security' ], 'wlsm-security' ) ) {
			die();
		}

		global $wpdb;

		$current_school = $current_user['school'];

		$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		$invoices_page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

		// Last 15 Payments
		$payments = $wpdb->get_results(
			$wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p
			JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
			WHERE p.school_id = %d AND ss.ID = %d GROUP BY p.ID ORDER BY p.ID DESC LIMIT 15', $school_id, $session_id )
		);

		$output['data'] = array();

		foreach ( $payments as $row ) {
			if ( $row->invoice_id ) {
				$invoice_title = '<a target="_blank" href="' . esc_url( $invoices_page_url . '&action=save&id=' . $row->invoice_id ) . '">' . esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $row->invoice_title ) ) . '</a>';
			} else {
				$invoice_title = '<span class="text-danger">' . esc_html__( 'Deleted', 'school-management-system' ) . '<br><span class="text-secondary">' . $row->invoice_label . '<br><small>' . esc_html( WLSM_Config::get_money_text( $row->invoice_payable ) )  . ' ' . esc_html__( 'Payable', 'school-management-system' ) . '</small></span></span>';
			}

			$data = array(
				esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ),
				esc_html( WLSM_Config::get_money_text( $row->amount ) ),
				esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ),
				esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ),
				esc_html( WLSM_Config::get_date_text( $row->created_at ) ),
				$invoice_title,
				esc_html( WLSM_M_Staff_Class::get_name_text( $row->student_name ) ),
				esc_html( WLSM_M_Staff_Class::get_admission_no_text( $row->admission_number ) ),
				esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ),
				esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ),
				esc_html( WLSM_M_Staff_Class::get_phone_text( $row->phone ) ),
				esc_html( WLSM_M_Staff_Class::get_name_text( $row->father_name ) ),
				esc_html( WLSM_M_Staff_Class::get_phone_text( $row->father_phone ) )
			);

			if ( $can_delete_payments ) {
				ob_start();
				?>
				<a class="text-danger wlsm-delete-payment" data-nonce="<?php echo esc_attr( wp_create_nonce( 'delete-payment-' . $row->ID ) ); ?>" data-payment="<?php echo esc_attr( $row->ID ); ?>" href="#" data-message-title="<?php esc_attr_e( 'Please Confirm!', 'school-management-system' ); ?>" data-message-content="<?php esc_attr_e( 'This will delete the payment.', 'school-management-system' ); ?>" data-cancel="<?php esc_attr_e( 'Cancel', 'school-management-system' ); ?>" data-submit="<?php esc_attr_e( 'Confirm', 'school-management-system' ); ?>"><span class="dashicons dashicons-trash"></span></a>
				<?php
				$delete_payment = ob_get_clean();
				array_push( $data, $delete_payment );
			}

			$output['data'][] = $data;
		}

		echo json_encode( $output );
		die();
	}

	public static function save_school_general_settings() {
		$current_user = WLSM_M_Role::can( 'manage_settings' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['save-school-general-settings'], 'save-school-general-settings' ) ) {
				die();
			}

			$school_logo        = ( isset( $_FILES['school_logo'] ) && is_array( $_FILES['school_logo'] ) ) ? $_FILES['school_logo'] : NULL;
			$remove_school_logo = isset( $_POST['remove_school_logo'] ) ? (bool) $_POST['remove_school_logo'] : 0;

			// Start validation.
			$errors = array();

			if ( ! $remove_school_logo && ( isset( $school_logo['tmp_name'] ) && ! empty( $school_logo['tmp_name'] ) ) ) {
				$finfo = finfo_open( FILEINFO_MIME_TYPE );
				$mime  = finfo_file( $finfo, $school_logo['tmp_name'] );
				finfo_close($finfo);

				if ( ! in_array( $mime, WLSM_Helper::get_image_mime() ) ) {
					$errors['school_logo'] = esc_html__( 'Please provide school logo in JPG, JPEG or PNG format.', 'school-management-system' );
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

				$general = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "general"', $school_id ) );

				if ( $remove_school_logo ) {
					$school_logo = NULL;
				} else {
					if ( ! empty( $school_logo ) ) {
						$school_logo = media_handle_upload( 'school_logo', 0 );
						if ( is_wp_error( $school_logo ) ) {
							throw new Exception( $school_logo->get_error_message() );
						}
					}
				}

				if ( ! $school_logo ) {
					$school_logo = NULL;
				}

				$general_data = array(
					'school_logo' => $school_logo,
				);

				if ( ! $general ) {
					$wpdb->insert(
						WLSM_SETTINGS,
						array(
							'setting_key'   => 'general',
							'setting_value' => serialize( $general_data ),
							'school_id'     => $school_id,
						)
					);
				} else {
					$general_saved_data = unserialize( $general->setting_value );

					if ( isset( $general_saved_data['school_logo'] ) && ! empty( $general_saved_data['school_logo'] ) ) {
						if ( $remove_school_logo ) {
							// If remove school logo is checked, delete saved logo.
							$school_logo_delete_id = $general_saved_data['school_logo'];
						} elseif ( ! $general_data['school_logo'] ) {
							// If no school logo is provided from input, use saved school logo.
							$general_data['school_logo'] = $general_saved_data['school_logo'];
						} else {
							// If school logo is provided from input, delete saved school logo.
							$school_logo_delete_id = $general_saved_data['school_logo'];
						}
					}
					$wpdb->update(
						WLSM_SETTINGS,
						array( 'setting_value' => serialize( $general_data ) ),
						array( 'ID'            => $general->ID )
					);
					if ( isset( $school_logo_delete_id ) ) {
						wp_delete_attachment( $school_logo_delete_id, true );
					}
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				$wpdb->query( 'COMMIT;' );

				$message = esc_html__( 'General settings saved.', 'school-management-system' );

				wp_send_json_success( array( 'message' => $message ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function save_school_email_carrier_settings() {
		$current_user = WLSM_M_Role::can( 'manage_settings' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['save-school-email-carrier-settings'], 'save-school-email-carrier-settings' ) ) {
				die();
			}

			$email_carrier = isset( $_POST['email_carrier'] ) ? sanitize_text_field( $_POST['email_carrier'] ) : 'wp_mail';

			$wp_mail_from_name  = isset( $_POST['wp_mail_from_name'] ) ? sanitize_text_field( $_POST['wp_mail_from_name'] ) : '';
			$wp_mail_from_email = isset( $_POST['wp_mail_from_email'] ) ? sanitize_text_field( $_POST['wp_mail_from_email'] ) : '';

			$smtp_from_name  = isset( $_POST['smtp_from_name'] ) ? sanitize_text_field( $_POST['smtp_from_name'] ) : '';
			$smtp_host       = isset( $_POST['smtp_host'] ) ? sanitize_text_field( $_POST['smtp_host'] ) : '';
			$smtp_username   = isset( $_POST['smtp_username'] ) ? sanitize_text_field( $_POST['smtp_username'] ) : '';
			$smtp_password   = isset( $_POST['smtp_password'] ) ? $_POST['smtp_password'] : '';
			$smtp_encryption = isset( $_POST['smtp_encryption'] ) ? sanitize_text_field( $_POST['smtp_encryption'] ) : '';
			$smtp_port       = isset( $_POST['smtp_port'] ) ? sanitize_text_field( $_POST['smtp_port'] ) : '';

			// Start validation.
			$errors = array();

			if ( ! in_array( $email_carrier, array_keys( WLSM_Email::email_carriers() ) ) ) {
				$errors['email_carrier'] = esc_html__( 'Please select a valid email carrier.', 'school-management-system' );
			}

			if ( ! empty( $wp_mail_from_email ) && ! filter_var( $wp_mail_from_email, FILTER_VALIDATE_EMAIL ) ) {
				$errors['wp_mail_from_email'] = esc_html__( 'Please provide a valid email.', 'school-management-system' );
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

				// Email Carrier.
				$email = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "email"', $school_id ) );

				$email_data = array(
					'carrier' => $email_carrier,
				);

				if ( ! $email ) {
					$wpdb->insert(
						WLSM_SETTINGS,
						array(
							'setting_key'   => 'email',
							'setting_value' => serialize( $email_data ),
							'school_id'     => $school_id,
						)
					);
				} else {
					$wpdb->update(
						WLSM_SETTINGS,
						array( 'setting_value' => serialize( $email_data ) ),
						array( 'ID'            => $email->ID )
					);
				}

				// WP_Mail.
				$wp_mail = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "wp_mail"', $school_id ) );

				$wp_mail_data = array(
					'from_name'  => $wp_mail_from_name,
					'from_email' => $wp_mail_from_email,
				);

				if ( ! $wp_mail ) {
					$wpdb->insert(
						WLSM_SETTINGS,
						array(
							'setting_key'   => 'wp_mail',
							'setting_value' => serialize( $wp_mail_data ),
							'school_id'     => $school_id,
						)
					);
				} else {
					$wpdb->update(
						WLSM_SETTINGS,
						array( 'setting_value' => serialize( $wp_mail_data ) ),
						array( 'ID'            => $wp_mail->ID )
					);
				}

				// SMTP.
				$smtp = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "smtp"', $school_id ) );

				$smtp_data = array(
					'from_name'  => $smtp_from_name,
					'host'       => $smtp_host,
					'username'   => $smtp_username,
					'password'   => $smtp_password,
					'encryption' => $smtp_encryption,
					'port'       => $smtp_port,
				);

				if ( ! $smtp ) {
					$wpdb->insert(
						WLSM_SETTINGS,
						array(
							'setting_key'   => 'smtp',
							'setting_value' => serialize( $smtp_data ),
							'school_id'     => $school_id,
						)
					);
				} else {
					$smtp_saved_data = unserialize( $smtp->setting_value );

					if ( empty( $smtp_data['password'] ) ) {
						$smtp_data['password'] = $smtp_saved_data['password'];
					}

					$wpdb->update(
						WLSM_SETTINGS,
						array( 'setting_value' => serialize( $smtp_data ) ),
						array( 'ID'            => $smtp->ID )
					);
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}

				$wpdb->query( 'COMMIT;' );

				$message = esc_html__( 'Email settings saved.', 'school-management-system' );

				wp_send_json_success( array( 'message' => $message ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function save_school_email_templates_settings() {
		$current_user = WLSM_M_Role::can( 'manage_settings' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['save-school-email-templates-settings'], 'save-school-email-templates-settings' ) ) {
				die();
			}

			$email_student_admission_enable  = isset( $_POST['email_student_admission_enable'] ) ? (bool) ( $_POST['email_student_admission_enable'] ) : 0;
			$email_student_admission_subject = isset( $_POST['email_student_admission_subject'] ) ? sanitize_text_field( stripslashes( $_POST['email_student_admission_subject'] ) ) : '';
			$email_student_admission_body    = isset( $_POST['email_student_admission_body'] ) ? wp_kses_post( stripslashes( $_POST['email_student_admission_body'] ) ) : '';

			$email_invoice_generated_enable  = isset( $_POST['email_invoice_generated_enable'] ) ? (bool) ( $_POST['email_invoice_generated_enable'] ) : 0;
			$email_invoice_generated_subject = isset( $_POST['email_invoice_generated_subject'] ) ? sanitize_text_field( stripslashes( $_POST['email_invoice_generated_subject'] ) ) : '';
			$email_invoice_generated_body    = isset( $_POST['email_invoice_generated_body'] ) ? wp_kses_post( stripslashes( $_POST['email_invoice_generated_body'] ) ) : '';

			$email_online_fee_submission_enable  = isset( $_POST['email_online_fee_submission_enable'] ) ? (bool) ( $_POST['email_online_fee_submission_enable'] ) : 0;
			$email_online_fee_submission_subject = isset( $_POST['email_online_fee_submission_subject'] ) ? sanitize_text_field( stripslashes( $_POST['email_online_fee_submission_subject'] ) ) : '';
			$email_online_fee_submission_body    = isset( $_POST['email_online_fee_submission_body'] ) ? wp_kses_post( stripslashes( $_POST['email_online_fee_submission_body'] ) ) : '';

			$email_offline_fee_submission_enable  = isset( $_POST['email_offline_fee_submission_enable'] ) ? (bool) ( $_POST['email_offline_fee_submission_enable'] ) : 0;
			$email_offline_fee_submission_subject = isset( $_POST['email_offline_fee_submission_subject'] ) ? sanitize_text_field( stripslashes( $_POST['email_offline_fee_submission_subject'] ) ) : '';
			$email_offline_fee_submission_body    = isset( $_POST['email_offline_fee_submission_body'] ) ? wp_kses_post( stripslashes( $_POST['email_offline_fee_submission_body'] ) ) : '';

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

			// Email Student Admission.
			$email_student_admission = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "email_student_admission"', $school_id ) );

			$email_student_admission_data = array(
				'enable'  => $email_student_admission_enable,
				'subject' => $email_student_admission_subject,
				'body'    => $email_student_admission_body,
			);

			if ( ! $email_student_admission ) {
				$wpdb->insert(
					WLSM_SETTINGS,
					array(
						'setting_key'   => 'email_student_admission',
						'setting_value' => serialize( $email_student_admission_data ),
						'school_id'     => $school_id,
					)
				);
			} else {
				$wpdb->update(
					WLSM_SETTINGS,
					array( 'setting_value' => serialize( $email_student_admission_data ) ),
					array( 'ID'            => $email_student_admission->ID )
				);
			}

			// Email Invoice Generated.
			$email_invoice_generated = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "email_invoice_generated"', $school_id ) );

			$email_invoice_generated_data = array(
				'enable'  => $email_invoice_generated_enable,
				'subject' => $email_invoice_generated_subject,
				'body'    => $email_invoice_generated_body,
			);

			if ( ! $email_invoice_generated ) {
				$wpdb->insert(
					WLSM_SETTINGS,
					array(
						'setting_key'   => 'email_invoice_generated',
						'setting_value' => serialize( $email_invoice_generated_data ),
						'school_id'     => $school_id,
					)
				);
			} else {
				$wpdb->update(
					WLSM_SETTINGS,
					array( 'setting_value' => serialize( $email_invoice_generated_data ) ),
					array( 'ID'            => $email_invoice_generated->ID )
				);
			}

			// Email Online Fee Submission.
			$email_online_fee_submission = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "email_online_fee_submission"', $school_id ) );

			$email_online_fee_submission_data = array(
				'enable'  => $email_online_fee_submission_enable,
				'subject' => $email_online_fee_submission_subject,
				'body'    => $email_online_fee_submission_body,
			);

			if ( ! $email_online_fee_submission ) {
				$wpdb->insert(
					WLSM_SETTINGS,
					array(
						'setting_key'   => 'email_online_fee_submission',
						'setting_value' => serialize( $email_online_fee_submission_data ),
						'school_id'     => $school_id,
					)
				);
			} else {
				$wpdb->update(
					WLSM_SETTINGS,
					array( 'setting_value' => serialize( $email_online_fee_submission_data ) ),
					array( 'ID'            => $email_online_fee_submission->ID )
				);
			}

			// Email Offline Fee Submission.
			$email_offline_fee_submission = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "email_offline_fee_submission"', $school_id ) );

			$email_offline_fee_submission_data = array(
				'enable'  => $email_offline_fee_submission_enable,
				'subject' => $email_offline_fee_submission_subject,
				'body'    => $email_offline_fee_submission_body,
			);

			if ( ! $email_offline_fee_submission ) {
				$wpdb->insert(
					WLSM_SETTINGS,
					array(
						'setting_key'   => 'email_offline_fee_submission',
						'setting_value' => serialize( $email_offline_fee_submission_data ),
						'school_id'     => $school_id,
					)
				);
			} else {
				$wpdb->update(
					WLSM_SETTINGS,
					array( 'setting_value' => serialize( $email_offline_fee_submission_data ) ),
					array( 'ID'            => $email_offline_fee_submission->ID )
				);
			}

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}

			$wpdb->query( 'COMMIT;' );

			$message = esc_html__( 'Email templates saved.', 'school-management-system' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function save_school_payment_method_settings() {
		$current_user = WLSM_M_Role::can( 'manage_settings' );

		if ( ! $current_user ) {
			die();
		}

		$school_id = $current_user['school']['id'];

		try {
			ob_start();
			global $wpdb;

			if ( ! wp_verify_nonce( $_POST['save-school-payment-method-settings'], 'save-school-payment-method-settings' ) ) {
				die();
			}

			$stripe_enable          = isset( $_POST['stripe_enable'] ) ? (bool) ( $_POST['stripe_enable'] ) : 0;
			$stripe_publishable_key = isset( $_POST['stripe_publishable_key'] ) ? sanitize_text_field( $_POST['stripe_publishable_key'] ) : '';
			$stripe_secret_key      = isset( $_POST['stripe_secret_key'] ) ? sanitize_text_field( $_POST['stripe_secret_key'] ) : '';

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

			// Stripe.
			$stripe = $wpdb->get_row( $wpdb->prepare( 'SELECT ID, setting_value FROM ' . WLSM_SETTINGS . ' WHERE school_id = %d AND setting_key = "stripe"', $school_id ) );

			$stripe_data = array(
				'enable'          => $stripe_enable,
				'publishable_key' => $stripe_publishable_key,
				'secret_key'      => $stripe_secret_key,
			);

			if ( ! $stripe ) {
				$wpdb->insert(
					WLSM_SETTINGS,
					array(
						'setting_key'   => 'stripe',
						'setting_value' => serialize( $stripe_data ),
						'school_id'     => $school_id,
					)
				);
			} else {
				$wpdb->update(
					WLSM_SETTINGS,
					array( 'setting_value' => serialize( $stripe_data ) ),
					array( 'ID'            => $stripe->ID )
				);
			}

			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				throw new Exception( $buffer );
			}

			$wpdb->query( 'COMMIT;' );

			$message = esc_html__( 'Payment settings saved.', 'school-management-system' );

			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}
}
