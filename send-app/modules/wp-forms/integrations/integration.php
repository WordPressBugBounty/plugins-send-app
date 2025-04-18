<?php
namespace Send_App\Modules\WpForms\Integrations;

use Send_App\Core\Integrations\Classes\Integration_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// TODO: Implement the integration with WPForms, consider extending Action_Scheduler_Sync_Base
class Integration extends Integration_Base {

	public function register_hooks(): void {
		//TODO
	}

	public function sync(): bool {
		return true;
	}
}
