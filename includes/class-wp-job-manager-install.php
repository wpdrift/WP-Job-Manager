<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Restaurant_Listings_Install
 */
class WP_Restaurant_Listings_Install {

	/**
	 * Install WP Job Manager
	 */
	public static function install() {
		global $wpdb;

		self::init_user_roles();
		self::default_terms();
		self::schedule_cron();

		// Redirect to setup screen for new installs
		if ( ! get_option( 'wp_job_manager_version' ) ) {
			set_transient( '_job_manager_activation_redirect', 1, HOUR_IN_SECONDS );
		}

		// Update featured posts ordering
		if ( version_compare( get_option( 'wp_job_manager_version', JOB_MANAGER_VERSION ), '1.22.0', '<' ) ) {
			$wpdb->query( "UPDATE {$wpdb->posts} p SET p.menu_order = 0 WHERE p.post_type='restaurant_listing';" );
			$wpdb->query( "UPDATE {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id SET p.menu_order = -1 WHERE pm.meta_key = '_featured' AND pm.meta_value='1' AND p.post_type='restaurant_listing';" );
		}

		// Update legacy options
		if ( false === get_option( 'job_manager_submit_job_form_page_id', false ) && get_option( 'job_manager_submit_page_slug' ) ) {
			$page_id = get_page_by_path( get_option( 'job_manager_submit_page_slug' ) )->ID;
			update_option( 'job_manager_submit_job_form_page_id', $page_id );
		}
		if ( false === get_option( 'job_manager_job_dashboard_page_id', false ) && get_option( 'job_manager_job_dashboard_page_slug' ) ) {
			$page_id = get_page_by_path( get_option( 'job_manager_job_dashboard_page_slug' ) )->ID;
			update_option( 'job_manager_job_dashboard_page_id', $page_id );
		}

		delete_transient( 'wp_job_manager_addons_html' );
		update_option( 'wp_job_manager_version', JOB_MANAGER_VERSION );
	}

	/**
	 * Init user roles
	 */
	private static function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		if ( is_object( $wp_roles ) ) {
			add_role( 'employer', __( 'Employer', 'wp-job-manager' ), array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false
			) );

			$capabilities = self::get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
		}
	}

	/**
	 * Get capabilities
	 * @return array
	 */
	private static function get_core_capabilities() {
		return array(
			'core' => array(
				'manage_restaurant_listings'
			),
			'restaurant_listing' => array(
				"edit_restaurant_listing",
				"read_restaurant_listing",
				"delete_restaurant_listing",
				"edit_restaurant_listings",
				"edit_others_restaurant_listings",
				"publish_restaurant_listings",
				"read_private_restaurant_listings",
				"delete_restaurant_listings",
				"delete_private_restaurant_listings",
				"delete_published_restaurant_listings",
				"delete_others_restaurant_listings",
				"edit_private_restaurant_listings",
				"edit_published_restaurant_listings",
				"manage_restaurant_listing_terms",
				"edit_restaurant_listing_terms",
				"delete_restaurant_listing_terms",
				"assign_restaurant_listing_terms"
			)
		);
	}

	/**
	 * default_terms function.
	 */
	private static function default_terms() {
		if ( get_option( 'job_manager_installed_terms' ) == 1 ) {
			return;
		}

		$taxonomies = array(
			'restaurant_listing_type' => array(
				'Full Time',
				'Part Time',
				'Temporary',
				'Freelance',
				'Internship'
			)
		);

		foreach ( $taxonomies as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
					wp_insert_term( $term, $taxonomy );
				}
			}
		}

		update_option( 'job_manager_installed_terms', 1 );
	}

	/**
	 * Setup cron jobs
	 */
	private static function schedule_cron() {
		wp_clear_scheduled_hook( 'job_manager_check_for_expired_jobs' );
		wp_clear_scheduled_hook( 'job_manager_delete_old_previews' );
		wp_clear_scheduled_hook( 'job_manager_clear_expired_transients' );
		wp_schedule_event( time(), 'hourly', 'job_manager_check_for_expired_jobs' );
		wp_schedule_event( time(), 'daily', 'job_manager_delete_old_previews' );
		wp_schedule_event( time(), 'twicedaily', 'job_manager_clear_expired_transients' );
	}
}
