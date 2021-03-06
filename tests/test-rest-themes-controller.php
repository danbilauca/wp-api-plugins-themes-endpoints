<?php

class WP_Test_REST_Themes_Controller extends WP_Test_REST_Controller_TestCase {
	protected $admin_id;

	public function setUp() {
		parent::setUp();

		$this->admin_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}

	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wp/v2/themes', $routes );
		$this->assertArrayHasKey( '/wp/v2/themes/(?P<slug>[\w-]+)', $routes );
	}

	public function test_get_items_without_permissions() {
		wp_set_current_user( 0 );

		$request = new WP_REST_Request( 'GET', '/wp/v2/plugins' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );

	}

	public function test_delete_item_without_permissions() {

		wp_set_current_user( 0 );

		$request = new WP_REST_Request( WP_REST_Server::DELETABLE, '/wp/v2/themes/theme-name' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );

	}

	public function test_context_param() {

	}

	public function test_get_items() {
		wp_set_current_user( $this->admin_id );
		$request = new WP_REST_Request( 'GET', '/wp/v2/themes' );
		/** @var WP_REST_Response $response */
		$response = $this->server->dispatch( $request );
		$this->check_response( $response );

		//check if there exists at least one item in the list
		$data = $response->get_data();
		$this->assertGreaterThanOrEqual( 1, $data );

		$first = reset( $data );
		$this->check_data( $first );
	}

	public function test_get_item() {
		wp_set_current_user( $this->admin_id );
		$request = new WP_REST_Request( 'GET', '/wp/v2/themes/twentysixteen' );
		/** @var WP_REST_Response $response */
		$response = $this->server->dispatch( $request );

		$this->check_response( $response );
		$this->check_data( $response->get_data() );
	}

	public function test_create_item() {

	}

	public function test_update_item() {

	}

	public function test_delete_item() {

	}

	public function test_prepare_item() {

	}

	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/wp/v2/themes' );
		$response   = $this->server->dispatch( $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertEquals( 9, count( $properties ) );

		$this->assertArrayHasKey( 'name', $properties );
		$this->assertArrayHasKey( 'version', $properties );
		$this->assertArrayHasKey( 'description', $properties );
		$this->assertArrayHasKey( 'author', $properties );
		$this->assertArrayHasKey( 'author_uri', $properties );
		$this->assertArrayHasKey( 'text_domain', $properties );
		$this->assertArrayHasKey( 'domain_path', $properties );
		$this->assertArrayHasKey( 'tags', $properties );
		$this->assertArrayHasKey( 'minItems', $properties['tags'] );
		$this->assertGreaterThanOrEqual( 1, $properties['tags']['minItems'] );
	}

	/**
	 * General checking for response
	 *
	 * @param WP_REST_Response $response
	 */
	protected function check_response( $response ) {
		$this->assertNotInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Check theme's data
	 *
	 * @param $data
	 */
	protected function check_data( $data ) {
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'version', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'author', $data );
		$this->assertArrayHasKey( 'author_uri', $data );
		$this->assertArrayHasKey( 'text_domain', $data );
		$this->assertArrayHasKey( 'domain_path', $data );
		$this->assertArrayHasKey( 'tags', $data );
	}

}
