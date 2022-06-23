<?php
defined( 'ABSPATH' ) || die();

class WLSM_Login_Widget extends WP_Widget {
	public function __construct() {
		$widget_options = array(
			'classname'   => 'wlsm_login_widget',
			'description' => esc_html__( 'Display login form for students.', 'school-management-system' ),
		);
		parent::__construct( 'wlsm_login_widget', esc_html__( 'Student Login Form', 'school-management-system' ), $widget_options );
	}

	public function widget( $args, $instance ) {
		global $wp;

		WLSM_Shortcode::enqueue_assets();
		$title               = apply_filters( 'widget_title', $instance['title'] );
		$login_redirect_url  = $instance['login_redirect_url'];
		$logout_redirect_url = $instance['logout_redirect_url'];

		$nonce_action = 'wlsm-login-user';

		echo wp_kses_post( $args['before_widget'] . $args['before_title'] . $title . $args['after_title'] );

		if ( ! is_user_logged_in() ) {
			$login_form_args = array(
				'form_id'        => 'wlsm-login-via-widget-form',
				'id_username'    => 'wlsm-login-via-widget-username',
				'id_password'    => 'wlsm-login-via-widget-password',
				'id_remember'    => 'wlsm-login-via-widget-remember',
				'id_submit'      => 'wlsm-login-via-widget-submit',
				'value_username' => '',
			);

			if ( $login_redirect_url ) {
				$login_form_args['redirect'] = $login_redirect_url;
			}

			wp_login_form( $login_form_args );
			?>
			<a target="_blank" href="<?php echo esc_url( wp_lostpassword_url( home_url( add_query_arg( array(), $wp->request ) ) ) ); ?>">
				<?php esc_html_e( 'Lost your password?', 'school-management-system' ); ?>
			</a>
			<?php
		} else {
			if ( $logout_redirect_url ) {
				$logout_url = wp_logout_url( $logout_redirect_url );
			} else {
				$logout_url = wp_logout_url( home_url( add_query_arg( array(), $wp->request ) ) );
			}
			esc_html_e( 'You are logged in.', 'school-management-system' );
			?>
			<a href="<?php echo esc_url( $logout_url ); ?>">
				<?php esc_html_e( 'Logout', 'school-management-system' ); ?>
			</a>
		<?php
		}
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$fields = array(
			'title'               => esc_html__( 'Student Login', 'school-management-system' ),
			'login_redirect_url'  => '',
			'logout_redirect_url' => '',
		);

		$instance = wp_parse_args( (array) $instance, $fields );

		$title               = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$login_redirect_url  = ! empty( $instance['login_redirect_url'] ) ? $instance['login_redirect_url'] : '';
		$logout_redirect_url = ! empty( $instance['logout_redirect_url'] ) ? $instance['logout_redirect_url'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>"><?php esc_html_e( 'Enter URL to redirect user after login', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'login_redirect_url' ) ); ?>" value="<?php echo esc_url( $login_redirect_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>"><?php esc_html_e( 'Enter URL to redirect user after logout', 'school-management-system' ); ?>:</label><br>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logout_redirect_url' ) ); ?>" value="<?php echo esc_url( $logout_redirect_url ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                        = $old_instance;
		$instance['title']               = strip_tags( $new_instance['title'] );
		$instance['login_redirect_url']  = strip_tags( $new_instance['login_redirect_url'] );
		$instance['logout_redirect_url'] = strip_tags( $new_instance['logout_redirect_url'] );

		return $instance;
	}
}
