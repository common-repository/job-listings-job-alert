<?php

class JLT_Job_Alert_Hander {
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'edit_job_alert_action' ) );
		add_action( 'init', array( __CLASS__, 'delete_job_alert_action' ) );
	}

	public static function edit_job_alert_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'edit_job_alert' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'edit-job-alert' ) ) {
			return;
		}
		$job_alert_id = self::_save_job_alert( $_POST );

		if ( $job_alert_id === false ) {
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'add-job-alert' ) );
		} else {
			jlt_message_add( __( 'Job alert saved', 'job-listings-job-alert' ) );
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'job-alert' ) );
		}

		exit();
	}

	private static function _save_job_alert( $args = '' ) {
		try {
			$defaults = array(
				'candidate_id' => get_current_user_id(),
				'job_alert_id' => '',
				'title'        => '',
				'keywords'     => '',
				'job_location' => '',
				'job_category' => '',
				'job_type'     => '',
				'status'       => 'publish',
			);
			$args     = wp_parse_args( $args, $defaults );
			if ( empty( $args[ 'candidate_id' ] ) ) {
				jlt_message_add( __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings-job-alert' ), 'error' );

				return false;
			}

			if ( ! jlt_is_logged_in() ) {
				jlt_message_add( __( 'Sorry, you can\'t post job alert.', 'job-listings-job-alert' ), 'error' );

				return false;
			}

			if ( ! empty( $args[ 'job_alert_id' ] ) && $args[ 'candidate_id' ] != get_post_field( 'post_author', $args[ 'job_alert_id' ] ) ) {
				jlt_message_add( __( 'Sorry, you can\'t edit this job alert.', 'job-listings-job-alert' ), 'error' );

				return false;
			}

			$candidate_id = intval( $args[ 'candidate_id' ] );

			$job_alert = array(
				'post_title'  => wp_kses( $args[ 'title' ], array() ),
				'post_type'   => 'job_alert',
				'post_status' => wp_kses( $args[ 'status' ], array() ),
				'post_author' => $candidate_id,
			);
			if ( empty( $job_alert[ 'post_title' ] ) ) {
				jlt_message_add( __( 'Your job alert needs a name.', 'job-listings-job-alert' ), 'error' );

				return false;
			}

			if ( ! empty( $args[ 'job_alert_id' ] ) ) {
				$job_alert[ 'ID' ] = intval( $args[ 'job_alert_id' ] );
				if ( ! jlt_job_alert_is_owner( $candidate_id, $job_alert[ 'ID' ] ) ) {
					jlt_message_add( __( 'Sorry, you can\'t edit this job alert.', 'job-listings-job-alert' ), 'error' );

					return false;
				}
				$post_id = wp_update_post( $job_alert );
			} else {
				$post_id = wp_insert_post( $job_alert );
			}

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_keywords', wp_kses( $args[ 'keywords' ], array() ) );
				update_post_meta( $post_id, '_job_location', json_encode( wp_kses( $args[ 'job_location' ], array() ) ) );
				update_post_meta( $post_id, '_job_category', json_encode( wp_kses( $args[ 'job_category' ], array() ) ) );
				update_post_meta( $post_id, '_job_type', wp_kses( $args[ 'job_type' ], array() ) );

				$frequency     = wp_kses( $args[ 'frequency' ], array() );
				$old_frequency = jlt_get_post_meta( $post_id, '_frequency' );
				if ( $frequency != $old_frequency ) {
					update_post_meta( $post_id, '_frequency', $frequency );

					// Schedule new alert
					JLT_Job_Alert::set_alert_schedule( $post_id, $frequency );
				}

				do_action( 'jlt_save_job_alert', $post_id );
			} else {
				jlt_message_add( __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings-job-alert' ), 'error' );

				return false;
			}

			do_action( 'jlt_after_save_job_alert', $post_id );

			return $post_id;
		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	public static function delete_job_alert_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( 'GET' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_GET[ 'action' ] ) || 'delete_job_alert' !== $_GET[ 'action' ] || empty( $_GET[ '_wpnonce' ] ) || ! wp_verify_nonce( $_GET[ '_wpnonce' ], 'edit-job-alert' ) ) {
			return;
		}
		$job_alert_id = isset( $_GET[ 'job_alert_id' ] ) ? intval(sanitize_text_field($_GET[ 'job_alert_id' ])) : '';

		if ( empty( $job_alert_id ) ) {
			jlt_message_add( __( 'There was a problem deleting this job alert', 'job-listings-job-alert' ) );
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'job-alert' ) );
		} else {
			wp_delete_post( $job_alert_id );
			jlt_message_add( __( 'Job alert deleted', 'job-listings-job-alert' ) );
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'job-alert' ) );
		}

		exit();
	}
}

new JLT_Job_Alert_Hander();