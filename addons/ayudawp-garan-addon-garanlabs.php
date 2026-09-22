<?php
/**
 * Add-on for "EU Legal-Guarantee Notice & GARAN Durability Label for WooCommerce" (GaranLabs).
 *
 * - Uses the Spanish notice on Catalan, Basque and Galician sites (with the plugin language on "Automatic").
 * - Shows the plugin's notice right above the "Place order" button (classic and block checkout).
 * - Adds the Spanish three-year note below it.
 *
 * In the plugin settings, keep the checkout placement off so the notice doesn't show twice.
 *
 * @author  AyudaWP.com
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Spanish notice instead of the English fallback on ca/eu/gl sites.
 *
 * @param mixed $value Stored plugin language ("auto" by default).
 * @return mixed
 */
function ayudawp_eggl_locale( $value ) {
	if ( 'auto' === $value && in_array( strtolower( substr( get_locale(), 0, 2 ) ), array( 'ca', 'eu', 'gl' ), true ) ) {
		return 'es-ES';
	}
	return $value;
}
add_filter( 'option_eggl_locale', 'ayudawp_eggl_locale' );
add_filter( 'default_option_eggl_locale', 'ayudawp_eggl_locale' );

/**
 * True if the cart contains at least one physical product.
 *
 * @return bool
 */
function ayudawp_eggl_cart_has_goods() {
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
 * Notice from the plugin shortcode plus the Spanish note.
 *
 * @return string
 */
function ayudawp_eggl_checkout_html() {
	if ( ! shortcode_exists( 'eu_guarantee_notice' ) || ! ayudawp_eggl_cart_has_goods() ) {
		return '';
	}

	$html = do_shortcode( '[eu_guarantee_notice]' );
	if ( '' === trim( $html ) ) {
		return '';
	}

	$note = '';
	if ( 'ES' === WC()->countries->get_base_country() ) {
		$note     = esc_html__( 'En España, la garantía legal de los bienes nuevos es de tres años desde la entrega.', 'ayudawp' );
		$terms_id = wc_terms_and_conditions_page_id();
		if ( $terms_id ) {
			$note .= sprintf(
				' <a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
				esc_url( get_permalink( $terms_id ) . '#garantia-legal' ),
				esc_html__( 'Consulta todas las condiciones', 'ayudawp' )
			);
		}
		$note = '<p class="ayudawp-gl__nota">' . $note . '</p>';
	}

	return '<div class="ayudawp-gl">' . $html . $note . '</div>';
}

/**
 * Allowed HTML: post content plus the small SVG icons the plugin uses.
 *
 * @return array
 */
function ayudawp_eggl_allowed_html() {
	return array_merge(
		wp_kses_allowed_html( 'post' ),
		array(
			'svg'  => array( 'width' => true, 'height' => true, 'viewbox' => true, 'xmlns' => true, 'aria-hidden' => true ),
			'path' => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
		)
	);
}

/**
 * Classic checkout: right above the "Place order" button.
 */
function ayudawp_eggl_classic_checkout() {
	echo wp_kses( ayudawp_eggl_checkout_html(), ayudawp_eggl_allowed_html() );
}
add_action( 'woocommerce_review_order_before_submit', 'ayudawp_eggl_classic_checkout' );

/**
 * Block checkout: prepended to the Actions block, right above the button.
 *
 * @param string $block_content Block HTML.
 * @return string
 */
function ayudawp_eggl_block_checkout( $block_content ) {
	return wp_kses( ayudawp_eggl_checkout_html(), ayudawp_eggl_allowed_html() ) . $block_content;
}
add_filter( 'render_block_woocommerce/checkout-actions-block', 'ayudawp_eggl_block_checkout' );

/**
 * Minimal style for the note on the checkout.
 */
function ayudawp_eggl_styles() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}
	wp_register_style( 'ayudawp-eggl', false, array(), '1.0' );
	wp_enqueue_style( 'ayudawp-eggl' );
	wp_add_inline_style( 'ayudawp-eggl', '.ayudawp-gl{margin:1em 0}.ayudawp-gl__nota{font-size:.875em;margin:.5em 0 0}' );
}
add_action( 'wp_enqueue_scripts', 'ayudawp_eggl_styles' );
