<?php
/**
 * EU harmonised notice on the legal guarantee of conformity for WooCommerce
 * (Implementing Regulation (EU) 2025/1960), with the Spanish three-year note.
 *
 * - Shows the official notice right above the "Place order" button (classic and block checkout).
 * - Adds it to the customer confirmation emails, with the official PDF attached.
 * - Languages without an official notice use the official one of their country (Catalan, Basque
 *   and Galician use Spanish) and anything else uses the fallback language.
 * - Adds an order note recording which notice was shown at checkout.
 *
 * Upload the official COLOUR PNG and the PDF from the European Commission to the Media Library
 * and write their attachment IDs in ayudawp_gl_settings().
 *
 * @author  AyudaWP.com
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Snippet settings. Edit the values here.
 *
 * @return array
 */
function ayudawp_gl_settings() {
	return array(
		// Attachment IDs of the official colour PNG, by two-letter language code.
		'images'       => array(
			'es' => 0,
		),
		// Attachment IDs of the official PDF, by language. Attached to the confirmation email.
		'pdfs'         => array(
			'es' => 0,
		),
		// Languages without an official notice and the official language they use instead.
		'languages'    => array(
			'ca' => 'es',
			'eu' => 'es',
			'gl' => 'es',
			'lb' => 'fr',
			'cy' => 'en',
			'gd' => 'en',
		),
		// Language used when there is no file for the site language.
		'fallback'     => 'es',
		// Modules. Switch off whatever your plugin already does.
		'checkout'     => true,  // Notice above the "Place order" button.
		'email'        => true,  // Notice inside the customer confirmation emails.
		'pdf'          => true,  // Official PDF attached to those emails.
		'note'         => true,  // Spanish three-year note (only if the store is based in Spain).
		'record'       => true,  // Order note saying which notice was shown at checkout.
		// 'full': the whole notice. 'details': a line that expands it below.
		// 'popover': a button that opens it large over the page (best for narrow checkout columns).
		'display'      => 'full',
		// Customer emails that get the notice and the PDF.
		'email_ids'    => array( 'customer_on_hold_order', 'customer_processing_order' ),
		// Anchor (id) of the legal guarantee section in your terms and conditions page.
		'terms_anchor' => 'garantia-legal',
	);
}

/**
 * Language of the notice: the site language (or the official language that replaces it)
 * if there is a file for it, otherwise the fallback.
 *
 * @return string
 */
function ayudawp_gl_lang() {
	$settings = ayudawp_gl_settings();
	$lang     = strtolower( substr( get_locale(), 0, 2 ) );

	if ( isset( $settings['languages'][ $lang ] ) ) {
		$lang = $settings['languages'][ $lang ];
	}

	return empty( $settings['images'][ $lang ] ) ? $settings['fallback'] : $lang;
}

/**
 * Attachment ID of an official file.
 *
 * @param string $type 'images' or 'pdfs'.
 * @param string $lang Two-letter language code.
 * @return int
 */
function ayudawp_gl_file_id( $type, $lang ) {
	$settings = ayudawp_gl_settings();
	return isset( $settings[ $type ][ $lang ] ) ? absint( $settings[ $type ][ $lang ] ) : 0;
}

/**
 * True if there is a valid notice image for the current language.
 *
 * @return bool
 */
function ayudawp_gl_has_notice() {
	$image_id = ayudawp_gl_file_id( 'images', ayudawp_gl_lang() );
	return $image_id && wp_attachment_is_image( $image_id );
}

/**
 * Path of the official PDF, only if it is a real PDF inside the uploads folder.
 *
 * @param string $lang Two-letter language code.
 * @return string Empty string when there is no valid file.
 */
function ayudawp_gl_pdf_path( $lang ) {
	$pdf_id = ayudawp_gl_file_id( 'pdfs', $lang );
	if ( ! $pdf_id || 'application/pdf' !== get_post_mime_type( $pdf_id ) ) {
		return '';
	}

	$file    = get_attached_file( $pdf_id );
	$path    = $file ? realpath( $file ) : false;
	$uploads = wp_get_upload_dir();
	$base    = realpath( $uploads['basedir'] );

	if ( ! $path || ! $base || 0 !== strpos( wp_normalize_path( $path ), trailingslashit( wp_normalize_path( $base ) ) ) ) {
		return '';
	}
	if ( 'pdf' !== strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) || ! is_readable( $path ) ) {
		return '';
	}
	return $path;
}

/**
 * Same destination as the QR code printed on the official notice.
 *
 * @param string $lang Two-letter language code.
 * @return string
 */
function ayudawp_gl_youreurope_url( $lang ) {
	return 'https://europa.eu/youreurope/citizens/consumers/shopping/guarantees-returns/index_' . sanitize_key( $lang ) . '.htm';
}

/**
 * True if the cart contains at least one physical product (the notice is about goods).
 *
 * @return bool
 */
function ayudawp_gl_cart_has_goods() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( isset( $item['data'] ) && $item['data'] instanceof WC_Product && ! $item['data']->is_virtual() ) {
			return true;
		}
	}
	return false;
}

/**
 * True if the order contains at least one physical product.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function ayudawp_gl_order_has_goods( $order ) {
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product && ! $product->is_virtual() ) {
			return true;
		}
	}
	return false;
}

/**
 * Spanish note with the three-year period and a link to the terms page.
 *
 * @param bool $link Whether to link the terms page.
 * @return string Plain text when $link is false, HTML otherwise.
 */
function ayudawp_gl_note( $link = true ) {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['note'] || ! function_exists( 'WC' ) || ! WC()->countries || 'ES' !== WC()->countries->get_base_country() ) {
		return '';
	}

	$text = __( 'En España, la garantía legal de los bienes nuevos es de tres años desde la entrega.', 'ayudawp' );

	if ( ! $link ) {
		return $text;
	}

	$html     = esc_html( $text );
	$terms_id = wc_terms_and_conditions_page_id();
	if ( $terms_id ) {
		$html .= sprintf(
			' <a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
			esc_url( get_permalink( $terms_id ) . '#' . $settings['terms_anchor'] ),
			esc_html__( 'Consulta todas las condiciones', 'ayudawp' )
		);
	}
	return $html;
}

/**
 * Notice markup for the checkout.
 *
 * @return string
 */
function ayudawp_gl_notice_html() {
	if ( ! ayudawp_gl_has_notice() ) {
		return '';
	}

	$settings = ayudawp_gl_settings();
	$lang     = ayudawp_gl_lang();
	$image_id = ayudawp_gl_file_id( 'images', $lang );
	$src      = wp_get_attachment_url( $image_id );
	$meta     = wp_get_attachment_metadata( $image_id );

	$figure = sprintf(
		'<figure class="ayudawp-gl__aviso"><a href="%1$s" target="_blank" rel="noopener"><img src="%1$s" width="%2$d" height="%3$d" alt="%4$s" loading="lazy"></a><figcaption><a href="%5$s" target="_blank" rel="noopener">%6$s</a></figcaption></figure>',
		esc_url( $src ),
		isset( $meta['width'] ) ? (int) $meta['width'] : 0,
		isset( $meta['height'] ) ? (int) $meta['height'] : 0,
		esc_attr__( 'Aviso armonizado de la Unión Europea sobre la garantía legal de conformidad', 'ayudawp' ),
		esc_url( ayudawp_gl_youreurope_url( $lang ) ),
		esc_html__( 'Más información sobre tus derechos de garantía en la web de la Comisión Europea', 'ayudawp' )
	);

	if ( 'details' === $settings['display'] ) {
		$figure = sprintf(
			'<details><summary>%1$s</summary>%2$s</details>',
			esc_html__( 'Consulta tus derechos de garantía legal', 'ayudawp' ),
			$figure
		);
	} elseif ( 'popover' === $settings['display'] ) {
		$figure = sprintf(
			'<button type="button" class="ayudawp-gl__abrir" popovertarget="ayudawp-gl-aviso">%1$s</button><div id="ayudawp-gl-aviso" class="ayudawp-gl__popover" popover><button type="button" class="ayudawp-gl__cerrar" popovertarget="ayudawp-gl-aviso" popovertargetaction="hide" autofocus>%3$s</button>%2$s</div>',
			esc_html__( 'Consulta tus derechos de garantía legal', 'ayudawp' ),
			$figure,
			esc_html__( 'Cerrar', 'ayudawp' )
		);
	}

	$note = ayudawp_gl_note();

	return '<div class="ayudawp-gl">' . $figure . ( $note ? '<p class="ayudawp-gl__nota">' . $note . '</p>' : '' ) . '</div>';
}

/**
 * Allowed HTML: post content plus the native popover attributes.
 *
 * @return array
 */
function ayudawp_gl_allowed_html() {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['div']['popover']                = true;
	$allowed['button']['popovertarget']       = true;
	$allowed['button']['popovertargetaction'] = true;
	$allowed['button']['autofocus']           = true;

	return $allowed;
}

/**
 * Classic checkout: right above the "Place order" button.
 */
function ayudawp_gl_classic_checkout() {
	$settings = ayudawp_gl_settings();
	if ( $settings['checkout'] && ayudawp_gl_cart_has_goods() ) {
		echo wp_kses( ayudawp_gl_notice_html(), ayudawp_gl_allowed_html() );
	}
}
add_action( 'woocommerce_review_order_before_submit', 'ayudawp_gl_classic_checkout' );

/**
 * Block checkout: prepended to the Actions block, so it lands right above the button.
 *
 * @param string $block_content Block HTML.
 * @return string
 */
function ayudawp_gl_block_checkout( $block_content ) {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['checkout'] || ! ayudawp_gl_cart_has_goods() ) {
		return $block_content;
	}
	return wp_kses( ayudawp_gl_notice_html(), ayudawp_gl_allowed_html() ) . $block_content;
}
add_filter( 'render_block_woocommerce/checkout-actions-block', 'ayudawp_gl_block_checkout' );

/**
 * Minimal styles, only on the checkout page.
 */
function ayudawp_gl_styles() {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['checkout'] || ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}
	wp_register_style( 'ayudawp-gl', false, array(), '1.0' );
	wp_enqueue_style( 'ayudawp-gl' );
	wp_add_inline_style(
		'ayudawp-gl',
		'.ayudawp-gl{margin:1em 0}.ayudawp-gl__aviso{margin:0;max-width:640px}.ayudawp-gl__aviso img{display:block;width:100%;height:auto}.ayudawp-gl__aviso figcaption,.ayudawp-gl__nota{font-size:.875em;margin:.5em 0 0}.ayudawp-gl summary{cursor:pointer;font-weight:600}.ayudawp-gl__popover{width:min(92vw,820px);max-height:92vh;overflow:auto;padding:1em;border:0;background:#fff}.ayudawp-gl__popover::backdrop{background:rgba(0,0,0,.6)}.ayudawp-gl__popover .ayudawp-gl__aviso{max-width:none}.ayudawp-gl__cerrar{display:block;margin:0 0 1em auto}'
	);
}
add_action( 'wp_enqueue_scripts', 'ayudawp_gl_styles' );

/**
 * Customer confirmation emails: notice image linked to the full-size file, plus text version.
 *
 * @param WC_Order $order         Order.
 * @param bool     $sent_to_admin Whether the email goes to the admin.
 * @param bool     $plain_text    Plain text email.
 * @param WC_Email $email         Email object.
 */
function ayudawp_gl_email( $order, $sent_to_admin, $plain_text, $email ) {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['email'] || $sent_to_admin || ! $order instanceof WC_Order || ! $email instanceof WC_Email ) {
		return;
	}
	if ( ! in_array( $email->id, $settings['email_ids'], true ) || ! ayudawp_gl_order_has_goods( $order ) || ! ayudawp_gl_has_notice() ) {
		return;
	}

	$lang = ayudawp_gl_lang();
	$src  = wp_get_attachment_url( ayudawp_gl_file_id( 'images', $lang ) );
	if ( ! $src ) {
		return;
	}

	if ( $plain_text ) {
		echo "\n" . esc_html( __( 'Tus derechos de garantía legal', 'ayudawp' ) ) . "\n\n";
		echo esc_html__( 'Aviso oficial de la UE:', 'ayudawp' ) . ' ' . esc_url( $src ) . "\n";
		echo esc_html__( 'Más información:', 'ayudawp' ) . ' ' . esc_url( ayudawp_gl_youreurope_url( $lang ) ) . "\n";
		$note = ayudawp_gl_note( false );
		if ( $note ) {
			echo esc_html( $note ) . "\n";
		}
		return;
	}

	$html  = '<h2>' . esc_html__( 'Tus derechos de garantía legal', 'ayudawp' ) . '</h2>';
	$html .= sprintf(
		'<p><a href="%1$s" target="_blank"><img src="%1$s" width="500" alt="%2$s" style="display:block;width:500px;max-width:100%%;height:auto;border:0"></a></p>',
		esc_url( $src ),
		esc_attr__( 'Aviso armonizado de la Unión Europea sobre la garantía legal de conformidad', 'ayudawp' )
	);
	$html .= sprintf(
		'<p><a href="%1$s" target="_blank">%2$s</a></p>',
		esc_url( ayudawp_gl_youreurope_url( $lang ) ),
		esc_html__( 'Más información sobre tus derechos de garantía en la web de la Comisión Europea', 'ayudawp' )
	);
	$note = ayudawp_gl_note();
	if ( $note ) {
		$html .= '<p>' . $note . '</p>';
	}

	echo wp_kses_post( $html );
}
add_action( 'woocommerce_email_after_order_table', 'ayudawp_gl_email', 20, 4 );

/**
 * Attach the official PDF to the same customer emails.
 *
 * The filter passes four arguments; the third one is the email's object, which is only
 * an order in order emails, so it is checked before use. The fourth (email) is not needed.
 *
 * @param array  $attachments Attachment paths.
 * @param string $email_id    Email ID.
 * @param mixed  $object      Email object: the order in order emails, something else in the rest.
 * @return array
 */
function ayudawp_gl_email_pdf( $attachments, $email_id, $object ) {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['pdf'] || ! $object instanceof WC_Order || ! in_array( $email_id, $settings['email_ids'], true ) ) {
		return $attachments;
	}
	if ( ! ayudawp_gl_order_has_goods( $object ) ) {
		return $attachments;
	}

	$path = ayudawp_gl_pdf_path( ayudawp_gl_lang() );
	if ( $path ) {
		$attachments[] = $path;
	}
	return $attachments;
}
add_filter( 'woocommerce_email_attachments', 'ayudawp_gl_email_pdf', 10, 3 );

/**
 * Order note recording which notice was shown at checkout (classic and block checkout).
 *
 * Both checkouts reuse the same order and run these hooks again when the customer retries
 * a failed payment, so the note is written only once per order.
 *
 * @param WC_Order $order Order.
 */
function ayudawp_gl_record( $order ) {
	$settings = ayudawp_gl_settings();
	if ( ! $settings['record'] || ! $settings['checkout'] || ! $order instanceof WC_Order ) {
		return;
	}
	if ( $order->get_meta( '_ayudawp_gl_notice_shown' ) || ! ayudawp_gl_order_has_goods( $order ) || ! ayudawp_gl_has_notice() ) {
		return;
	}

	$lang = ayudawp_gl_lang();

	$order->add_order_note(
		sprintf(
			/* translators: 1: language code, 2: display mode */
			__( 'Aviso armonizado de garantía legal de la UE mostrado en el checkout (idioma: %1$s, modo: %2$s).', 'ayudawp' ),
			strtoupper( $lang ),
			$settings['display']
		)
	);
	$order->update_meta_data( '_ayudawp_gl_notice_shown', $lang );
	$order->save_meta_data();
}
add_action( 'woocommerce_checkout_order_created', 'ayudawp_gl_record' );
add_action( 'woocommerce_store_api_checkout_order_processed', 'ayudawp_gl_record' );