<?php
/**
 * @package Lookit_Bulk_SEO_Manager
 */

class Test_Lookit_Bulk_SEO_Manager_Uninstall extends WP_UnitTestCase {

	public function test_uninstall_deletes_plugin_options() {
		update_option( 'asy_openrouter_api_key', 'lookit-test-value' );

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'lookit-bulk-seo-manager/lookit-bulk-seo-manager.php' );
		}
		require dirname( __DIR__ ) . '/uninstall.php';

		$this->assertFalse( get_option( 'asy_openrouter_api_key' ) );
	}
}
