<?php
/**
 * @package Lookit_Bulk_SEO_Manager
 */

class Test_Lookit_Bulk_SEO_Manager_Settings extends WP_UnitTestCase {

	const SECRET      = 'sk-openrouter-test-key';
	const TEXT_SECRET = 'lookit-text-webhook-test-token';

	public function tear_down() {
		delete_option( 'asy_openrouter_api_key' );
		delete_option( 'bsm_ai_webhook_url' );
		delete_option( 'bsm_ai_webhook_token' );
		remove_all_filters( 'pre_http_request' );
		parent::tear_down();
	}

	public function test_store_blank_keeps_existing_key() {
		$settings = new ASY_Settings();
		$settings->store_openrouter_api_key( self::SECRET );

		$this->assertSame( self::SECRET, $settings->store_openrouter_api_key( '' ) );
		$this->assertSame( self::SECRET, get_option( 'asy_openrouter_api_key' ) );
	}

	public function test_store_saves_trimmed_key_without_autoload() {
		$settings = new ASY_Settings();
		$settings->store_openrouter_api_key( '  ' . self::SECRET . '  ' );

		$this->assertSame( self::SECRET, get_option( 'asy_openrouter_api_key' ) );
		$this->assertArrayNotHasKey( 'asy_openrouter_api_key', wp_load_alloptions() );
	}

	public function test_maybe_disable_autoload_removes_key_from_autoload() {
		delete_option( 'asy_openrouter_api_key' );
		add_option( 'asy_openrouter_api_key', self::SECRET, '', 'yes' );

		$this->assertArrayHasKey( 'asy_openrouter_api_key', wp_load_alloptions() );

		$settings = new ASY_Settings();
		$settings->maybe_disable_autoload();

		$this->assertArrayNotHasKey( 'asy_openrouter_api_key', wp_load_alloptions() );
		$this->assertSame( self::SECRET, get_option( 'asy_openrouter_api_key' ) );
	}

	public function test_text_webhook_token_is_preserved_and_not_autoloaded() {
		bsm_store_ai_settings( 'https://platform.example.test/text', self::TEXT_SECRET );
		bsm_store_ai_settings( 'https://platform.example.test/text', '' );

		$this->assertSame( self::TEXT_SECRET, get_option( 'bsm_ai_webhook_token' ) );
		$this->assertArrayNotHasKey( 'bsm_ai_webhook_token', wp_load_alloptions() );
		$this->assertSame( 'Bearer ' . self::TEXT_SECRET, bsm_ai_webhook_headers()['Authorization'] );
	}

	public function test_text_generation_requires_token_before_sending_content() {
		$post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		update_option( 'bsm_ai_webhook_url', 'https://platform.example.test/text' );
		$requests = 0;
		add_filter(
			'pre_http_request',
			static function ( $response ) use ( &$requests ) {
				++$requests;
				return $response;
			}
		);

		$result = bsm_ai_call_webhook( 'metadesc', get_post( $post_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'no_webhook_token', $result->get_error_code() );
		$this->assertSame( 0, $requests );
	}

	public function test_text_generation_sends_saved_bearer_token() {
		$post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
		bsm_store_ai_settings( 'https://platform.example.test/text', self::TEXT_SECRET );
		$request = array();
		add_filter(
			'pre_http_request',
			static function ( $response, $args ) use ( &$request ) {
				$request = $args;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'text' => 'Generated description.' ) ),
					'response' => array( 'code' => 200 ),
					'cookies'  => array(),
				);
			},
			10,
			2
		);

		$result = bsm_ai_call_webhook( 'metadesc', get_post( $post_id ) );

		$this->assertSame( 'Generated description.', $result );
		$this->assertSame( 'Bearer ' . self::TEXT_SECRET, $request['headers']['Authorization'] );
		$this->assertStringNotContainsString( self::TEXT_SECRET, $request['body'] );
	}
}
