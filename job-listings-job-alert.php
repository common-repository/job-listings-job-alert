<?php

/**
 * Plugin Name: Job Listings Job Alert
 * Plugin URI:        https://nootheme.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.1.0
 * Author:            NooTheme
 * Author URI:        https://nootheme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       job-listings-job-alert
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JLT_Job_Alert {

	public function __construct() {

		define( 'JLT_JOB_ALERT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'JLT_JOB_ALERT_PLUGIN_TEMPLATE_DIR', JLT_JOB_ALERT_PLUGIN_DIR . 'templates/' );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		$this->includes();

		if ( self::enable_job_alert() ) {
			add_action( 'init', array( $this, 'register_post_type' ), 20 );
			add_action( 'jlt_job_alert_notify', array( $this, 'notify' ) );
		}

		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'admin_init' ) );

			add_filter( 'jlt_admin_settings_tabs_array', array( &$this, 'add_seting_job_alert_tab' ), 20 );
			add_action( 'jlt_admin_setting_job_alert', array( &$this, 'setting_page' ) );
		}
	}

	public function includes() {
		require JLT_JOB_ALERT_PLUGIN_DIR . 'includes/loader.php';
	}

	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'job-listings-job-alert' );

		load_textdomain( 'job-listings-job-alert', WP_LANG_DIR . "/job-listings-job-alert/job-listings-job-alert-$locale.mo" );
		load_plugin_textdomain( 'job-listings-job-alert', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	public static function set_alert_schedule( $job_alert_id = null, $frequency = '' ) {
		if ( ! self::enable_job_alert() ) {
			return;
		}

		if ( empty( $job_alert_id ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'jlt_job_alert_notify', array( $job_alert_id ) );

		$alert = get_post( $job_alert_id );

		if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'job_alert' ) {
			return;
		}

		// Update the schedule time
		update_post_meta( $alert->ID, '_start_schedule_time', time() );

		// Reschedule next alert
		$frequency = empty( $frequency ) ? jlt_get_post_meta( $alert->ID, '_frequency', 'weekly' ) : $frequency;
		switch ( $frequency ) {
			case 'daily' :
				$next = strtotime( '+1 day' );
				break;
			case 'hourly' :
				$next = strtotime( '+1 hour' );
				break;
			case 'weekly' :
				$next = strtotime( '+1 week' );
				break;
			case 'fortnight' :
				$next = strtotime( '+1 fortnight' );
				break;
			case 'monthly' :
				$next = strtotime( '+1 month' );
				break;
			default:
				$next = strtotime( '+1 week' );
		}

		// Create cron
		return wp_schedule_single_event( $next, 'jlt_job_alert_notify', array( $alert->ID ) );
	}

	public static function enable_job_alert() {
		return self::get_setting( 'enable_job_alert', 'yes' ) == 'yes';
	}

	public static function get_setting( $id = null, $default = null ) {
		global $job_alert_setting;
		if ( ! isset( $job_alert_setting ) || empty( $job_alert_setting ) ) {
			$job_alert_setting = get_option( 'jlt_job_alert' );
		}
		if ( isset( $job_alert_setting[ $id ] ) ) {
			return $job_alert_setting[ $id ];
		}

		return $default;
	}

	public function register_post_type() {
		register_post_type( 'jlt_job_alert', array(
			'public'              => false,
			'show_ui'             => false,
			'capability_type'     => 'post',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => false,
			'has_archive'         => false,
			'show_in_nav_menus'   => false,
		) );
	}

	public function admin_init() {
		register_setting( 'jlt_job_alert', 'jlt_job_alert' );
	}

	public function add_seting_job_alert_tab( $tabs ) {
		$tabs[ 'job_alert' ] = __( 'Job Alert', 'job-listings-job-alert' );

		return $tabs;
	}

	public function setting_page() {
		if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) {
			flush_rewrite_rules();
		}
		?>
		<?php settings_fields( 'jlt_job_alert' ); ?>
		<h3><?php echo __( 'Job Alert Options', 'job-listings-job-alert' ) ?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
			<tr>
				<th>
					<?php esc_html_e( 'Enable Job Alert', 'job-listings-job-alert' ) ?>
				</th>
				<td>
					<?php
					$enable_job_alert = self::get_setting( 'enable_job_alert', 'yes' );
					?>
					<input type="hidden" name="jlt_job_alert[enable_job_alert]" value="no">
					<input type="checkbox" name="jlt_job_alert[enable_job_alert]"
					       value="yes" <?php checked( $enable_job_alert, 'yes' ) ?>>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Max Jobs for each Email', 'job-listings-job-alert' ) ?>
				</th>
				<td>
					<?php
					$max_job_count_email = self::get_setting( 'max_job_count_email', 5 );
					?>
					<input type="text" name="jlt_job_alert[max_job_count_email]"
					       value="<?php echo( $max_job_count_email ? $max_job_count_email : '5' ) ?>">
					<p>
						<small><?php echo __( 'The maximum number of jobs included in each email. It helps make sure the email has reasonable length. If there are more jobs, a read more link will be added to the end of email.', 'job-listings-job-alert' ); ?></small>
					</p>
				</td>
			</tr>
			<?php do_action( 'jlt_setting_job_alert_fields' ); ?>
			</tbody>
		</table>
		<?php
	}

	public function notify( $alert_id ) {
		$alert = get_post( $alert_id );

		if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'job_alert' ) {
			return;
		}

		$user = get_user_by( 'id', $alert->post_author );
		$jobs = $this->_get_alert_jobs( $alert );

		if ( $jobs && $jobs->found_posts > 0 ) {
			$site_name = get_bloginfo( 'name' );

			$email   = $this->_format_email( $alert, $user, $jobs );
			$subject = sprintf( __( '%d+ New Jobs - Job Alert from %s', 'job-listings-job-alert' ), $jobs->found_posts, $site_name );
			$subject = apply_filters( 'jlt_job_alert_email_subject', $subject, $alert, $jobs );

			if ( $email ) {
				jlt_mail( $user->user_email, $subject, $email, array(), 'jlt_notify_job_alert_candidate' );
			}

			// Count
			update_post_meta( $alert->ID, '_notify_count', 1 + absint( jlt_get_post_meta( $alert->ID, '_notify_count', 0 ) ) );
		}

		self::set_alert_schedule( $alert->ID );
	}

	public function _get_alert_jobs( $alert ) {
		global $wpdb;

		$alert_id = $alert->ID;

		$post__in = array();
		// $meta_query = array('relation' => 'AND');
		$tax_query  = array( 'relation' => 'AND' );
		$date_query = array();

		$keywords        = jlt_get_post_meta( $alert_id, '_keywords', '' );
		$search_keywords = array_map( 'trim', explode( ',', $keywords ) );
		$keywords_where  = array();

		if ( ! empty( $search_keywords ) && count( $search_keywords ) ) :
			foreach ( $search_keywords as $keyword ) {
				$keywords_where[] = 'post_title LIKE \'%' . esc_sql( $keyword ) . '%\' OR post_content LIKE \'%' . esc_sql( $keyword ) . '%\'';
			}

			$where    = implode( ' OR ', $keywords_where );
			$post__in = array_merge( $wpdb->get_col( "
				    SELECT DISTINCT ID FROM {$wpdb->posts}
				    WHERE ( {$where} )
				    AND post_type = 'job'
				    AND post_status = 'publish'" ), array( 0 ) ); // add 0 value to make sure there's no result if no job matchs keywords

		endif;

		$location = jlt_get_post_meta( $alert_id, '_job_location', '' );
		$location = jlt_json_decode( $location );
		if ( ! empty( $location ) ) {
			$location_query = array(
				'taxonomy' => 'job_location',
				'field'    => 'id',
				'terms'    => $location,
				'compare'  => 'IN',
			);
			$tax_query[]    = $location_query;
		}

		$category = jlt_get_post_meta( $alert_id, '_job_category', '' );
		$category = jlt_json_decode( $category );
		if ( ! empty( $category ) ) {
			$category_query = array(
				'taxonomy' => 'job_category',
				'field'    => 'id',
				'terms'    => $category,
				'compare'  => 'IN',
			);
			$tax_query[]    = $category_query;
		}

		$type = jlt_get_post_meta( $alert_id, '_job_type', '' );
		if ( ! empty( $type ) ) {
			$type_query  = array(
				'taxonomy' => 'job_type',
				'field'    => 'id',
				'terms'    => $type,
			);
			$tax_query[] = $type_query;
		}

		$last_schedule_time = jlt_get_post_meta( $alert_id, '_start_schedule_time', '' );
		if ( ! empty( $last_schedule_time ) ) {
			$date_query[ 'after' ] = get_date_from_gmt( date( 'Y-m-d H:i:s', absint( $last_schedule_time ) ), 'Y-m-d H:i:s' );
		} else {
			$frequency = jlt_get_post_meta( $alert_id, '_frequency', '' );
			switch ( $frequency ) {
				case 'monthly':
					$date_query[ 'after' ] = '-1 month';
					break;
				case 'fortnight':
					$date_query[ 'after' ] = '-1 fortnight';
					break;
				case 'daily':
					$date_query[ 'after' ] = '-1 day';
					break;
				case 'hourly':
					$date_query[ 'after' ] = '-1 hour';
					break;
				default: // weekly
					$date_query[ 'after' ] = '-1 week';
					break;
			}
		}

		$args = array(
			'post_type'      => 'job',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'nopaging'       => true,
			'post__in'       => $post__in,
			// 'meta_query'    => $meta_query,
			'tax_query'      => $tax_query,
			'date_query'     => $date_query,
		);

		do_action( 'jlt_before_get_job_alert', $args );

		$result = new WP_Query( $args );

		do_action( 'jlt_after_get_job_alert', $args );

		return $result;
	}

	private function _format_email( $alert, $user, $jobs ) {
		$max_alert_job_count = self::get_setting( 'max_job_count_email', 5 );
		$site_name           = get_bloginfo( 'name' );

		$message = sprintf( __( 'Dear %s,', 'job-listings-job-alert' ), $user->display_name ) . '<br/><br/>';
		$message .= sprintf( __( 'We found %d new jobs that match your criteria.', 'job-listings-job-alert' ), $jobs->found_posts ) . '<br/><br/>';

		if ( $jobs && $jobs->have_posts() ) {
			$count = 0;
			while ( $jobs->have_posts() && $count <= $max_alert_job_count ) :
				$jobs->the_post();
				global $post;
				$count ++;
				$locations  = wp_get_post_terms( $post->ID, 'job_location', array( 'fields' => 'names' ) );
				$categories = wp_get_post_terms( $post->ID, 'job_category', array( 'fields' => 'names' ) );
				$types      = wp_get_post_terms( $post->ID, 'job_type', array( 'fields' => 'names' ) );

				$message .= sprintf( __( '%s: <a href="%s">%s</a>', 'job-listings-job-alert' ), get_the_title( $post ), get_permalink( $post->ID ), get_permalink( $post->ID ) ) . '<br/>';
				$message .= sprintf( __( '** Location: %s', 'job-listings-job-alert' ), implode( ', ', $locations ) ) . '<br/>';
				$message .= sprintf( __( '** Job Category: %s', 'job-listings-job-alert' ), implode( ', ', $categories ) ) . '<br/>';
				$message .= sprintf( __( '** Job Type: %s', 'job-listings-job-alert' ), implode( ', ', $types ) ) . '<br/>';
				$message .= __( '------', 'job-listings-job-alert' ) . '<br/>';

			endwhile;

			if ( $jobs->found_posts > $max_alert_job_count ) {
				$message .= sprintf( __( 'View more jobs: %s', 'job-listings-job-alert' ), get_home_url() ) . '<br/>';
			}
		}

		$message .= '<br/>' . __( 'Best regards,', 'job-listings-job-alert' ) . '<br/>';
		$message .= $site_name;

		return apply_filters( 'jlt_job_alerts_email_content', $message );
	}

	public static function get_frequency() {
		$frequency = array(
			'daily'     => __( 'Daily', 'job-listings-job-alert' ),
			'weekly'    => __( 'Weekly', 'job-listings-job-alert' ),
			'fortnight' => __( 'Fortnightly', 'job-listings-job-alert' ),
			'monthly'   => __( 'Monthly', 'job-listings-job-alert' ),
		);

		return apply_filters( 'get_frequency', $frequency );
	}
}

function run_job_listings_job_alert() {
	new JLT_Job_Alert();
}

add_action( 'job_listings_loaded', 'run_job_listings_job_alert' );