<?php
/**
 * Uninstall Give
 *
 * @package     Give
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load Give file.
include_once( 'give.php' );

global $wpdb, $wp_roles;


if ( give_is_setting_enabled( give_get_option( 'uninstall_on_delete' ) ) ) {

	// Delete All the Custom Post Types.
	$give_taxonomies = array( 'form_category', 'form_tag', 'give_log_type' );
	$give_post_types = array( 'give_forms', 'give_payment', 'give_log' );
	foreach ( $give_post_types as $post_type ) {

		$give_taxonomies = array_merge( $give_taxonomies, get_object_taxonomies( $post_type ) );
		$items           = get_posts( array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => - 1,
			'fields'      => 'ids',
		) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true );
			}
		}
	}

	// Delete All the Terms & Taxonomies.
	foreach ( array_unique( array_filter( $give_taxonomies ) ) as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	// Delete the Plugin Pages.
	$give_created_pages = array( 'success_page', 'failure_page', 'history_page' );
	foreach ( $give_created_pages as $p ) {
		$page = give_get_option( $p, false );
		if ( $page ) {
			wp_delete_post( $page, true );
		}
	}

	// Delete Capabilities.
	Give()->roles = new Give_Roles();
	Give()->roles->remove_caps();

	// Delete the Roles.
	$give_roles = array( 'give_manager', 'give_accountant', 'give_worker' );
	foreach ( $give_roles as $role ) {
		remove_role( $role );
	}

	// Remove all database tables.
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'give_donors' );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'give_customers' );
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'give_customermeta' );

	// Cleanup Cron Events.
	wp_clear_scheduled_hook( 'give_daily_scheduled_events' );
	wp_clear_scheduled_hook( 'give_daily_cron' );
	wp_clear_scheduled_hook( 'give_weekly_cron' );

	// Get all options.
	$give_option_names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
			'give'
		)
	);

	if ( ! empty( $give_option_names ) ) {
		// Convert option name to transient or option name.
		$new_give_option_names = array();

		// Delete all the Plugin Options.
		foreach ( $give_option_names as $option ) {
			if ( false !== strpos( $option, 'give_cache' ) ) {
				Give_Cache::delete( $option );
			} else {
				delete_option( $option );
			}
		}
	}
}
