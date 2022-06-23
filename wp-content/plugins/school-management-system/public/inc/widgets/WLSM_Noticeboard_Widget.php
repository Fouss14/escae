<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

class WLSM_Noticeboard_Widget extends WP_Widget {
	public function __construct() {
		$widget_options = array(
			'classname'   => 'wlsm_noticeboard_widget',
			'description' => esc_html__( 'Display school notices.', 'school-management-system' ),
		);
		parent::__construct( 'wlsm_noticeboard_widget', esc_html__( 'School Noticeboard', 'school-management-system' ), $widget_options );
	}

	public function widget( $args, $instance ) {
		global $wp;

		WLSM_Shortcode::enqueue_assets();
		$title              = apply_filters( 'widget_title', $instance['title'] );
		$number_of_notices  = $instance['number_of_notices'];
		$animation_interval = $instance['animation_interval'];
		$max_height         = $instance['max_height'];
		$min_height         = $instance['min_height'];

		$school_id = 1;

		$notices = WLSM_M_Staff_Class::get_school_notices( $school_id, $number_of_notices );

		echo wp_kses_post( $args['before_widget'] . $args['before_title'] . $title . $args['after_title'] );
		?>
		<div class="wlsm-st-recent-notices-section" id="wlsm-noticeboard-widget">
			<?php
			if ( count( $notices ) ) {
				$css = '';
				if ( 8 !== $animation_interval ) {
					$css .= '.wlsm-st-recent-notices { animation-interval: ' . esc_attr( $animation_interval ) . 's; } ';
				}
				if ( 400 !== $max_height ) {
					$css .= '#wlsm-noticeboard-widget { max-height: ' . esc_attr( $max_height ) . 'px; } ';
				}
				if ( 100 !== $min_height ) {
					$css .= '#wlsm-noticeboard-widget { min-height: ' . esc_attr( $min_height ) . 'px; } ';
				}
				if ( ! empty( $css ) ) {
					wp_register_style( 'wlsm-noticeboard-widget', false );
					wp_enqueue_style( 'wlsm-noticeboard-widget' );
					wp_add_inline_style( 'wlsm-noticeboard-widget', $css );
				}

				$today = new DateTime();
				$today->setTime( 0, 0, 0 );
			?>
			<ul class="wlsm-st-recent-notices">
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
						<img class="wlsm-st-notice-new" src="<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/images/newicon.gif' ); ?>" alt="notice">
						<?php } ?>
					</span>
				</li>
				<?php
				}
			?>
			</ul>
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
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$fields = array(
			'title'              => esc_html__( 'Noticeboard', 'school-management-system' ),
			'number_of_notices'  => 6,
			'animation_interval' => 8,
			'max_height'         => 400,
			'min_height'         => 100,
		);

		$instance = wp_parse_args( (array) $instance, $fields );

		$title              = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$number_of_notices  = ! empty( $instance['number_of_notices'] ) ? $instance['number_of_notices'] : 6;
		$animation_interval = ! empty( $instance['animation_interval'] ) ? $instance['animation_interval'] : 8;
		$max_height         = ! empty( $instance['max_height'] ) ? $instance['max_height'] : 400;
		$min_height         = ! empty( $instance['min_height'] ) ? $instance['min_height'] : 100;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_notices' ) ); ?>"><?php esc_html_e( 'Number of Notices', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'number_of_notices' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_notices' ) ); ?>" value="<?php echo esc_attr( $number_of_notices ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'animation_interval' ) ); ?>"><?php esc_html_e( 'Animation Interval (in seconds)', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'animation_interval' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'animation_interval' ) ); ?>" value="<?php echo esc_attr( $animation_interval ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max_height' ) ); ?>"><?php esc_html_e( 'Maximum Height (in pixels)', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'max_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max_height' ) ); ?>" value="<?php echo esc_attr( $max_height ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'min_height' ) ); ?>"><?php esc_html_e( 'Minimum Height (in pixels)', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'min_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'min_height' ) ); ?>" value="<?php echo esc_attr( $min_height ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = strip_tags( $new_instance['title'] );
		$instance['number_of_notices']  = absint( $new_instance['number_of_notices'] );
		$instance['animation_interval'] = absint( $new_instance['animation_interval'] );
		$instance['max_height']         = absint( $new_instance['max_height'] );
		$instance['min_height']         = absint( $new_instance['min_height'] );

		return $instance;
	}
}
