import FormFeEvents from "../../../../core/assets/js/form-fe-events";

declare const eSendElementorFormsSettings: {
	observer: MutationObserver | null;
	formSelectors: string[];
	ajaxUrl: string;
	nonce: string;
	viewedThreshold: number;
	viewedAction: string;
	abandonedAction: string;
	debugOn: boolean;
	idPrefix: string;
};

export default class ElementorFormFeEvents extends FormFeEvents {
	init() {
		this.formSelectors = eSendElementorFormsSettings.formSelectors;
		this.ajaxUrl= eSendElementorFormsSettings.ajaxUrl;
		this.nonce = eSendElementorFormsSettings.nonce;
		this.viewedThreshold = eSendElementorFormsSettings.viewedThreshold;
		this.viewedAction = eSendElementorFormsSettings.viewedAction;
		this.abandonedAction = eSendElementorFormsSettings.abandonedAction;
		this.debugOn = eSendElementorFormsSettings.debugOn;
		this.idPrefix = eSendElementorFormsSettings.idPrefix;

		this.abandonedEvents = [ 'elementor/popup/hide', ...this.abandonedEvents ];
	}

	getFormId(form: Element): string {
		return form.querySelector( 'input[name="form_id"]' )?.getAttribute( 'value' ) ||
			( form.classList.contains( 'elementor-login' ) ? 'login' : '' );
	}

	getPostId(form: Element): string {
		return form.querySelector( 'input[name="post_id"]' )?.getAttribute( 'value' ) || '';
	}
}

window.addEventListener( 'elementor/frontend/init', () => {
	new ElementorFormFeEvents();
} );
