<?php
/**
 * template-functions.php
 *
 * @package:
 * @since  : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Job Alert Endpoint
 */

function jlt_job_alert_endpoint_define() {
	$endpoints   = [ ];
	$endpoints[] = array(
		'key'          => 'job-alert',
		'value'        => jlt_get_endpoints_setting( 'job-alert', 'job-alert' ),
		'text'         => __( 'Job Alert', 'job-listings-job-alert' ),
		'order'        => 20,
		'show_in_menu' => true,
	);
	$endpoints[] = array(
		'key'          => 'edit-job-alert',
		'value'        => jlt_get_endpoints_setting( 'edit-job-alert', 'edit-job-alert' ),
		'text'         => __( 'Edit Job Alert', 'job-listings-job-alert' ),
		'order'        => 20,
		'show_in_menu' => false,
	);
	$endpoints[] = array(
		'key'          => 'add-job-alert',
		'value'        => jlt_get_endpoints_setting( 'add-job-alert', 'add-job-alert' ),
		'text'         => __( 'Add Job Alert', 'job-listings-job-alert' ),
		'order'        => 20,
		'show_in_menu' => false,
	);

	return $endpoints;
}

function jlt_job_alert_add_endpoints() {
	foreach ( jlt_job_alert_endpoint_define() as $endpoint ) {
		add_rewrite_endpoint( $endpoint[ 'value' ], EP_ROOT | EP_PAGES );
	}
	flush_rewrite_rules();
}

add_action( 'init', 'jlt_job_alert_add_endpoints' );

function jlt_job_alert_endpoint( $endpoints ) {

	$endpoints = array_merge( $endpoints, jlt_job_alert_endpoint_define() );

	return $endpoints;
}

add_filter( 'jlt_list_endpoints_candidate', 'jlt_job_alert_endpoint' );

function jlt_job_alert_manage() {

	if ( ! JLT_Job_Alert::enable_job_alert() ) {
		return;
	}

	$paged        = jlt_member_get_paged();
	$current_user = wp_get_current_user();

	$args      = array(
		'post_type'   => 'job_alert',
		'paged'       => $paged,
		'post_status' => array( 'publish', 'pending' ),
		'author'      => $current_user->ID,
	);
	$list_jobs = new WP_Query( $args );
	$array     = array(
		'list_jobs'    => $list_jobs,
		'current_user' => $current_user,
	);

	jlt_get_template( 'member/manage-job-alert.php', $array, '', JLT_JOB_ALERT_PLUGIN_TEMPLATE_DIR );
	wp_reset_query();
}

add_action( 'jlt_account_job-alert_endpoint', 'jlt_job_alert_manage' );

function jlt_job_alert_edit() {

	if ( ! JLT_Job_Alert::enable_job_alert() ) {
		return;
	}

	$is_edit      = isset( $_GET[ 'job_alert_id' ] ) && is_numeric( $_GET[ 'job_alert_id' ] );
	$job_alert_id = $is_edit ? absint( $_GET[ 'job_alert_id' ] ) : 0;
	$job_alert    = $job_alert_id ? get_post( $job_alert_id ) : '';
	$array        = array(
		'is_edit'      => $is_edit,
		'job_alert_id' => $job_alert_id,
		'job_alert'    => $job_alert,
	);

	jlt_get_template( 'form/job-alert-form.php', $array, '', JLT_JOB_ALERT_PLUGIN_TEMPLATE_DIR );
}

add_action( 'jlt_account_add-job-alert_endpoint', 'jlt_job_alert_edit' );
add_action( 'jlt_account_edit-job-alert_endpoint', 'jlt_job_alert_edit' );

function jlt_job_alert_btn_list() {
	$args       = array(
		'keyword'  => ! empty( $_GET[ 'keyword' ] ) ? sanitize_text_field( $_GET[ 'keyword' ] ) : '',
		'category' => ! empty( $_GET[ 'category' ] ) ? $_GET[ 'category' ] : '',
	);
	$categories = implode( ',', $args[ 'category' ] );
	
	?>
	<button class="btn-add-to-list-job-alert"
	        data-keyword="<?php echo $args[ 'keyword' ]; ?>"
	        data-category="<?php echo $categories; ?>"><?php _e( 'Add list to job alert', 'job-listings-job-alert' ); ?></button>
	<?php
}

//add_action( 'jlt_before_job_loop_content', 'jlt_job_alert_btn_list' );