<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/partials/navigation.php';

$student_name = WLSM_M_Staff_Class::get_name_text($student->student_name);

$notices = WLSM_M_Staff_Class::get_school_notices($school_id, 7);
?>
<div class="wlsm-content-area wlsm-section-dashboard wlsm-student-dashboard">
	<div class="wlsm-st-main-title">
		<span>
			<?php
			/* translators: %s: student name */
			printf(
				wp_kses(
					'Welcome <span class="wlsm-font-bold">%s</span>!',
					array('span' => array('class' => array()))
				),
				esc_html($student_name)
			);
			?>
		</span>
	</div>

	<div class="wlsm-flex-between">
		<div class="wlsm-flex-item wlsm-l-w-50 wlsm-mt-2">
			<div class="wlsm-st-details-heading">
				<span><?php esc_html_e('Noticeboard', 'school-management-system'); ?></span>
			</div>
			<div class="wlsm-st-recent-notices-section" id="wlsm_notice">
				<?php
				if (count($notices)) {
					$today = new DateTime();
					$today->setTime(0, 0, 0);
				?>
					<ul class="wlsm-st-recent-notices">
						<?php
						foreach ($notices as $key => $notice) {
							$link_to = $notice->link_to;
							$link    = '#';

							if ('url' === $link_to) {
								if (!empty($notice->url)) {
									$link = $notice->url;
								}
							} else if ('attachment' === $link_to) {
								if (!empty($notice->attachment)) {
									$attachment = $notice->attachment;
									$link       = wp_get_attachment_url($attachment);
								}
							} else {
								$link = '#';
							}

							$notice_date = DateTime::createFromFormat('Y-m-d H:i:s', $notice->created_at);
							$notice_date->setTime(0, 0, 0);

							$interval = $today->diff($notice_date);
						?>
							<li>
								<span>
									<a  href="#" class="wlsm_notice_link" data-id="<?php echo esc_attr($notice->ID) ?>"><?php echo esc_html(stripslashes($notice->title)); ?> <span class="wlsm-st-notice-date wlsm-font-bold"><?php echo esc_html(WLSM_Config::get_date_text($notice->created_at)); ?></span></a>

									<?php if ($interval->days < 7) { ?>
										<img class="wlsm-st-notice-new" src="<?php echo esc_url(WLSM_PLUGIN_URL . 'assets/images/newicon.gif'); ?>" alt="notice">
									<?php } ?>
								</span>
							</li>
							<!-- The Modal -->
							<div id="notice_modal" class="modal">

								<!-- Modal content -->
								<div class="modal-content">
									<iframe width="1100" height="600" src="<?php echo esc_url($link); ?>" allowfullscreen title="notice modal">
									</iframe>
								</div>

							</div>

						<?php
						}
						?>
					</ul>
				<?php
				} else {
				?>
					<div>
						<span class="wlsm-font-medium wlsm-font-bold">
							<?php esc_html_e('There is no notice.', 'school-management-system'); ?>
						</span>
					</div>
				<?php
				}
				?>
			</div>
		</div>


		<div class="wlsm-flex-item wlsm-l-w-48 wlsm-mt-2">
			<div class="wlsm-st-details">
				<div class="wlsm-st-details-heading">
					<span><?php esc_html_e('Your Details', 'school-management-system'); ?></span>
				</div>
				<ul class="wlsm-st-details-list">
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Name'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html($student_name); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Enrollment Number', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html($student->enrollment_number); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Session', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Session::get_label_text($student->session_label)); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Class', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Class::get_label_text($student->class_label)); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Section', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Class::get_label_text($student->section_label)); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Roll Number', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Staff_Class::get_roll_no_text($student->roll_number)); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('Father Name', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Staff_Class::get_name_text($student->father_name)); ?></span>
					</li>
					<li>
						<span class="wlsm-st-details-list-key"><?php esc_html_e('ID Card', 'school-management-system'); ?>:</span>
						<span class="wlsm-st-details-list-value">
							<a class="wlsm-st-print-id-card" data-id-card="<?php echo esc_attr($user_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('st-print-id-card-' . $user_id)); ?>" href="#" data-message-title="<?php echo esc_attr__('Print ID Card', 'school-management-system'); ?>">
								<span class="dashicons dashicons-search"></span>
							</a>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
