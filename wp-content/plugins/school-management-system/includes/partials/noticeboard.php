<?php
defined( 'ABSPATH' ) || die();

global $wpdb;

$notices_per_page = 10;

$notices_query = 'SELECT n.ID, n.title, n.attachment, n.url, n.link_to, n.is_active, n.created_at FROM ' . WLSM_NOTICES . ' as n WHERE n.school_id = %d AND n.is_active = 1 GROUP BY n.ID';

$notices_total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM ({$notices_query}) AS combined_table", $school_id ) );

$notices_page = isset( $_GET['notices_page'] ) ? absint( $_GET['notices_page'] ) : 1;

$notices_page_offset = ( $notices_page * $notices_per_page ) - $notices_per_page;

$notices = $wpdb->get_results( $wpdb->prepare( $notices_query . ' ORDER BY n.ID DESC LIMIT %d, %d', $school_id, $notices_page_offset, $notices_per_page ) );
?>
<div class="wlsm-content-area wlsm-section-noticeboard wlsm-student-noticeboard">
	<div class="wlsm-st-main-title">
		<span><?php esc_html_e( 'Noticeboard', 'school-management-system' ); ?></span>
	</div>

	<div class="wlsm-st-notices-section">
		<?php
		if ( count( $notices ) ) {
			$today = new DateTime();
			$today->setTime( 0, 0, 0 );
		?>
		<ul class="wlst-st-list wlsm-st-notices">
			<?php
			foreach ( $notices as $key => $notice ) {
				$link_to = $notice->link_to;
				$link    = '#';

				if ( 'url' === $link_to ) {
					if ( ! empty ( $notice->url ) ) {
						$link = $notice->url;
					}
				} else if ( 'attachment' === $link_to ) {
					if ( ! empty ( $notice->attachment ) ) {
						$attachment = $notice->attachment;
						$link       = wp_get_attachment_url( $attachment );
					}
				} else {
					$link = '#';
				}

				$notice_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $notice->created_at );
				$notice_date->setTime( 0, 0, 0 );

				$interval = $today->diff( $notice_date );
			?>
			<li>
				<span>
					<a target="_blank" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( stripslashes( $notice->title ) ); ?> <span class="wlsm-st-notice-date wlsm-font-bold"><?php echo esc_html( WLSM_Config::get_date_text( $notice->created_at ) ); ?></span></a>
					<?php if ( $interval->days < 7 ) { ?>
					<img class="wlsm-st-notice-new" src="<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/images/newicon.gif' ); ?>">
					<?php } ?>
				</span>
			</li>
			<?php
			}
		?>
		</ul>
		<div class="wlsm-text-right wlsm-font-medium wlsm-font-bold wlsm-mt-2">
		<?php
		echo paginate_links(
			array(
				'base'      => add_query_arg( 'notices_page', '%#%' ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => ceil( $notices_total / $notices_per_page ),
				'current'   => $notices_page,
			)
		);
		?>
		</div>
		<?php
		} else {
		?>
		<div>
			<span class="wlsm-font-medium wlsm-font-bold">
				<?php esc_html_e( 'There is no notice.', 'school-management-system' ); ?>
			</span>
		</div>
		<?php
		}
		?>
	</div>
</div>
