<?php
namespace Send_App\Modules\WpForms;

use Send_App\Core\Integrations\Classes\Integration_Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends Integration_Module_Base {

	public static function get_name(): string {
		return 'wp-forms';
	}

	protected function integrations_list(): array {
		return [
			'Integration',
		];
	}

	public function is_plugin_activated(): bool {
		// TODO: Implement is_active() method.
		return false;
	}
}
