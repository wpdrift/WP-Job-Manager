<?php global $post; ?>
<div class="single_restaurant_listing" itemscope itemtype="http://schema.org/JobPosting">
	<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />

	<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
		<div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
	<?php else : ?>
		<?php
			/**
			 * single_restaurant_listing_start hook
			 *
			 * @hooked restaurant_listing_meta_display - 20
			 * @hooked restaurant_listing_company_display - 30
			 */
			do_action( 'single_restaurant_listing_start' );
		?>

		<div class="job_description" itemprop="description">
			<?php echo apply_filters( 'the_job_description', get_the_content() ); ?>
		</div>

		<?php if ( candidates_can_apply() ) : ?>
			<?php get_job_manager_template( 'job-application.php' ); ?>
		<?php endif; ?>

		<?php
			/**
			 * single_restaurant_listing_end hook
			 */
			do_action( 'single_restaurant_listing_end' );
		?>
	<?php endif; ?>
</div>
