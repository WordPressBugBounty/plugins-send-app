<?php
namespace Send_App\Core\Integrations\Classes\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * class Forms_Component
 */
abstract class Forms_Component_Base {

	abstract protected function get_name(): string;

	/**
	 * Return all forms for a module.
	 *
	 * @param array $response
	 * @param ?\WP_REST_Request $request
	 *
	 * @return array
	 */
	abstract public function get_all_forms( array $response, ?\WP_REST_Request $request = null ): array;

	/**
	 * Return information for a specific form for a module.
	 *
	 * @param array $response
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 * @throws \Throwable
	 */
	abstract public function get_form_info( array $response, \WP_REST_Request $request ): array;

	/**
	 * Return information for a specific form for a module.
	 *
	 * @param string $form_id
	 *
	 * @return array | \WP_Error
	 */
	abstract public function get_form_info_legacy( string $form_id );

	public function __construct() {
		$filter_prefix = 'send_app/rest/integrations/' . $this->get_name() . '/forms';
		add_filter( $filter_prefix, [ $this, 'get_all_forms' ], 10, 2 );
		add_filter( $filter_prefix . '/by-id', [ $this, 'get_form_info' ], 10, 2 );
	}
}
