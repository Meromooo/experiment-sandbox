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
 *  - on a complete form, says plainly that sending is not connected yet and
 *    offers head office's number and the branch's directions. Nothing is
 *    sent and nothing typed is cleared. AMM-140 replaces that step with the
 *    real send (and its sending, sent and failed states).
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
	notSentTitle: string;
	notSent: string;
	call: string;
	directions: string;
	newTab: string;
	partOne: string;
	partMany: string;
	more: string;
}

interface Part {
	qty: number;
	name: string;
	sku: string;
}

interface BranchEvent {
	code: string;
	source: string;
}

type Control = HTMLInputElement | HTMLTextAreaElement;

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

	form.querySelectorAll< HTMLInputElement >( 'input[name="need"]' ).forEach(
		( radio ) =>
			radio.addEventListener( 'change', () => {
				if ( hint ) {
					hint.textContent = radio.dataset.hint ?? '';
				}
			} )
	);

	/* The quote list ---------------------------------------------------- */

	const drawQuote = () => {
		if ( ! quote || ! quoteCount || ! quoteLines ) {
			return;
		}
		const parts = readQuote();
		quote.hidden = 0 === parts.length;
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
			case 'message':
				return value ? '' : strings.errors[ field ];
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

	/* Sending (not connected until AMM-140) ----------------------------- */

	const notConnected = () => {
		const city = option()?.dataset.city ?? '';
		const map = option()?.dataset.map ?? '';
		const actions = element( 'p', 'dh-ct-form__status-actions' );
		const call = element( 'a', 'dh-pill dh-pill--outline', strings.call );
		call.href = 'tel:+966114634102';
		actions.append( call );

		if ( map ) {
			const directions = element(
				'a',
				'dh-pill dh-pill--outline',
				format( strings.directions, city )
			);
			directions.href = map;
			directions.target = '_blank';
			directions.rel = 'noopener noreferrer';
			directions.append(
				element( 'span', 'screen-reader-text', ` ${ strings.newTab }` )
			);
			actions.append( directions );
		}

		status.replaceChildren(
			element( 'p', 'dh-ct-form__status-title', strings.notSentTitle ),
			element( 'p', 'dh-ct-form__status-text', strings.notSent ),
			actions
		);
		status.classList.add( 'is-shown' );
	};

	form.addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		tried = true;
		clearStatus();

		if ( check().length ) {
			summary.focus();
			return;
		}

		notConnected();
	} );
}

document
	.querySelectorAll< HTMLFormElement >( '[data-request-form]' )
	.forEach( init );

// A module, not a script: its names stay its own.
export {};
