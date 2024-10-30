<?php
/**
 * Manage Job Alert Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-job-alert.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<?php do_action( 'jlt_member_manage_job_alert_before' ); ?>
<?php $title_text = $list_jobs->found_posts ? sprintf( _n( "You've set up %s job alert", "You've set up %s job alerts", $list_jobs->found_posts, 'job-listings-job-alert' ), $list_jobs->found_posts, $current_user->user_email ) : __( 'You have no job alert', 'job-listings-job-alert' ); ?>
	<div class="member-manage">

		<div><?php echo $title_text; ?></div>

		<div><?php echo sprintf( __( 'The emails will be sent to "%s"', 'job-listings-job-alert' ), $current_user->user_email ); ?></div>

		<a href="<?php echo jlt_job_alert_new_url(); ?>" class="jlt-btn"
		   style="margin-bottom: 15px;"><?php _e( 'Create New', 'job-listings-job-alert' ); ?></a>

		<form method="post">
			<div class="member-manage-table">
				<ul class="jlt-list jlt-list-alert">
					<li>
						<div class="col-job-alert-title jlt-col-30"><?php _e( 'Alert Name', 'job-listings-job-alert' ) ?></div>
						<div class="col-job-alert-info jlt-col-40"><?php _e( 'Infomation', 'job-listings-job-alert' ) ?></div>
						<div class="col-job-alert-frequency jlt-col-15"><?php _e( 'Schedule', 'job-listings-job-alert' ) ?></div>
						<div class="col-actions jlt-col-15"><?php _e( 'Action', 'job-listings-job-alert' ) ?></div>
					</li>
					<?php if ( $list_jobs->have_posts() ):
						?>
						<?php
						while ( $list_jobs->have_posts() ): $list_jobs->the_post();
							global $post;

							$job_location  = jlt_get_post_meta( get_the_ID(), '_job_location' );
							$job_locations = array();
							if ( ! empty( $job_location ) ) {
								$job_location  = jlt_json_decode( $job_location );
								$job_locations = empty( $job_location ) ? array() : get_terms( 'job_location', array(
									'include'    => array_merge( $job_location, array( - 1 ) ),
									'hide_empty' => 0,
									'fields'     => 'names',
								) );
							}

							$job_category   = jlt_get_post_meta( get_the_ID(), '_job_category', '' );
							$job_categories = array();
							if ( ! empty( $job_category ) ) {
								$job_category   = jlt_json_decode( $job_category );
								$job_categories = empty( $job_category ) ? array() : get_terms( 'job_category', array(
									'include'    => array_merge( $job_category, array( - 1 ) ),
									'hide_empty' => 0,
									'fields'     => 'names',
								) );
							}

							$job_type      = jlt_get_post_meta( get_the_ID(), '_job_type' );
							$job_type_term = ! empty( $job_type ) ? get_term_by( 'id', $job_type, 'job_type' ) : null;

							?>
							<li>
								<div class="col-job-alert-title jlt-col-30">
									<div class="job-alet-name"><?php the_title() ?></div>
									<em class="jlt-job-alert-keyword">
										<?php _e( 'Keywords: ', 'job-listings-job-alert' ) ?>
										<?php echo jlt_get_post_meta( get_the_ID(), '_keywords' ) ?>
									</em>
								</div>
								<div class="col-job-alert-info jlt-col-40">

									<?php if ( ! empty( $job_locations ) ): ?>
										<div class="jlt-job-alert-location">
											<i class="jlt-icon jltfa-map-marker"></i>
											<?php echo implode( ', ', $job_locations ); ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $job_categories ) ): ?>
										<div class="jlt-job-alert-category">
											<i class="jlt-icon jltfa-tag"></i>
											<?php echo implode( ', ', $job_categories ); ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $job_type_term ) ): ?>
										<div class="jlt-job-alert-type">
											<i class="jlt-icon jltfa-bookmark"></i>
											<?php echo esc_html( $job_type_term->name ); ?>
										</div>
									<?php endif; ?>

								</div>
								<div class="col-job-alert-frequency jlt-col-15">
									<i class="jlt-icon jltfa-calendar"></i>
									<?php echo jlt_job_alert_get_frequency(); ?>
								</div>
								<div class="col-actions jlt-col-15">
									<a href="<?php echo job_alert_edit_url(); ?>"
									   class="jlt-btn-link" title="<?php _e( 'Edit Job Alert', 'job-listings-job-alert' ) ?>">
										<i class="jlt-icon jltfa-pencil"></i>
									</a>
									<a href="<?php echo jlt_job_alert_delete_url(); ?>"
									   class="jlt-btn-link" title="<?php _e( 'Delete Job Alert', 'job-listings-job-alert' ) ?>">
										<i class="jlt-icon jltfa-trash-o"></i></a>
								</div>
							</li>
						<?php endwhile; ?>
					<?php else: ?>
						<li>
							<div class="jlt-not-found"><?php _e( 'No saved job alerts', 'job-listings-job-alert' ) ?></div>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<?php jlt_member_pagination( $list_jobs ) ?>

			<?php jlt_form_nonce( 'job-alert-manage-action' ) ?>
		</form>
	</div>
<?php
do_action( 'jlt_member_manage_job_alert_after' );