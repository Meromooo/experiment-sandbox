/**
 * Request Form — the Contact page's request (AMM-169, step 2).
 *
 * Enhances the markup in render.php:
 *
 *  - switches on the Send button (disabled without JavaScript, since nothing
 *    can receive the form yet) and takes over checking the fields, so the
 *    messages are the form's own and say how to fix each one;
 *  - keeps the branch in step with the Branch Finder both ways, through the
 *    demas-theme:branch event on document, and names it on the button
 *    ("Send to the Jeddah branch");
 *  - changes the message's hint with what the buyer needs;
 *  - shows the buyer's quote list — the one the header's Your quote keeps
 *    in this browser — with a box to include it, whenever it has parts;
 *  - on a complete form, posts it to inc/request.php (AMM-140) as JSON, with
 *    the quote list as { id, qty } when it is included (the server reads
 *    each part from the store), and shows what came back: sent (with the
 *    reference, and the typed fields cleared), not connected (until the
 *    Microsoft 365 settings exist), or not sent for anything else. Those
 *    two offer head office's number and the branch's directions, and keep
 *    everything typed. The panel takes focus.
 *
 * A wrong or missing field is marked on the field (aria-invalid, with its
 * message tied to it by aria-describedby) and listed in a summary at the top
 * that takes focus; each line there moves focus to its field. After a first
 * try, fields re-check as the buyer types.
 */

interface Strings {
	send: string;
	checkOne: string;
	checkMany: string;
	errors: Record< string, string >;
	required: string;
	optional: string;
	addHint: string;
	sending: string;
	sentTitle: string;
	sent: string;
	failedTitle: string;
	failed: string;
	notSentTitle: string;
	notSent: string;
	call: string;
	directions: string;
	directionsTo: string;
	partOne: string;
	partMany: string;
	more: string;
}

interface Part {
	id: number;
	qty: number;
	name: string;
	sku: string;
}

interface BranchEvent {
	code: string;
	source: string;
}

type Control = HTMLInputElement | HTMLTextAreaElement;

/** What inc/request.php answers: a ref when sent, a code when not. */
interface Reply {
	ref?: string;
	code?: string;
}

/** The quote list's key, as src/quote-drawer/view.ts writes it. */
const QUOTE_KEY = 'demas-theme/quote';

/** Fields in the order they are on the page, and so in the summary. */
const ORDER = [ 'name', 'phone', 'email', 'message' ];

function format( template: string, ...values: string[] ): string {
	let next = 0;
	return template.replace( /%(?:(\d)\$)?[sd]/g, ( _match, position ) =>
		String( values[ position ? Number( position ) - 1 : next++ ] ?? '' )
	);
}

/** The quote list, or nothing if storage is blocked or the value unreadable. */
function readQuote(): Part[] {
	try {
		const parsed: unknown = JSON.parse(
			window.localStorage.getItem( QUOTE_KEY ) || '[]'
		);
		return Array.isArray( parsed )
			? parsed.filter(
					( item ): item is Part =>
						!! item &&
						typeof item.name === 'string' &&
						typeof item.qty === 'number'
			  )
			: [];
	} catch {
		return [];
	}
}

function element< T extends keyof HTMLElementTagNameMap >(
	tag: T,
	className: string,
	text = ''
): HTMLElementTagNameMap[ T ] {
	const node = document.createElement( tag );
	node.className = className;
	node.textContent = text;
	return node;
}

function init( form: HTMLFormElement ): void {
	const strings = JSON.parse( form.dataset.strings || '{}' ) as Strings;
	const select = form.querySelector< HTMLSelectElement >(
		'[data-branch-select]'
	);
	const send = form.querySelector< HTMLButtonElement >( '[data-send]' );
	const status = form.querySelector< HTMLElement >( '[data-send-status]' );
	const summary = form.querySelector< HTMLElement >( '[data-summary]' );
	const summaryTitle = form.querySelector< HTMLElement >(
		'[data-summary-title]'
	);
	const summaryList = form.querySelector< HTMLElement >(
		'[data-summary-list]'
	);
	const hint = form.querySelector< HTMLElement >( '[data-need-hint]' );
	const quote = form.querySelector< HTMLElement >( '[data-quote]' );
	const quoteCount = form.querySelector< HTMLElement >( '[data-quote-count]' );
	const quoteLines = form.querySelector< HTMLElement >( '[data-quote-lines]' );
	const controls = new Map< string, Control >();

	form.querySelectorAll< Control >( '[data-field]' ).forEach( ( control ) =>
		controls.set( control.dataset.field ?? '', control )
	);

	if ( ! select || ! send || ! status || ! summary ) {
		return;
	}

	let tried = false;

	form.noValidate = true;
	send.disabled = false;

	const option = () => select.selectedOptions[ 0 ];

	const name = () => {
		send.textContent = format( strings.send, option()?.dataset.city ?? '' );
	};

	const clearStatus = () => {
		status.replaceChildren();
		status.classList.remove( 'is-shown' );
	};

	/* The branch -------------------------------------------------------- */

	const choose = ( code: string ) => {
		if (
			select.value !== code &&
			Array.from( select.options ).some( ( item ) => item.value === code )
		) {
			select.value = code;
			name();
			clearStatus();
		}
	};

	select.addEventListener( 'change', () => {
		name();
		clearStatus();
		document.dispatchEvent(
			new CustomEvent< BranchEvent >( 'demas-theme:branch', {
				detail: { code: select.value, source: 'form' },
			} )
		);
	} );

	document.addEventListener( 'demas-theme:branch', ( event ) => {
		const { code, source } = ( event as CustomEvent< BranchEvent > ).detail;
		if ( 'form' !== source ) {
			choose( code );
		}
	} );

	// The finder may have chosen a branch (a #branch-xxx link) before this ran.
	const finder = document.querySelector< HTMLElement >(
		'[data-branch-finder]'
	);
	if ( finder?.dataset.current ) {
		choose( finder.dataset.current );
	}
	name();

	/* What the buyer needs ---------------------------------------------- */

	const include = form.querySelector< HTMLInputElement >(
		'input[name="quote"]'
	);
	const messageTag = form.querySelector< HTMLElement >(
		'[data-message-tag]'
	);
	const need = () =>
		form.querySelector< HTMLInputElement >( 'input[name="need"]:checked' );

	/** Parts & prices with the quote list included: the list is the request. */
	const carried = () =>
		'parts' === need()?.value &&
		!! include?.checked &&
		!! quote &&
		! quote.hidden;

	/** The message and its hint follow the need; the message is optional when the list carries the request. */
	const syncMessage = () => {
		const optional = carried();
		controls.get( 'message' )?.toggleAttribute( 'required', ! optional );
		if ( messageTag ) {
			messageTag.textContent = optional
				? strings.optional
				: strings.required;
			messageTag.classList.toggle( 'is-optional', optional );
		}
		if ( hint ) {
			hint.textContent = optional
				? strings.addHint
				: need()?.dataset.hint ?? '';
		}
		if ( tried ) {
			check();
		}
	};

	form.querySelectorAll< HTMLInputElement >( 'input[name="need"]' ).forEach(
		( radio ) => radio.addEventListener( 'change', syncMessage )
	);
	include?.addEventListener( 'change', syncMessage );

	/* The quote list ---------------------------------------------------- */

	const drawQuote = () => {
		if ( ! quote || ! quoteCount || ! quoteLines ) {
			return;
		}
		const parts = readQuote();
		quote.hidden = 0 === parts.length;
		syncMessage();
		if ( ! parts.length ) {
			return;
		}
		quoteCount.textContent = `(${
			1 === parts.length
				? strings.partOne
				: format( strings.partMany, String( parts.length ) )
		})`;
		const lines = parts
			.slice( 0, 3 )
			.map( ( part ) =>
				element(
					'li',
					'dh-ct-quote__line',
					`${ part.qty } × ${ part.name }${
						part.sku ? ` · ${ part.sku }` : ''
					}`
				)
			);
		if ( parts.length > 3 ) {
			lines.push(
				element(
					'li',
					'dh-ct-quote__more',
					format( strings.more, String( parts.length - 3 ) )
				)
			);
		}
		quoteLines.replaceChildren( ...lines );
	};

	drawQuote();
	// Another tab changed it, or the buyer closed the Your quote list here.
	window.addEventListener( 'storage', ( event ) => {
		if ( event.key === QUOTE_KEY || event.key === null ) {
			drawQuote();
		}
	} );
	document.addEventListener( 'close', drawQuote, true );

	/* Checking ---------------------------------------------------------- */

	const problem = ( field: string ): string => {
		const control = controls.get( field );
		const value = control?.value.trim() ?? '';

		switch ( field ) {
			case 'name':
				return value ? '' : strings.errors.name;
			case 'message':
				return value || carried() ? '' : strings.errors.message;
			case 'phone': {
				if ( ! value ) {
					return strings.errors.phone;
				}
				const digits = value.replace( /\D/g, '' ).length;
				return /^[+\d\s()-]+$/.test( value ) && digits >= 9 && digits <= 15
					? ''
					: strings.errors.phoneBad;
			}
			case 'email':
				return ! value ||
					( control as HTMLInputElement ).validity.valid
					? ''
					: strings.errors.email;
		}
		return '';
	};

	const mark = ( field: string, message: string ) => {
		const control = controls.get( field );
		const error = form.querySelector< HTMLElement >(
			`[data-error-for="${ field }"]`
		);
		control?.setAttribute( 'aria-invalid', message ? 'true' : 'false' );
		if ( error ) {
			error.textContent = message;
			error.hidden = ! message;
		}
	};

	/** Marks every field and fills the summary; returns the problems. */
	const check = (): Array< [ string, string ] > => {
		const problems: Array< [ string, string ] > = [];
		ORDER.forEach( ( field ) => {
			const message = problem( field );
			mark( field, message );
			if ( message ) {
				problems.push( [ field, message ] );
			}
		} );

		summary.hidden = 0 === problems.length;
		if ( summaryTitle ) {
			summaryTitle.textContent =
				1 === problems.length
					? strings.checkOne
					: format( strings.checkMany, String( problems.length ) );
		}
		summaryList?.replaceChildren(
			...problems.map( ( [ field, message ] ) => {
				const item = element( 'li', '' );
				const link = element( 'a', '', message );
				link.href = `#${ controls.get( field )?.id ?? '' }`;
				link.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					controls.get( field )?.focus();
				} );
				item.append( link );
				return item;
			} )
		);

		return problems;
	};

	controls.forEach( ( control ) =>
		control.addEventListener( 'input', () => {
			clearStatus();
			if ( tried ) {
				check();
			}
		} )
	);

	/* Sending ----------------------------------------------------------- */

	const value = ( field: string ) =>
		( form.elements.namedItem( field ) as Control | null )?.value ?? '';

	/** Posts the form; the HTTP status and the reply, or 0 when nothing came back. */
	const post = async (): Promise< [ number, Reply ] > => {
		let code = 0;
		let reply: Reply = {};
		try {
			const response = await fetch( form.dataset.endpoint ?? '', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify( {
					need: need()?.value ?? '',
					branch: select.value,
					name: value( 'name' ),
					phone: value( 'phone' ),
					email: value( 'email' ),
					company: value( 'company' ),
					message: value( 'message' ),
					items:
						include?.checked && quote && ! quote.hidden
							? readQuote().map( ( { id, qty } ) => ( { id, qty } ) )
							: [],
				} ),
			} );
			code = response.status;
			reply = await response.json();
		} catch {
			// Offline, or a reply that isn't JSON: the code says which.
		}
		return [ code, reply ];
	};

	/** Shows a result under Send and moves focus to it. */
	const show = ( title: string, text: string, help: boolean ) => {
		status.replaceChildren(
			element( 'p', 'dh-ct-form__status-title', title ),
			element( 'p', 'dh-ct-form__status-text', text )
		);
		if ( help ) {
			status.append( helpLinks() );
		}
		status.classList.add( 'is-shown' );
		status.focus();
	};

	/** Head office's number, and the branch's directions when it has a map link. */
	const helpLinks = () => {
		const city = option()?.dataset.city ?? '';
		const map = option()?.dataset.map ?? '';
		const actions = element( 'p', 'dh-ct-form__status-actions' );
		const call = element( 'a', 'dh-pill dh-pill--outline', strings.call );
		call.href = 'tel:+966114634102';
		actions.append( call );

		if ( map ) {
			// The same label as the branch card's link; the branch is in the
			// screen-reader text.
			const directions = element(
				'a',
				'dh-pill dh-pill--outline',
				strings.directions
			);
			directions.href = map;
			directions.target = '_blank';
			directions.rel = 'noopener noreferrer';
			directions.append(
				element(
					'span',
					'screen-reader-text',
					` ${ format( strings.directionsTo, city ) }`
				)
			);
			actions.append( directions );
		}

		return actions;
	};

	let sending = false;

	form.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();
		// A second press while the first is on its way would send twice.
		if ( sending ) {
			return;
		}
		tried = true;
		clearStatus();

		if ( check().length ) {
			summary.focus();
			return;
		}

		sending = true;
		send.textContent = strings.sending;
		const [ code, reply ] = await post();
		sending = false;
		name();

		if ( 200 === code ) {
			[ 'name', 'phone', 'email', 'company', 'message' ].forEach(
				( field ) => {
					( form.elements.namedItem( field ) as Control ).value = '';
				}
			);
			tried = false;
			show(
				format( strings.sentTitle, option()?.dataset.city ?? '' ),
				format( strings.sent, reply.ref ?? '' ),
				false
			);
		} else if ( 'not_connected' === reply.code ) {
			show( strings.notSentTitle, strings.notSent, true );
		} else {
			show( strings.failedTitle, strings.failed, true );
		}
	} );
}

document
	.querySelectorAll< HTMLFormElement >( '[data-request-form]' )
	.forEach( init );

// A module, not a script: its names stay its own.
export {};
