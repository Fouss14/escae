<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Notify.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Payment.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Accountant.php';

class WLSM_P_Invoice {
	public static function get_students_with_pending_invoices() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'get-pending-invoices-students' ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$school_id  = isset( $_POST['school_id'] ) ? absint( $_POST['school_id'] ) : 0;
			$session_id = isset( $_POST['session_id'] ) ? absint( $_POST['session_id'] ) : 0;
			$class_id   = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;

			$name = isset( $_POST['student_name'] ) ? sanitize_text_field( $_POST['student_name'] ) : '';

			// Start validation.
			$errors = array();

			// Check if session exists.
			$session = WLSM_M_Session::get_session( $session_id );

			if ( ! $session ) {
				throw new Exception( esc_html__( 'Session not found.', 'school-management-system' ) );
			}

			if ( empty( $school_id ) ) {
				$errors['school_id'] = esc_html__( 'Please select a school.', 'school-management-system' );

			} else {
				// Checks if school exists.
				$school = WLSM_M_School::get_active_school( $school_id );

				if ( ! $school ) {
					$errors['school_id'] = esc_html__( 'Please select a school.', 'school-management-system' );
				}
			}

			if ( count( $errors ) > 0 ) {
				wp_send_json_error( $errors );
			}

			// Checks if class exists in the school.
			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );

			if ( empty( $class_id ) ) {
				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Please select a class.', 'school-management-system' );
				}
			} else {
				if ( ! $class_school ) {
					$errors['class_id'] = esc_html__( 'Class not found.', 'school-management-system' );
				}
			}

			if ( count( $errors ) > 0 ) {
				wp_send_json_error( $errors );
			}

			$class_school_id = $class_school->ID;

			$name = trim( $name );
			if ( empty( $name ) ) {
				$errors['student_name'] = esc_html__( 'Please specify the name.', 'school-management-system' );
				wp_send_json_error( $errors );
			} else if ( strlen( $name ) < 2 ) {
				$errors['student_name'] = esc_html__( 'Please provide at least 2 characters.', 'school-management-system' );
				wp_send_json_error( $errors );
			}

			// Get class students in a session with the name provided.
			$students = $wpdb->get_results(
				$wpdb->prepare( 'SELECT sr.ID, sr.name, sr.enrollment_number, c.label as class_label, se.label as section_label, sr.roll_number FROM ' . WLSM_STUDENT_RECORDS . ' as sr 
					JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
					JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
					JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
					JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
					WHERE sr.session_id = %d AND se.class_school_id = %d AND sr.name LIKE "%s" AND sr.is_active = 1 GROUP BY sr.ID', $session_id, $class_school_id, '%' . $wpdb->esc_like( $name ) . '%' )
			);

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
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
				<!-- Students with pending invoices. -->
				<div class="wlsm-table-section">
					<div class="wlsm-table-caption wlsm-font-bold">
						<?php
						printf(
							wp_kses(
								/* translators: %s: number of students */
								_n( '%d Student found.', '%d Students found.', count( $students ), 'school-management-system' ),
								array( 'span' => array( 'class' => array() ) )
							),
							count( $students )
						);
						?>
					</div>

					<div class="table-responsive w-100">
						<table class="table table-bordered wlsm-students-with-pending-invoices-table">
							<thead>
								<tr class="bg-primary text-white">
									<th><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $students as $row ) { ?>
								<tr>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $row->name ) ); ?>
									</td>
									<td>
										<?php echo esc_html( $row->enrollment_number ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Class::get_label_text( $row->class_label ) ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) ); ?>
									</td>
									<td>
										<?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $row->roll_number ) ); ?>
									</td>
									<td>
										<a class="wlsm-view-student-pending-invoices" data-student="<?php echo esc_attr( $row->ID ); ?>" data-nonce="<?php echo esc_attr( esc_attr( wp_create_nonce( 'view-student-invoices-' . $row->ID ) ) ); ?>" href="#">
											<span class="dashicons dashicons-search"></span>
											<?php esc_html_e( 'View', 'school-management-system' ); ?>
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="wlsm-student-pending-invoices"></div>
				<?php
				} else {
				?>
				<div class="wlsm-alert wlsm-alert-warning wlsm-font-bold">
					<span class="wlsm-icon wlsm-icon-red">&#33;</span>
					<?php esc_html_e( 'There is no student with this name having pending fees.', 'school-management-system' ); ?>
				</div>
				<?php
				}
				$html = ob_get_clean();

				wp_send_json_success( array( 'html' => $html ) );

			} catch ( Exception $exception ) {
				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
				} else {
					$response = $exception->getMessage();
				}
				wp_send_json_error( $response );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function get_student_pending_invoices() {
		$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

		if ( ! wp_verify_nonce( $_POST['nonce'], 'view-student-invoices-' . $student_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			// Checks if student exists.
			$student = WLSM_M_Staff_General::get_active_student( $student_id );

			if ( ! $student ) {
				die;
			}

			// Get student pending invoices.
			$invoices = WLSM_M_Staff_Accountant::get_student_pending_invoices( $student_id );

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		try {
			ob_start();
			?>
			<!-- Student details -->
			<div class="wlsm-invoices-section">
				<span class="wlsm-student-section-title">
					<?php esc_html_e( 'Student Detail', 'school-management-system' ); ?>
				</span>
				<ul class="wlsm-list-group">
					<li class="wlsm-list-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student->student_name ) ); ?></span>
					</li>
					<li class="wlsm-list-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( $student->enrollment_number ); ?></span>
					</li>
					<li class="wlsm-list-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Class::get_label_text( $student->class_label ) ); ?></span>
					</li>
					<li class="wlsm-list-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $student->section_label ) ); ?></span>
					</li>
					<li class="wlsm-list-item">
						<span class="wlsm-font-bold"><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?>:</span>
						<span><?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $student->roll_number ) ); ?></span>
					</li>
				</ul>
			</div>
			<?php
			require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/pending_fee_invoices.php';

			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}
	}

	public static function get_student_pending_invoice() {
		$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

		if ( ! wp_verify_nonce( $_POST['nonce'], 'view-student-invoice-' . $invoice_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			// Checks if pending invoice exists.
			$invoice = WLSM_M_Staff_Accountant::get_student_pending_invoice( $invoice_id );

			if ( ! $invoice ) {
				die;
			}

			$school_id = $invoice->school_id;

			$due = $invoice->payable - $invoice->paid;

			$invoice_partial_payment = $invoice->partial_payment;

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		$currency = WLSM_Config::currency();

		// Stripe settings.
		$settings_stripe      = WLSM_M_Setting::get_settings_stripe( $school_id );
		$school_stripe_enable = $settings_stripe['enable'];

		try {
			ob_start();
			?>
			<!-- Invoice and student details -->
			<div class="wlsm-invoices-section wlsm-invoices-student-detail">
				<div class="wlsm-invoices-detail-section">
					<span class="wlsm-invoices-section-title">
						<?php esc_html_e( 'Invoice Detail', 'school-management-system' ); ?>
					</span>
					<ul class="wlsm-list-group">
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Invoice Title', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Invoice Number', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( $invoice->invoice_number ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Date Issued', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_date_text( $invoice->date_issued ) ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Due Date', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_date_text( $invoice->due_date ) ); ?></span>
						</li>
					</ul>
				</div>

				<div class="wlsm-invoices-detail-section">
					<span class="wlsm-invoices-section-title">
						<?php esc_html_e( 'Student Detail', 'school-management-system' ); ?>
					</span>
					<ul class="wlsm-list-group">
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $invoice->student_name ) ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( $invoice->enrollment_number ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_M_Class::get_label_text( $invoice->class_label ) ); ?></span>
						</li>
						<li class="wlsm-list-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $invoice->section_label ) ); ?></span>
						</li>
					</ul>
				</div>
			</div>

			<!-- Invoice status and payment -->
			<div id="wlsm-pay-invoice-amount-section" class="wlsm-pt-2">
				<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice_id ); ?>" id="wlsm_invoice_id">
				<div class="wlsm-form-group">
					<label for="wlsm_payment_amount" class="wlsm-font-bold">
						<?php esc_html_e( 'Fees Due', 'school-management-system' ); ?>:
					</label>
					<?php
					echo esc_html( WLSM_Config::get_money_text( $due ) );
					if ( $invoice_partial_payment ) {
					?>
					<br>
					<input type="number" step="any" min="0" name="payment_amount" class="wlsm-form-control" id="wlsm_payment_amount" placeholder="<?php esc_attr_e( 'Enter amount to pay', 'school-management-system' ); ?>">
					<?php
					} else {
					?>
					<input type="hidden" name="payment_amount" id="wlsm_payment_amount" value="<?php echo esc_attr( WLSM_Config::sanitize_money( $due ) ); ?>">
					<?php
					}
					?>
				</div>
				<div class="wlsm-form-group">
					<label class="wlsm-font-bold">
						<?php esc_html_e( 'Select Payment Method', 'school-management-system' ); ?>:
					</label>
				<?php
				$payment_methods_count = 0;
				if ( $school_stripe_enable && WLSM_Payment::currency_supports_stripe( $currency ) ) { ?>
					<br>
					<label class="radio-inline wlsm-mr-3">
						<input type="radio" name="payment_method" class="wlsm-mr-2" value="stripe" id="wlsm-payment-stripe">
						<?php echo esc_html( WLSM_M_Invoice::get_payment_method_text( 'stripe' ) ); ?>
					</label>
					<?php
					$payment_methods_count++;
				}
				?>
				</div>
				<?php if ( $payment_methods_count > 0 ) { ?>
				<div class="wlsm-border-top wlsm-pt-2 wlsm-mt-2">
					<button class="button wlsm-btn btn btn-primary" type="button" id="wlsm-pay-invoice-amount-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'pay-invoice-amount-' . $invoice_id ) ); ?>">
						<?php esc_html_e( 'Proceed to Pay', 'school-management-system' ); ?>
					</button>
				</div>
				<div class="wlsm-pay-invoice-amount"></div>
				<?php } else { ?>
				<div class="wlsm-border-top wlsm-pt-2 wlsm-mt-2">
					<span class="wlsm-text-danger wlsm-font-bold"><?php esc_html_e( 'No payment method available right now.', 'school-management-system' ); ?></span>
				</div>
				<?php } ?>
			</div>
			<?php
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}
	}

	public static function pay_invoice_amount() {
		$invoice_id = isset( $_POST['invoice_id'] ) ? absint( $_POST['invoice_id'] ) : 0;

		if ( ! wp_verify_nonce( $_POST['nonce'], 'pay-invoice-amount-' . $invoice_id ) ) {
			die();
		}

		try {
			ob_start();
			global $wpdb;

			$payment_amount = isset( $_POST['payment_amount'] ) ? WLSM_Config::sanitize_money( $_POST['payment_amount'] ) : 0;
			$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';

			// Start validation.
			$errors = array();

			// Checks if pending invoice exists.
			$invoice = WLSM_M_Staff_Accountant::get_student_pending_invoice( $invoice_id );

			if ( ! $invoice ) {
				throw new Exception( esc_html__( 'Invoice not found or already paid.', 'school-management-system' ) );
			}

			$school_id = $invoice->school_id;

			// Checks if school exists.
			$school = WLSM_M_School::get_active_school( $school_id );

			if ( ! $school ) {
				wp_send_json_error( esc_html__( 'Your school is currently inactive.', 'school-management-system' ) );
			}

			$school_name = WLSM_M_School::get_label_text( $school->label );

			$due = $invoice->payable - $invoice->paid;

			$invoice_partial_payment = $invoice->partial_payment;

			if ( ! $payment_amount ) {
				$errors['payment_amount'] = esc_html__( 'Please enter a valid amount.', 'school-management-system' );
			} else {
				if ( $payment_amount > $due ) {
					$errors['payment_amount'] = esc_html__( 'Amount exceeded due amount.', 'school-management-system' );
				} else {
					if ( ! $invoice_partial_payment ) {
						$payment_amount = $due;
					}
				}
			}

			if ( ! $payment_method ) {
				throw new Exception( esc_html__( 'Please select a payment method.', 'school-management-system' ) );
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
			} else {
				$response = $exception->getMessage();
			}
			wp_send_json_error( $response );
		}

		if ( count( $errors ) < 1 ) {
			// Basic details.
			$name    = WLSM_M_Staff_Class::get_name_text( $invoice->student_name );
			$email   = $invoice->login_email ? $invoice->login_email : $invoice->email;
			$address = $invoice->address;

			$description = sprintf(
				/* translators: 1: invoice title, 2: invoice number */
				__( 'Invoice: %1$s (%2$s)', 'school-management-system' ),
				esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) ),
				esc_html( $invoice->invoice_number )
			);

			$invoice_number = $invoice->invoice_number;

			// School details.
			$settings_general = WLSM_M_Setting::get_settings_general( $school_id );
			$school_logo_url  = esc_url( wp_get_attachment_url( $settings_general['school_logo'] ) );

			// Currency.
			$currency = WLSM_Config::currency();

			try {
				ob_start();

				if ( 'stripe' === $payment_method ) {
					$settings_stripe               = WLSM_M_Setting::get_settings_stripe( $school_id );
					$school_stripe_enable          = $settings_stripe['enable'];
					$school_stripe_publishable_key = $settings_stripe['publishable_key'];

					if ( ! $school_stripe_enable || ! WLSM_Payment::currency_supports_stripe( $currency ) ) {
						wp_send_json_error( esc_html__( 'Stripe payment method is currently unavailable.', 'school-management-system' ) );
					}

					$amount_in_cents = $payment_amount * 100;
					$security        = wp_create_nonce( 'pay-with-stripe' );
					$stripe_key      = $school_stripe_publishable_key;

					$pay_with_stripe_text = sprintf(
						/* translators: %s: amount to pay */
						__( 'Pay Amount %s using Stripe', 'school-management-system' ),
						esc_html( WLSM_Config::get_money_text( $payment_amount ) )
					);

					$html = "<button class='wlsm-mt-2 float-right button btn btn-success' id='wlsm-stripe-btn'>$pay_with_stripe_text</button>";

					$json = json_encode(
						array(
							'action'          => 'wlsm-p-pay-with-stripe',
							'payment_method'  => esc_attr( $payment_method ),
							'stripe_key'      => esc_attr( $stripe_key ),
							'amount_in_cents' => esc_attr( $amount_in_cents ),
							'currency'        => esc_attr( $currency ),
							'school_name'     => esc_attr( $school_name ),
							'school_logo_url' => esc_attr( $school_logo_url ),
							'security'        => esc_attr( $security ),
							'name'            => esc_attr( $name ),
							'email'           => esc_attr( $email ),
							'address'         => esc_attr( $address ),
							'invoice_id'      => esc_attr( $invoice_id ),
							'invoice_number'  => esc_attr( $invoice_number ),
							'payment_amount'  => esc_attr( $payment_amount ),
							'description'     => esc_attr( $description ),
						)
					);

				} else {
					wp_send_json_error( esc_html__( 'Please select a valid payment method.', 'school-management-system' ) );
				}

				wp_send_json_success( array( 'html' => $html, 'json' => $json ) );

			} catch ( Exception $exception ) {
				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					$response = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
				} else {
					$response = $exception->getMessage();
				}
				wp_send_json_error( $response );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function process_stripe() {
		if ( ! wp_verify_nonce( $_POST['security'], 'pay-with-stripe' ) ) {
			die();
		}

		$unexpected_error_message = esc_html__( 'An unexpected error occurred!', 'school-management-system' );
		if ( ! isset( $_POST['stripeToken'] ) || ! isset( $_POST['amount'] ) ) {
			wp_send_json_error( $unexpected_error_message );
		}

		require_once WLSM_PLUGIN_DIR_PATH . 'includes/vendor/autoload.php';

		// Currency.
		$currency = WLSM_Config::currency();

		$stripe_token    = sanitize_text_field( $_POST['stripeToken'] );
		$amount_in_cents = WLSM_Config::sanitize_money( $_POST['amount'] );

		$invoice_id = absint( $_POST['invoice_id'] );

		// Checks if pending invoice exists.
		$invoice = WLSM_M_Staff_Accountant::get_student_pending_invoice( $invoice_id );

		if ( ! $invoice ) {
			wp_send_json_error( esc_html__( 'Invoice not found or already paid.', 'school-management-system' ) );
		}

		$partial_payment = $invoice->partial_payment;

		$due = $invoice->payable - $invoice->paid;

		$school_id  = $invoice->school_id;
		$session_id = $invoice->session_id;

		$description = sprintf(
			/* translators: 1: invoice title, 2: invoice number */
			__( 'Invoice: %1$s (%2$s)', 'school-management-system' ),
			esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) ),
			esc_html( $invoice->invoice_number )
		);

		global $wpdb;

		$payment_amount = $amount_in_cents / 100;

		if ( ( $payment_amount <= 0 ) || ( $payment_amount > $due ) || ( ! $partial_payment && ( $payment_amount != $due ) ) ) {
			wp_send_json_error( $unexpected_error_message );
		}

		$settings_stripe          = WLSM_M_Setting::get_settings_stripe( $school_id );
		$school_stripe_secret_key = $settings_stripe['secret_key'];

		$secret_key = $school_stripe_secret_key;

		try {
			\Stripe\Stripe::setApiKey( $secret_key );
			$charge = \Stripe\Charge::create(
				array(
					'amount'      => $amount_in_cents,
					'currency'    => $currency,
					'description' => $description,
					'source'      => $stripe_token
				)
			);
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}

		if ( ! ( $charge && $charge->captured && ( $charge->amount == $amount_in_cents ) ) ) {
			wp_send_json_error( esc_html__( 'Unable to capture the payment.', 'school-management-system' ) );
		}

		$transaction_id = $charge->id;

		try {
			$wpdb->query( 'BEGIN;' );

			$receipt_number = WLSM_M_Invoice::get_receipt_number( $school_id );

			// Payment data.
			$payment_data = array(
				'receipt_number'    => $receipt_number,
				'amount'            => $payment_amount,
				'transaction_id'    => $transaction_id,
				'payment_method'    => 'stripe',
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

			$wpdb->query( 'COMMIT;' );

			if ( isset( $new_payment_id ) ) {
				// Notify for online fee submission.
				$data = array(
					'school_id'  => $school_id,
					'session_id' => $session_id,
					'payment_id' => $new_payment_id,
				);

				wp_schedule_single_event( time() + 30, 'wlsm_notify_for_online_fee_submission', $data );
			}

			wp_send_json_success( array( 'message' => esc_html__( 'Payment made successfully.', 'school-management-system' ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $unexpected_error_message );
		}
	}
}
