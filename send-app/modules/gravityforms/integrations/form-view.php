<?php
namespace Send_App\Modules\Gravityforms\Integrations;

use Send_App\Core\Integrations\Classes\Forms\Form_View_Base;
use Send_App\Modules\Gravityforms\Classes\Forms_Data_Helper;
use Send_App\Modules\Gravityforms\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Form_View extends Form_View_Base {
	const FORM_TRACKER_SCRIPT_HANDLE = 'send-app-gravityforms-form-tracker';
	const AJAX_ACTION_VIEWED = 'send_app_gravityforms_form_viewed';
	const AJAX_ACTION_ABANDONED = 'send_app_gravityforms_form_abandoned';

	protected function is_form_disabled( $form_id ): bool {
		/** @var Module $module */
		$module = Module::get_instance();

		/** @var \Send_App\Modules\Gravityforms\Components\Forms $forms_component */
		$forms_component = $module->get_component( 'Forms' );

		return $forms_component->is_disabled_form( $form_id );
	}

	protected function get_id_prefix(): string {
		return Forms_Data_Helper::FORM_ID_PREFIX;
	}

	protected function get_js_object_name(): string {
		return 'eSendGravityformsSettings';
	}

	protected function get_form_selectors(): array {
		return [ 'form[data-formid]' ];
	}

	protected function get_script_relative_path(): string {
		return 'js/send-app-gravityforms-form-tracker.js';
	}

	protected function get_init_hook_name(): string {
		return 'gform_loaded';
	}

	public function get_integration_name(): string {
		return Module::get_name();
	}

	protected function get_script_depends(): array {
		return [ 'gform_gravityforms' ];
	}
}
