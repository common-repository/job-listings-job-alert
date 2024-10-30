<?php
/**
 * functions.php
 *
 * @package:
 * @since  : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function job_alert_edit_url( $job_alert_id = '' ) {
	$job_alert_id = ! empty( $job_alert_id ) ? $job_alert_id : get_the_ID();

	return esc_url_raw( add_query_arg( 'job_alert_id', $job_alert_id, JLT_Member::get_endpoint_url( 'edit-job-alert' ) ) );
}

function jlt_job_alert_delete_url() {
	return wp_nonce_url( add_query_arg( array(
		'action'       => 'delete_job_alert',
		'job_alert_id' => get_the_ID(),
	) ), 'edit-job-alert' );
}

function jlt_job_alert_new_url() {
	return JLT_Member::get_endpoint_url( 'add-job-alert' );
}

function jlt_job_alert_is_owner( $user_id = 0, $job_alert_id = 0 ) {

	$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

	if ( empty( $user_id ) || empty( $job_alert_id ) ) {
		return false;
	}
	$candidate_id = get_post_field( 'post_author', $job_alert_id );

	return $candidate_id == $user_id;
}

function jlt_job_alert_list_frequency() {
	return JLT_Job_Alert::get_frequency();
}

function jlt_job_alert_get_frequency( $job_alert_id = '' ) {

	$job_alert_id = ! empty( $job_alert_id ) ? $job_alert_id : get_the_ID();

	$frequency_arr = jlt_job_alert_list_frequency();

	$frequency = jlt_get_post_meta( $job_alert_id, '_frequency' );

	return $frequency && isset( $frequency_arr[ $frequency ] ) ? $frequency_arr[ $frequency ] : '';
}