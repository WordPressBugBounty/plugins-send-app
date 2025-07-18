<?php

namespace Send_App\Modules\Gravityforms\Components;

use Send_App\Core\Integrations\Classes\Forms\Forms_Component_Base;
use Send_App\Modules\Gravityforms\Classes\Forms_Data_Helper;
use Send_App\Modules\Gravityforms\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Forms extends Forms_Component_Base {

	protected function get_name(): string {
		return Module::get_name();
	}

	/**
	 * Return all forms for the integration.
	 *
	 * @param array $response
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

	protected function extract_form_by_external_id( string $form_id ) {
		$raw_form_id = Forms_Data_Helper::extract_form_id( $form_id );

		return Forms_Data_Helper::get_published_form( $raw_form_id );
	}

	/**
	 * Create details for a single form.
	 *
	 * @param array $form_object
	 *
	 * @return array
	 */
	protected function create_form_info( $form_object ): array {
		$formatted_id = Forms_Data_Helper::normalize_form_id( $form_object );
		$title = Forms_Data_Helper::get_form_title( $form_object );

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
}
