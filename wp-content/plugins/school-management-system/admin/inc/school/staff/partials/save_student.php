<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$page_url = WLSM_M_Staff_General::get_students_page_url();

$gender_list = WLSM_Helper::gender_list();

$blood_group_list = WLSM_Helper::blood_group_list();

$student = NULL;

$nonce_action = 'add-admission';
$action       = 'wlsm-add-admission';

$name              = '';
$gender            = 'male';
$dob               = '';
$religion          = '';
$caste             = '';
$blood_group       = '';
$address           = '';
$phone             = '';
$email             = '';
$admission_date    = '';
$class_id          = '';
$class_label       = '';
$section_id        = '';
$admission_number  = '';
$roll_number       = '';
$photo_id          = '';
$father_name       = '';
$father_phone      = '';
$father_occupation = '';
$mother_name       = '';
$mother_phone      = '';
$mother_occupation = '';
$username          = '';
$login_email       = '';
$is_active         = 1;

$sections = array();

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$current_user = WLSM_M_Role::can( 'manage_students' );

	$id      = absint( $_GET['id'] );
	$student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $id );

	if ( $student ) {
		$nonce_action = 'edit-student-' . $student->ID;
		$action       = 'wlsm-edit-student';

		$name              = $student->student_name;
		$gender            = $student->gender;
		$dob               = $student->dob;
		$religion          = $student->religion;
		$caste             = $student->caste;
		$blood_group       = $student->blood_group;
		$address           = $student->address;
		$phone             = $student->phone;
		$email             = $student->email;
		$admission_date    = $student->admission_date;
		$class_id          = $student->class_id;
		$class_label       = $student->class_label;
		$section_id        = $student->section_id;
		$admission_number  = $student->admission_number;
		$roll_number       = $student->roll_number;
		$photo_id          = $student->photo_id;
		$father_name       = $student->father_name;
		$father_phone      = $student->father_phone;
		$father_occupation = $student->father_occupation;
		$mother_name       = $student->mother_name;
		$mother_phone      = $student->mother_phone;
		$mother_occupation = $student->mother_occupation;
		$username          = $student->username;
		$login_email       = $student->login_email;
		$is_active         = $student->is_active;

		$sections = WLSM_M_Staff_Class::fetch_sections( $student->class_school_id );
	}
} else {
	$current_user = WLSM_M_Role::can( 'manage_admissions' );

	$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );

	if ( isset( $_GET['inquiry_id'] ) && ! empty( $_GET['inquiry_id'] ) ) {
		$inquiry_id = absint( $_GET['inquiry_id'] );
		$inquiry    = WLSM_M_Staff_General::fetch_inquiry( $school_id, $inquiry_id );
		if ( $inquiry && $inquiry->is_active ) {
			$name     = $inquiry->name;
			$phone    = $inquiry->phone;
			$email    = $inquiry->email;
			$class_id = $inquiry->class_id;

			$class_school = WLSM_M_Staff_Class::get_class( $school_id, $class_id );
			if ( $class_school ) {
				$sections = WLSM_M_Staff_Class::fetch_sections( $class_school->ID );
			}
		}
	}
}

if ( ! $current_user ) {
	die();
}

$fee_periods = WLSM_Helper::fee_period_list();
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ( $student ) {
						/* translators: 1: student name, 2: enrollment number */
						printf( esc_html__( 'Edit Student: %1$s (Enrollment Number - %2$s)', 'school-management-system' ), esc_html( $name ), esc_html( $student->enrollment_number ) );
					} else {
						/* translators: %s: session label */
						printf( esc_html__( 'New Admission For Session: %s', 'school-management-system' ), esc_html( WLSM_M_Session::get_label_text( $current_session['label'] ) ) );
					}
					?>
				</span>
			</span>
			<?php if ( $student ) { ?>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-users"></i>&nbsp;
					<?php esc_html_e( 'View All', 'school-management-system' ); ?>
				</a>
			</span>
			<?php } else if ( WLSM_M_Role::check_permission( array( 'manage_students' ), $current_school['permissions'] ) ) { ?>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-users"></i>&nbsp;
					<?php esc_html_e( 'View Students', 'school-management-system' ); ?>
				</a>
			</span>
			<?php } ?>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="<?php echo esc_attr( $action ); ?>-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">

			<?php
			if ( $student ) {
			?>
			<input type="hidden" name="student_id" value="<?php echo esc_attr( $student->ID ); ?>">
			<?php
			} else {
				if ( isset( $inquiry ) && $inquiry ) {
			?>
			<input type="hidden" name="inquiry_id" value="<?php echo esc_attr( $inquiry->ID ); ?>">
			<?php
				}
			}
			?>

			<!-- Personal Detail -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Personal Detail', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_name" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Student Name', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="name" class="form-control" id="wlsm_name" placeholder="<?php esc_attr_e( 'Enter student name', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripcslashes( $name ) ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Gender', 'school-management-system' ); ?>:
						</label>
						<br>
						<?php
						foreach ( $gender_list as $key => $value ) {
							reset( $gender_list );
						?>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="gender" id="wlsm_gender_<?php echo esc_attr( $value ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, $gender, true ); ?>>
							<label class="ml-1 form-check-label wlsm-font-bold" for="wlsm_gender_<?php echo esc_attr( $value ); ?>">
								<?php echo esc_html( $value ); ?>
							</label>
						</div>
						<?php
						}
						?>
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_date_of_birth" class="wlsm-font-bold">
							<?php esc_html_e( 'Date of Birth', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="dob" class="form-control" id="wlsm_date_of_birth" placeholder="<?php esc_attr_e( 'Enter date of birth', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $dob ) ); ?>">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_religion" class="wlsm-font-bold">
							<?php esc_html_e( 'Religion', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="religion" class="form-control" id="wlsm_religion" placeholder="<?php esc_attr_e( 'Enter religion', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripcslashes( $religion ) ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_caste" class="wlsm-font-bold">
							<?php esc_html_e( 'Caste', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="caste" class="form-control" id="wlsm_caste" placeholder="<?php esc_attr_e( 'Enter caste', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripcslashes( $caste ) ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_blood_group" class="wlsm-font-bold">
							<?php esc_html_e( 'Blood Group', 'school-management-system' ); ?>:
						</label>
						<select name="blood_group" class="form-control selectpicker" id="wlsm_blood_group" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select Blood Group', 'school-management-system' ); ?></option>
							<?php foreach ( $blood_group_list as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $blood_group, true ); ?>>
								<?php echo esc_html( $value ); ?>
							</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_address" class="wlsm-font-bold">
							<?php esc_html_e( 'Address', 'school-management-system' ); ?>:
						</label>
						<textarea name="address" class="form-control" id="wlsm_address" cols="30" rows="3" placeholder="<?php esc_attr_e( 'Enter student address', 'school-management-system' ); ?>"><?php echo esc_html( $address ); ?></textarea>
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_phone" class="wlsm-font-bold">
							<?php esc_html_e( 'Phone', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="phone" class="form-control" id="wlsm_phone" placeholder="<?php esc_attr_e( 'Enter student phone number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $phone ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_email" class="wlsm-font-bold">
							<?php esc_html_e( 'Email', 'school-management-system' ); ?>:
						</label>
						<input type="email" name="email" class="form-control" id="wlsm_email" placeholder="<?php esc_attr_e( 'Enter student email address', 'school-management-system' ); ?>" value="<?php echo esc_attr( $email ); ?>">
					</div>
				</div>
			</div>

			<!-- Admission Detail -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Admission Detail', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_admission_date" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Admission Date', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="admission_date" class="form-control" id="wlsm_admission_date" placeholder="<?php esc_attr_e( 'Enter admission date', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $admission_date ) ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_class" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Class', 'school-management-system' ); ?>:
						</label>
						<?php if ( $student ) { ?>
						<div class="ml-2"><?php echo esc_html( WLSM_M_Class::get_label_text( $class_label ) ); ?></div>
						<?php } else { ?>
						<select name="class_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>" id="wlsm_class" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php foreach ( $classes as $class ) { ?>
							<option value="<?php echo esc_attr( $class->ID ); ?>" <?php selected( $class->ID, $class_id, true ); ?>>
								<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
							</option>
							<?php } ?>
						</select>
						<?php } ?>
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_section" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Section', 'school-management-system' ); ?>:
						</label>
						<select name="section_id" class="form-control selectpicker" id="wlsm_section" data-live-search="true" title="<?php esc_attr_e( 'Select Section', 'school-management-system' ); ?>">
							<?php foreach ( $sections as $section ) { ?>
							<option value="<?php echo esc_attr( $section->ID ); ?>" <?php selected( $section->ID, $section_id, true ); ?>>
								<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $section->label ) ); ?>
							</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_admission_number" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Admission Number', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="admission_number" class="form-control" id="wlsm_admission_number" placeholder="<?php esc_attr_e( 'Enter admission number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $admission_number ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_roll_number" class="wlsm-font-bold">
							<?php esc_html_e( 'Roll Number', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="roll_number" class="form-control" id="wlsm_roll_number" placeholder="<?php esc_attr_e( 'Enter class roll number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $roll_number ); ?>">
					</div>

					<div class="form-group col-md-4">
						<div class="wlsm-photo-box">
							<div class="wlsm-photo-section">
								<label for="wlsm_photo" class="wlsm-font-bold">
									<?php esc_html_e( 'Upload Photo', 'school-management-system' ); ?>:
								</label>
								<?php if ( ! empty ( $photo_id ) ) { ?>
									<img src="<?php echo esc_url( wp_get_attachment_url( $photo_id ) ); ?>" class="img-responsive wlsm-photo">
								<?php } ?>
								<div class="custom-file mb-3">
									<input type="file" class="custom-file-input" id="wlsm_photo" name="photo">
									<label class="custom-file-label" for="wlsm_photo">
										<?php esc_html_e( 'Choose File', 'school-management-system' ); ?>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Parent Detail -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Parent Detail', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_father_name" class="wlsm-font-bold">
							<?php esc_html_e( 'Father Name', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="father_name" class="form-control" id="wlsm_father_name" placeholder="<?php esc_attr_e( 'Enter father name', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripcslashes( $father_name ) ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_father_phone" class="wlsm-font-bold">
							<?php esc_html_e( 'Father Phone', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="father_phone" class="form-control" id="wlsm_father_phone" placeholder="<?php esc_attr_e( 'Enter father phone number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $father_phone ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_father_occupation" class="wlsm-font-bold">
							<?php esc_html_e( 'Father Occupation', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="father_occupation" class="form-control" id="wlsm_father_occupation" placeholder="<?php esc_attr_e( 'Enter father occupation', 'school-management-system' ); ?>" value="<?php echo esc_attr( $father_occupation ); ?>">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_mother_name" class="wlsm-font-bold">
							<?php esc_html_e( 'Mother Name', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="mother_name" class="form-control" id="wlsm_mother_name" placeholder="<?php esc_attr_e( 'Enter mother name', 'school-management-system' ); ?>" value="<?php echo esc_attr( stripcslashes( $mother_name ) ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_mother_phone" class="wlsm-font-bold">
							<?php esc_html_e( 'Mother Phone', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="mother_phone" class="form-control" id="wlsm_mother_phone" placeholder="<?php esc_attr_e( 'Enter mother phone number', 'school-management-system' ); ?>" value="<?php echo esc_attr( $mother_phone ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_mother_occupation" class="wlsm-font-bold">
							<?php esc_html_e( 'Mother Occupation', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="mother_occupation" class="form-control" id="wlsm_mother_occupation" placeholder="<?php esc_attr_e( 'Enter mother occupation', 'school-management-system' ); ?>" value="<?php echo esc_attr( $mother_occupation ); ?>">
					</div>
				</div>
			</div>

			<!-- Student Login Detail -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Login Detail', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-12">
						<div class="form-check form-check-inline">
							<input <?php checked( false, (bool) $username, true ); ?> class="form-check-input" type="radio" name="student_new_or_existing" id="wlsm_student_disallow_login" value="">
							<label class="ml-1 form-check-label text-secondary font-weight-bold" for="wlsm_student_disallow_login">
								<?php esc_html_e( 'Disallow Login?', 'school-management-system' ); ?>
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input <?php checked( true, (bool) $username, true ); ?> class="form-check-input" type="radio" name="student_new_or_existing" id="wlsm_student_existing_user" value="existing_user">
							<label class="ml-1 form-check-label text-primary font-weight-bold" for="wlsm_student_existing_user">
								<?php esc_html_e( 'Existing User?', 'school-management-system' ); ?>
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="student_new_or_existing" id="wlsm_student_new_user" value="new_user">
							<label class="ml-1 form-check-label text-danger font-weight-bold" for="wlsm_student_new_user">
								<?php esc_html_e( 'New User?', 'school-management-system' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="form-row wlsm-student-existing-user">
					<div class="form-group col-md-4">
						<label for="wlsm_existing_username" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Existing Username', 'school-management-system' ); ?>:
							<?php if ( $username ) { ?>
								<small>
									<em class="text-secondary">
									<?php esc_html_e( 'Usernames cannot be changed.', 'school-management-system' ); ?>
									</em>
								</small>
							<?php } ?>
						</label>
						<input type="text" name="existing_username" class="form-control" id="wlsm_existing_username" placeholder="<?php esc_attr_e( 'Enter existing username', 'school-management-system' ); ?>" value="<?php echo esc_attr( $username ); ?>" <?php if ( $username ) { echo esc_attr('readonly'); } ?>>
					</div>

					<?php if ( $username ) { ?>
					<div class="form-group col-md-4">
						<label for="wlsm_new_login_email" class="wlsm-font-bold">
							<?php esc_html_e( 'Login Email', 'school-management-system' ); ?>:
						</label>
						<input type="email" name="new_login_email" class="form-control" id="wlsm_new_login_email" placeholder="<?php esc_attr_e( 'Enter login email', 'school-management-system' ); ?>" value="<?php echo esc_attr( $login_email ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_new_login_password" class="wlsm-font-bold">
							<?php esc_html_e( 'Password', 'school-management-system' ); ?>:
						</label>
						<input type="password" name="new_password" class="form-control" id="wlsm_new_login_password" placeholder="<?php esc_attr_e( 'Enter password', 'school-management-system' ); ?>">
					</div>
					<?php } ?>
				</div>

				<div class="form-row wlsm-student-new-user">
					<div class="form-group col-md-4">
						<label for="wlsm_username" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Username', 'school-management-system' ); ?>:
						</label>
						<input type="text" name="username" class="form-control" id="wlsm_username" placeholder="<?php esc_attr_e( 'Enter username', 'school-management-system' ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_login_email" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Login Email', 'school-management-system' ); ?>:
						</label>
						<input type="email" name="login_email" class="form-control" id="wlsm_login_email" placeholder="<?php esc_attr_e( 'Enter login email', 'school-management-system' ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_login_password" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Password', 'school-management-system' ); ?>:
						</label>
						<input type="password" name="password" class="form-control" id="wlsm_login_password" placeholder="<?php esc_attr_e( 'Enter password', 'school-management-system' ); ?>">
					</div>
				</div>
			</div>

			<!-- Status -->
			<div class="wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<?php esc_html_e( 'Status', 'school-management-system' ); ?>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-12">
						<div class="form-check form-check-inline">
							<input <?php checked( 1, $is_active, true ); ?> class="form-check-input" type="radio" name="is_active" id="wlsm_status_active" value="1">
							<label class="ml-1 form-check-label text-primary font-weight-bold" for="wlsm_status_active">
								<?php echo esc_html( WLSM_M_Staff_Class::get_active_text() ); ?>
							</label>
						</div>
						<div class="form-check form-check-inline">
							<input <?php checked( 0, $is_active, true ); ?> class="form-check-input" type="radio" name="is_active" id="wlsm_status_inactive" value="0">
							<label class="ml-1 form-check-label text-danger font-weight-bold" for="wlsm_status_inactive">
								<?php echo esc_html( WLSM_M_Staff_Class::get_inactive_text() ); ?>
							</label>
						</div>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="<?php echo esc_attr( $action ); ?>-btn">
						<?php
						if ( $student ) {
							?>
							<i class="fas fa-save"></i>&nbsp;
							<?php
							esc_html_e( 'Update Student', 'school-management-system' );
						} else {
							?>
							<i class="fas fa-plus-square"></i>&nbsp;
							<?php
							esc_html_e( 'Submit', 'school-management-system' );
						}
						?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
