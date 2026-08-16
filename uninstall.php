<?php
/**
 * Uninstall routine for Lookit Bulk SEO Manager.
 *
 * @package Lookit_Bulk_SEO_Manager
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$asy_uninstall_options = array(
	'asy_openrouter_api_key',
	'asy_post_type_templates',
	'asy_kp_count',
	'bsm_custom_templates',
	'bsm_keyphrase_templates',
	'bsm_title_templates',
	'bsm_ai_webhook_url',
);

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $asy_uninstall_site_id ) {
		switch_to_blog( $asy_uninstall_site_id );
		foreach ( $asy_uninstall_options as $asy_uninstall_option ) {
			delete_option( $asy_uninstall_option );
		}
		restore_current_blog();
	}
} else {
	foreach ( $asy_uninstall_options as $asy_uninstall_option ) {
		delete_option( $asy_uninstall_option );
	}
}
