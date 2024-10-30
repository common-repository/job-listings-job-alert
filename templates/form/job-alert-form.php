<?php
/**
 * Job Alert Form.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/form/job-alert-form.php.
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
	<form method="post" id="add_job_alert_form" class="jlt-form">
		<div class="form-title">
			<?php if ( $is_edit ) : ?>
				<p><?php _e( 'Edit Job alert', 'job-listings-job-alert' ); ?></p>
			<?php else : ?>
				<p><?php _e( 'Create a new Job alert', 'job-listings-job-alert' ); ?></p>
			<?php endif; ?>
		</div>
		<div class="job_alert-form">
			<?php
			$job_alert_name       = $job_alert ? $job_alert->post_title : '';
			$job_alert_name_field = array(
				'name'  => 'title',
				'label' => __( 'Alert Name', 'job-listings-job-alert' ),
				'type'  => 'text',
				'value' => $job_alert_name,
				'required' => true,
			);
			jlt_render_form_field( $job_alert_name_field );

			$job_alert_keyword = $job_alert ? jlt_get_post_meta( $job_alert_id, '_keywords' ) : '';

			$job_alert_keyword_field = array(
				'name'  => 'keywords',
				'label' => __( 'Keywords', 'job-listings-job-alert' ),
				'type'  => 'text',
				'value' => $job_alert_keyword,
			);
			jlt_render_form_field( $job_alert_keyword_field );

			?>
			<fieldset class="fieldset">
				<label for="job_location"><?php _e( 'Job Location', 'job-listings-job-alert' ) ?></label>
				<div class="field <?php if ( is_rtl() ) {
					echo ' chosen-rtl';
				} ?>">
					<select id="job_location" name="job_location[]" multiple
					        class="jlt-form-control jlt-form-control-chosen">
						<option value=""></option>
						<?php
						$locations = get_terms( 'job_location', array( 'hide_empty' => 0 ) );
						if ( $locations ):
							$value = $job_alert ? jlt_get_post_meta( $job_alert_id, '_job_location' ) : '';
							$value = jlt_json_decode( $value );
							?><?php
							foreach ( $locations as $location ) : ?>
								<option
									value="<?php echo esc_attr( $location->term_id ) ?>" <?php if ( ! empty( $value ) && in_array( $location->term_id, $value ) ): ?> selected="selected"<?php endif; ?>><?php echo esc_html( $location->name ) ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
			</fieldset>

			<fieldset class="fieldset">
				<label for="job_category"><?php _e( 'Job Category', 'job-listings-job-alert' ) ?></label>
				<div class="field <?php if ( is_rtl() ) {
					echo ' chosen-rtl';
				} ?>">
					<select id="job_category" name="job_category[]" multiple
					        class="jlt-form-control jlt-form-control-chosen">>
						<option value=""></option>
						<?php
						$categories = get_terms( 'job_category', array( 'hide_empty' => 0 ) );
						if ( $categories ):
							$value = $job_alert ? jlt_get_post_meta( $job_alert_id, '_job_category', '' ) : '';
							$value  = jlt_json_decode( $value );
							?>
							<?php foreach ( $categories as $category ): ?>
							<option
								value="<?php echo esc_attr( $category->term_id ) ?>" <?php if ( ! empty( $value ) && in_array( $category->term_id, $value ) ): ?> selected="selected"<?php endif; ?>><?php echo esc_html( $category->name ) ?></option>
						<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
			</fieldset>

			<fieldset class="fieldset">
				<label for="job_type"><?php _e( 'Job Type', 'job-listings-job-alert' ) ?></label>
				<div class="field">
					<?php
					$types    = get_terms( 'job_type', array( 'hide_empty' => 0 ) );
					$job_type = $job_alert ? jlt_get_post_meta( $job_alert_id, '_job_type' ) : '';
					if ( $types ):
						?>
						<select class="jlt-form-control" name="job_type" id="job_type">
							<option value="" <?php selected( $job_type, '' ); ?>></option>
							<?php foreach ( $types as $type ): ?>
								<option
									value="<?php echo esc_attr( $type->term_id ) ?>" <?php selected( $job_type, $type->term_id ); ?>><?php echo esc_html( $type->name ) ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>
			</fieldset>

			<fieldset class="fieldset">
				<label for="frequency"><?php _e( 'Email Frequency', 'job-listings-job-alert' ) ?></label>
				<div class="field">
					<?php
					$frequency     = $job_alert ? jlt_get_post_meta( $job_alert_id, '_frequency', 'weekly' ) : 'weekly';
					$frequency_arr = JLT_Job_Alert::get_frequency();
					?>
					<select class="jlt-form-control" name="frequency" id="frequency">
						<?php foreach ( $frequency_arr as $key => $label ): ?>
							<option
								value="<?php echo esc_attr( $key ) ?>" <?php selected( $frequency, $key ); ?>><?php echo $label ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>

		</div>
		<input type="hidden" name="action" value="edit_job_alert"/>
		<input type="hidden" name="job_alert_id" value="<?php echo $job_alert_id; ?>"/>
		<input type="hidden" name="candidate_id" value="<?php echo get_current_user_id(); ?>"/>
		<?php jlt_form_nonce( 'edit-job-alert' ) ?>
		<button type="submit" class="jlt-btn"><?php echo esc_html__( 'Save', 'job-listings-job-alert' ) ?></button>

	</form>
<?php do_action( 'jlt_post_job_alert_after' ); ?>