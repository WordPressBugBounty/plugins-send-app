<?php
namespace Send_App\Modules\Elementor\Components;

use Send_App\Modules\Elementor\Module;
use Send_App\Modules\Elementor\Classes\Forms_Data_Helper;
use Send_App\Core\Integrations\Options\Disabled_Forms_Option;
use Send_App\Core\Integrations\Classes\Forms\Forms_Component_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Forms extends Forms_Component_Base {
	protected function get_name(): string {
		return Module::get_name();
	}

	/**
	 * Return all forms for the Elementor integration.
	 *
	 * @param array $response
	 * @param ?\WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_all_forms( array $response, ?\WP_REST_Request $request = null ): array {
		$forms_data = [];

		$posts_with_forms = Forms_Data_Helper::get_post_ids_with_forms();
		$posts_with_forms_data = Forms_Data_Helper::get_forms_from_post_ids( $posts_with_forms );

		$forms_posts = [];
		// flip the array to get the aggregation by form id:
		foreach ( $posts_with_forms_data as $post_id => $forms ) {
			foreach ( $forms as $form ) {
				if ( empty( $forms_data[ $form['id'] ] ) ) {
					$forms_data[ $form['id'] ] = $this->create_form_info( $form );
				}
				$forms_posts[ $form['id'] ]['page_ids'][] = strval( $post_id );
			}
		}

		foreach ( $forms_data as $form_id => $form_data ) {
			$forms_data[ $form_id ]['page_ids'] = array_unique( $forms_posts[ $form_id ]['page_ids'] );
		}

		if ( empty( $forms_data ) ) {
			$response[ $this->get_name() ] = new \WP_Error( 'no_forms_data', sprintf( '[%s] No forms data', Module::get_name() ) );
			return $response;
		}

		return array_merge( $response, $forms_data );
	}

	/**
	 * Returns details for a single Form
	 *
	 * @param array $response
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public function get_form_info( array $response, \WP_REST_Request $request ): array {
		$form_id = $request->get_param( 'form_id' );
		if ( empty( $form_id ) ) {
			throw new \Exception( 'Missing form_id param', 400 );
		}

		// will return a nested array, top level is by post-id and for each post - the related forms
		$form_instances = Forms_Data_Helper::get_form_instances_by_form_id( $form_id );

		if ( empty( $form_instances ) ) {
			throw new \Exception( 'Form not found', 404 );
		}

		$form_info = [];

		$enable_tracking = $request->get_param( 'trackingEnabled' );

		if ( $enable_tracking ) {
			Disabled_Forms_Option::remove( $form_id, $this->get_name() );
		} else {
			Disabled_Forms_Option::add( $form_id, $this->get_name() );
		}

		foreach ( $form_instances as $post_id => $form ) {
			// TODO: do we need to return multiple instances of the same form but in different pages?
			// if we do - add the $post_id as array key to the $from_info array,
			// and maybe add the post_id as an optional request param
			// and return an array instead of overriding the same one
			$form_info = $this->create_form_info( $form );
		}

		if ( empty( $form_info ) ) {
			throw new \Exception( 'Form not found', 404 );
		}

		return $form_info;
	}

	/**
	 * Create details for a single form.
	 *
	 * @param array $form
	 * @return array
	 */
	private function create_form_info( array $form ): array {
		static $disabled_forms = null;

		$form_id = $form['id'];

		if ( is_null( $disabled_forms ) ) {
			$disabled_forms = Disabled_Forms_Option::get_all( $this->get_name() );
		}

		return [
			'id' => $form_id,
			'name' => $form['settings']['form_name'],
			'tracking_enabled' => ! in_array( $form_id, $disabled_forms, true ),
			'integration' => $this->get_name(),
		];
	}

	/**
	 * Returns details for a single Form
	 *
	 * @param string $form_id
	 * @return array
	 */
	public function get_form_info_legacy( string $form_id ): array {
		$parts = explode( '-', $form_id );

		$forms = [];
		if ( 2 === count( $parts ) ) {
			$form_id = $parts[1];
			$forms = Forms_Data_Helper::get_forms_for_post_id( $parts[0] );
		} elseif ( 1 === count( $parts ) ) {
			$forms = Forms_Data_Helper::get_form_instances_by_form_id( $form_id );
		}

		$form_info = [];

		foreach ( $forms as $form ) {
			if ( $form['id'] === $form_id ) {
				$form_info = $this->create_form_info( $form );
			}
		}

		return $form_info;
	}

	public function get_disabled_forms(): array {
		return Disabled_Forms_Option::get_all( $this->get_name() );
	}

	public function is_disabled_form( string $form_id ): bool {
		return in_array( $form_id, $this->get_disabled_forms(), true );
	}
}
