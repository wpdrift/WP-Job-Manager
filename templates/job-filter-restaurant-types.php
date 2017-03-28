<?php if ( ! is_tax( 'restaurant_listing_type' ) && empty( $restaurant_types ) ) : ?>
	<ul class="restaurant_types">
		<?php foreach ( get_restaurant_listing_types() as $type ) : ?>
			<li><label for="job_type_<?php echo $type->slug; ?>" class="<?php echo sanitize_title( $type->name ); ?>"><input type="checkbox" name="filter_job_type[]" value="<?php echo $type->slug; ?>" <?php checked( in_array( $type->slug, $selected_restaurant_types ), true ); ?> id="job_type_<?php echo $type->slug; ?>" /> <?php echo $type->name; ?></label></li>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" name="filter_job_type[]" value="" />
<?php elseif ( $restaurant_types ) : ?>
	<?php foreach ( $restaurant_types as $job_type ) : ?>
		<input type="hidden" name="filter_job_type[]" value="<?php echo sanitize_title( $job_type ); ?>" />
	<?php endforeach; ?>
<?php endif; ?>