<?php

/**
 * Manage themes for a WordPress site
 */
class WP_REST_Themes_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'themes';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<slug>[\w-]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
		) );
	}

	/**
	 * Check if a given request has access to read /themes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'switch_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot view the list of themes' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_items( $request ) {
		$data   = array();
		$themes = wp_get_themes();

		foreach ( $themes as $slug => $obj ) {
			$obj->slug = $slug;
			$theme     = $this->prepare_item_for_response( $obj, $request );
			if ( is_wp_error( $theme ) ) {
				continue;
			}

			$data[] = $this->prepare_response_for_collection( $theme );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Check if a given request has access to read /theme/{theme-name}
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'switch_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you do not have access to this resource' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function get_item( $request ) {
		$slug  = $request['slug'];
		$theme = null;

		$themes = wp_get_themes();
		foreach ( $themes as $key => $obj ) {
			if ( $slug === $key ) {
				$theme       = $obj;
				$theme->slug = $key;
				break;
			}
		}

		if ( ! $theme ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid theme slug.' ), array( 'status' => 404 ) );
		}

		$data = $this->prepare_item_for_response( $theme, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * check if a request can delete a theme
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can( 'delete_themes' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot delete themes' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;

	}

	public function delete_item( $request ) {

	}

	/**
	 * Prepare item for response
	 *
	 * @param WP_Theme $item
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function prepare_item_for_response( $item, $request ) {
		return array(
			'name'        => $item->get( 'Name' ),
			'slug'        => $item->slug,
			'version'     => $item->get( 'Version' ),
			'description' => $item->get( 'Description' ),
			'author'      => $item->get( 'Author' ),
			'author_uri'  => $item->get( 'AuthorURI' ),
			'text_domain' => $item->get( 'TextDomain' ),
			'domain_path' => $item->get( 'DomainPath' ),
			'tags'        => $item->get( 'Tags' ),
		);
	}

	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'theme',
			'type'       => 'object',
			'properties' => array(
				'name'        => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'slug'        => array(
					'description' => __( 'The identifier for theme' ),
					'type'        => 'string',
				),
				'version'     => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'description' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'author'      => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'author_uri'  => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'text_domain' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'domain_path' => array(
					'description' => __( 'The title for the resource.' ),
					'type'        => 'string',
				),
				'tags'        => array(
					'description' => __( 'List of Tags used to describe the theme. These allow user to find your theme using the tag filter.' ),
					'type'        => 'array',
					'minItems'    => 1,
					'items'       => array(
						'type' => 'string',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	public function get_collection_params() {
		return array();
	}

}
