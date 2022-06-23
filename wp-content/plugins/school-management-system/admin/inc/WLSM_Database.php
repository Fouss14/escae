<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/constants.php';

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';

class WLSM_Database {
	public static function activation() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$wpdb->query( "ALTER TABLE " . WLSM_USERS . " ENGINE = InnoDB" );
		$wpdb->query( "ALTER TABLE " . WLSM_POSTS . " ENGINE = InnoDB" );

		/* Create schools table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_SCHOOLS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label varchar(191) DEFAULT NULL,
				phone varchar(255) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				address text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				last_enrollment_count bigint(20) NOT NULL DEFAULT '0',
				last_invoice_count bigint(20) NOT NULL DEFAULT '0',
				last_payment_count bigint(20) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (label)
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create settings table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_SETTINGS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				setting_key varchar(191) DEFAULT NULL,
				setting_value text DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (school_id, setting_key),
				INDEX (school_id),
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create classes table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_CLASSES . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label varchar(191) DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (label)
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		// Insert default school if there is no school.
		$schools_count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . WLSM_SCHOOLS );
		if ( ! $schools_count ) {
			$default_school_id = self::insert_default_school();
		}

		// Insert default classes if there is no class.
		$classes_count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . WLSM_CLASSES );
		if ( ! $classes_count ) {
			self::insert_default_classes();
		}

		/* Create class_school table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_CLASS_SCHOOL . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				class_id bigint(20) UNSIGNED DEFAULT NULL,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				default_section_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (class_id, school_id),
				INDEX (class_id),
				INDEX (school_id),
				FOREIGN KEY (class_id) REFERENCES " . WLSM_CLASSES . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create sessions table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_SESSIONS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label varchar(191) DEFAULT NULL,
				start_date date NULL DEFAULT NULL,
				end_date date NULL DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (label, start_date, end_date)
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		$session_id     = NULL;
		$sessions_count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . WLSM_SESSIONS );
		if ( ! $sessions_count ) {
			/* Insert Default Session */
			$session_years = 1;
			$current_session_exists = false;
			for ( $i = 0; $i <= $session_years; $i++ ) {
				$current_year = absint( date('Y') ) + $i;
				$next_year    = $current_year + 1;
				$start_date   = $current_year . '-4-1';
				$end_date     = $next_year . '-3-31';

				$data = array(
					'label'      => $current_year . '-' . $next_year,
					'start_date' => $start_date,
					'end_date'   => $end_date,
				);

				$wpdb->insert( WLSM_SESSIONS, $data );

				if ( ! $current_session_exists ) {
					$session_id = $wpdb->insert_id;

					$current_session_exists = true;
				}
			}
		}

		/* Create inquiries table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_INQUIRIES . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(60) DEFAULT NULL,
				phone varchar(40) DEFAULT NULL,
				email varchar(60) DEFAULT NULL,
				message text DEFAULT NULL,
				note text DEFAULT NULL,
				next_follow_up date NULL DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				class_school_id bigint(20) UNSIGNED DEFAULT NULL,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (class_school_id),
				INDEX (school_id),
				FOREIGN KEY (class_school_id) REFERENCES " . WLSM_CLASS_SCHOOL . " (ID) ON DELETE SET NULL,
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		$row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '" . WLSM_INQUIRIES . "' AND COLUMN_NAME = 'last_name'");
		if (empty($row)) {
			$wpdb->query("ALTER TABLE " . WLSM_INQUIRIES . " ADD last_name varchar(60) DEFAULT NULL");
		}

		/* Create roles table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_ROLES . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(60) NOT NULL,
				permissions text NOT NULL,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (name, school_id),
				INDEX (school_id),
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create staff table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_STAFF . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				role varchar(40) NOT NULL,
				permissions text NOT NULL,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				user_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (school_id, user_id),
				INDEX (school_id),
				INDEX (user_id),
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (user_id) REFERENCES " . WLSM_USERS . " (ID) ON DELETE SET NULL
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create admins table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_ADMINS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(60) DEFAULT NULL,
				gender varchar(10) DEFAULT NULL,
				dob date NULL DEFAULT NULL,
				phone varchar(40) DEFAULT NULL,
				email varchar(60) DEFAULT NULL,
				address text DEFAULT NULL,
				salary decimal(12,2) UNSIGNED DEFAULT NULL,
				designation varchar(80) DEFAULT NULL,
				joining_date date NULL DEFAULT NULL,
				role_id bigint(20) UNSIGNED DEFAULT NULL,
				staff_id bigint(20) UNSIGNED DEFAULT NULL,
				assigned_by_manager tinyint(1) NOT NULL DEFAULT '0',
				is_active tinyint(1) NOT NULL DEFAULT '1',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (staff_id),
				FOREIGN KEY (role_id) REFERENCES " . WLSM_ROLES . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (staff_id) REFERENCES " . WLSM_STAFF . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create sections table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_SECTIONS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label varchar(191) DEFAULT NULL,
				class_school_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (label, class_school_id),
				INDEX (class_school_id),
				FOREIGN KEY (class_school_id) REFERENCES " . WLSM_CLASS_SCHOOL . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create student_records table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_STUDENT_RECORDS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				enrollment_number varchar(60) DEFAULT NULL,
				admission_number varchar(60) DEFAULT NULL,
				name varchar(60) DEFAULT NULL,
				gender varchar(10) DEFAULT NULL,
				dob date NULL DEFAULT NULL,
				phone varchar(40) DEFAULT NULL,
				email varchar(60) DEFAULT NULL,
				address text DEFAULT NULL,
				admission_date date NULL DEFAULT NULL,
				religion varchar(40) DEFAULT NULL,
				caste varchar(40) DEFAULT NULL,
				blood_group varchar(5) DEFAULT NULL,
				father_name varchar(60) DEFAULT NULL,
				mother_name varchar(60) DEFAULT NULL,
				father_phone varchar(40) DEFAULT NULL,
				mother_phone varchar(40) DEFAULT NULL,
				father_occupation varchar(60) DEFAULT NULL,
				mother_occupation varchar(60) DEFAULT NULL,
				roll_number varchar(30) DEFAULT NULL,
				photo_id bigint(20) UNSIGNED DEFAULT NULL,
				section_id bigint(20) UNSIGNED DEFAULT NULL,
				session_id bigint(20) UNSIGNED DEFAULT NULL,
				user_id bigint(20) UNSIGNED DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (user_id),
				INDEX (section_id),
				INDEX (session_id),
				INDEX (user_id),
				FOREIGN KEY (section_id) REFERENCES " . WLSM_SECTIONS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (session_id) REFERENCES " . WLSM_SESSIONS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (user_id) REFERENCES " . WLSM_USERS . " (ID) ON DELETE SET NULL,
				FOREIGN KEY (photo_id) REFERENCES " . WLSM_POSTS . " (ID) ON DELETE SET NULL
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create promotions table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_PROMOTIONS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				from_student_record bigint(20) UNSIGNED DEFAULT NULL,
				to_student_record bigint(20) UNSIGNED DEFAULT NULL,
				note varchar(255) DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (from_student_record),
				INDEX (to_student_record),
				FOREIGN KEY (from_student_record) REFERENCES " . WLSM_STUDENT_RECORDS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (to_student_record) REFERENCES " . WLSM_STUDENT_RECORDS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create invoices table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_INVOICES . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				invoice_number varchar(60) DEFAULT NULL,
				label varchar(100) DEFAULT NULL,
				description varchar(255) DEFAULT NULL,
				amount decimal(12,2) UNSIGNED DEFAULT '0.00',
				discount decimal(12,2) UNSIGNED DEFAULT '0.00',
				date_issued date NULL DEFAULT NULL,
				due_date date NULL DEFAULT NULL,
				partial_payment tinyint(1) NOT NULL DEFAULT '0',
				status varchar(15) DEFAULT 'unpaid',
				student_record_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (student_record_id),
				FOREIGN KEY (student_record_id) REFERENCES " . WLSM_STUDENT_RECORDS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create payments table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_PAYMENTS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				receipt_number varchar(60) DEFAULT NULL,
				amount decimal(12,2) UNSIGNED DEFAULT '0.00',
				payment_method varchar(50) DEFAULT NULL,
				transaction_id varchar(80) DEFAULT NULL,
				note text DEFAULT NULL,
				invoice_label varchar(100) DEFAULT NULL,
				invoice_payable decimal(12,2) UNSIGNED DEFAULT '0.00',
				invoice_id bigint(20) UNSIGNED DEFAULT NULL,
				student_record_id bigint(20) UNSIGNED DEFAULT NULL,
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (invoice_id),
				INDEX (school_id),
				FOREIGN KEY (invoice_id) REFERENCES " . WLSM_INVOICES . " (ID) ON DELETE SET NULL,
				FOREIGN KEY (student_record_id) REFERENCES " . WLSM_STUDENT_RECORDS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create subjects table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_SUBJECTS . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label varchar(100) DEFAULT NULL,
				code varchar(40) DEFAULT NULL,
				type varchar(40) DEFAULT NULL,
				class_school_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (label, class_school_id),
				UNIQUE (code, class_school_id),
				INDEX (class_school_id),
				FOREIGN KEY (class_school_id) REFERENCES " . WLSM_CLASS_SCHOOL . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create notices table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_NOTICES . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title text DEFAULT NULL,
				attachment bigint(20) UNSIGNED DEFAULT NULL,
				url text DEFAULT NULL,
				link_to varchar(15) DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				school_id bigint(20) UNSIGNED DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				INDEX (school_id),
				INDEX (added_by),
				FOREIGN KEY (attachment) REFERENCES " . WLSM_POSTS . " (ID) ON DELETE SET NULL,
				FOREIGN KEY (school_id) REFERENCES " . WLSM_SCHOOLS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (added_by) REFERENCES " . WLSM_USERS . " (ID) ON DELETE SET NULL
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		/* Create admin_subject table */
		$sql = "CREATE TABLE IF NOT EXISTS " . WLSM_ADMIN_SUBJECT . " (
				ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				admin_id bigint(20) UNSIGNED DEFAULT NULL,
				subject_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (ID),
				UNIQUE (admin_id, subject_id),
				INDEX (admin_id),
				INDEX (subject_id),
				FOREIGN KEY (admin_id) REFERENCES " . WLSM_ADMINS . " (ID) ON DELETE CASCADE,
				FOREIGN KEY (subject_id) REFERENCES " . WLSM_SUBJECTS . " (ID) ON DELETE CASCADE
				) ENGINE=InnoDB " . $charset_collate;
		dbDelta( $sql );

		self::set_default_options( $session_id );

		// Set default school for super admin.
		if ( isset( $default_school_id ) ) {
			$user_id = get_current_user_id();

			// Data to update or insert.
			$data = array(
				'school_id' => $default_school_id,
				'user_id'   => $user_id,
				'role'      => WLSM_M_Role::get_admin_key(),
			);

			$wpdb->insert( WLSM_STAFF, $data );

			update_user_meta( $user_id, 'wlsm_school_id', $default_school_id );
		}
	}

	public static function uninstall() {
		if ( get_option( 'wlsm_delete_on_uninstall' ) ) {
			// Drop all tables and delete options.
			self::remove_data();
		}
	}

	private static function insert_default_school() {
		global $wpdb;

		$default_school_data = array(
			'label' => esc_html__( 'Default School', 'school-management-system' ),
		);

		$wpdb->insert( WLSM_SCHOOLS, $default_school_data );

		$default_school_id = $wpdb->insert_id;

		return $default_school_id;
	}

	private static function insert_default_classes() {
		global $wpdb;

		$sql = "INSERT INTO `" . WLSM_CLASSES . "` (`label`) VALUES ('1st'),('2nd'),('3rd'),('4th'),('5th'),('6th'),('7th'),('8th'),('9th'),('10th'),('11th'),('12th');";
		$wpdb->query( $sql );
	}

	public static function set_default_options( $session_id = NULL ) {
		$current_session = get_option( 'wlsm_current_session' );
		if ( ! $current_session && $session_id ) {
			add_option( 'wlsm_current_session', $session_id );
		}

		$currency = get_option( 'wlsm_currency' );
		if ( ! $currency ) {
			add_option( 'wlsm_currency', WLSM_Config::get_default_currency() );
		}

		$date_format = get_option( 'wlsm_date_format' );
		if ( ! $date_format ) {
			add_option( 'wlsm_date_format', WLSM_Config::get_default_date_format() );
		}
	}

	public static function remove_data() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_ADMIN_SUBJECT );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_NOTICES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_SUBJECTS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_PAYMENTS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_INVOICES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_PROMOTIONS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_STUDENT_RECORDS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_SECTIONS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_ADMINS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_STAFF );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_ROLES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_INQUIRIES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_SESSIONS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_CLASS_SCHOOL );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_CLASSES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_SETTINGS );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . WLSM_SCHOOLS );

		delete_metadata( 'user', 0, 'wlsm_school_id', '', true );
		delete_metadata( 'user', 0, 'wlsm_current_session', '', true );

		delete_option( 'wlsm_current_session' );
		delete_option( 'wlsm_date_format' );
		delete_option( 'wlsm_currency' );
		delete_option( 'wlsm_gdpr_enable' );

		delete_option( 'wlsm_delete_on_uninstall' );
	}
}
