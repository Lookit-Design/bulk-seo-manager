<?php
/**
 * @package Lookit_Bulk_SEO_Manager
 */

class Test_Lookit_Bulk_SEO_Manager_Plugin extends WP_UnitTestCase {

	public function test_plugin_defines_version() {
		$this->assertTrue( defined( 'BSM_VERSION' ) );
	}

	public function test_settings_class_is_available() {
		$this->assertTrue( class_exists( 'ASY_Settings' ) );
	}

	public function test_sanitize_templates_returns_array() {
		$result = bsm_sanitize_templates( 'not-an-array' );
		$this->assertSame( array(), $result );
	}
}
