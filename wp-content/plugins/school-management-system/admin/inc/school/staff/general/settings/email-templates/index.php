<?php
defined( 'ABSPATH' ) || die();
?>
<div class="tab-pane fade" id="wlsm-school-email-templates" role="tabpanel" aria-labelledby="wlsm-school-email-templates-tab">

	<div class="row">
		<div class="col-md-12">
			<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-school-email-templates-settings-form">
				<?php
				$nonce_action = 'save-school-email-templates-settings';
				$nonce        = wp_create_nonce( $nonce_action );
				?>
				<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

				<input type="hidden" name="action" value="wlsm-save-school-email-templates-settings">

				<?php
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-templates/student_admission.php';
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-templates/invoice_generated.php';
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-templates/online_fee_submission.php';
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/settings/email-templates/offline_fee_submission.php';
				?>

				<div class="mt-2">
					<span class="float-right">
						<button type="submit" class="btn btn-primary" id="wlsm-save-school-email-templates-settings-btn">
							<i class="fas fa-save"></i>&nbsp;
							<?php esc_html_e( 'Save', 'school-management-system' ); ?>
						</button>
					</span>
				</div>
			</form>
		</div>
	</div>

</div>
