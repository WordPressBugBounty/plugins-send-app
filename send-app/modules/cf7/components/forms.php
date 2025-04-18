<?php

namespace Send_App\Modules\CF7\Components;

use Send_App\Core\Integrations\Classes\Forms\Forms_Component_Base;
use Send_App\Core\Integrations\Options\Disabled_Forms_Option;
use Send_App\Modules\CF7\Classes\Forms_Data_Helper;
use Send_App\Modules\CF7\Module;

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
	 * @param array             $response
	 * @param ?\WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_all_forms( array $response, ?\WP_REST_Request $request = null ): array {
		static $all_forms = null;
		if ( ! is_null( $all_forms ) ) {
			return $all_forms;
		}

		$published_forms = Forms_Data_Helper::get_published_forms();

		$all_forms = [];
		foreach ( $published_forms as $form_object ) {
			$formatted_id             = Forms_Data_Helper::normalize_form_id( $form_object );
			$form_info                = $this->create_form_info( $form_object );
			$all_forms[ $formatted_id ] = $form_info;
		}

		return array_merge( $response, $all_forms );
	}

	/**
	 * Returns details for a single form
	 *
	 * @param array            $response
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

		$raw_form_id = Forms_Data_Helper::extract_form_id( $form_id );

		$form = Forms_Data_Helper::get_published_form( $raw_form_id );

		if ( empty( $form ) ) {
			throw new \Exception( 'Form not found', 404 );
		}

		$enable_tracking = $request->get_param( 'trackingEnabled' );

		if ( $enable_tracking ) {
			Disabled_Forms_Option::remove( $form_id, Module::get_name() );
		} else {
			Disabled_Forms_Option::add( $form_id, Module::get_name() );
		}

		return $this->create_form_info( $form );
	}

	/**
	 * Create details for a single form.
	 *
	 * @param string $form_id
	 *
	 * @return array
	 */
	private function create_form_info( \WPCF7_ContactForm $form_object ): array {
		$formatted_id = Forms_Data_Helper::normalize_form_id( $form_object );
		$title        = Forms_Data_Helper::get_form_title( $form_object );

		return [
			'id'               => $formatted_id,
			'name'             => $title,
			'tracking_enabled' => ! $this->is_disabled_form( $formatted_id ),
			'integration'      => $this->get_name(),
			'page_ids'         => [],
		];
	}

	public function get_forms( array $post_ids, bool $force = false ): array {
		$forms           = [];
		$published_forms = Forms_Data_Helper::get_published_forms();

		foreach ( $published_forms as $form ) {
			$form_info = $this->create_form_info( $form );
			$forms[ $form_info['id'] ] = $form_info;
		}

		return $forms;
	}

	/**
	 * Returns details for a single Form
	 *
	 * @param string $form_id
	 *
	 * @return array
	 */
	public function get_form_info_legacy( string $form_id ): array {
		$forms     = $this->get_forms( [] );
		$form_info = [];

		foreach ( $forms as $form ) {
			if ( $form['id'] === $form_id ) {
				$form_info = $form;
			}
		}

		return $form_info;
	}

	public function get_disabled_forms(): array {
		static $disabled_forms = null;
		if ( ! is_null( $disabled_forms ) ) {
			return $disabled_forms;
		}

		$disabled_forms = Disabled_Forms_Option::get_all( Module::get_name() );

		return $disabled_forms;
	}

	public function is_disabled_form( string $form_id ): bool {
		return in_array( $form_id, $this->get_disabled_forms(), true );
	}
}
