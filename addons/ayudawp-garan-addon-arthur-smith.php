<?php
/**
 * Add-on for "Arthur Smith's EU Guarantee Notice and GARAN Label".
 *
 * - Shows the plugin's notice right above the "Place order" button (classic and block checkout).
 * - Uses the Spanish notice on Catalan, Basque and Galician sites.
 * - Adds the Spanish three-year note below it.
 *
 * In the plugin settings, keep the checkout placement unticked so the notice doesn't show twice.
 *
 * @author  AyudaWP.com
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * True if the cart contains at least one physical product.
 *
 * @return bool
 */
function ayudawp_eugn_cart_has_goods() {
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
 * Notice from the plugin plus the Spanish note.
 *
 * @return string
 */
function ayudawp_eugn_checkout_html() {
	if ( ! class_exists( '\EUGN\Renderer' ) || ! ayudawp_eugn_cart_has_goods() ) {
		return '';
	}

	$lang = strtolower( substr( get_locale(), 0, 2 ) );
	$args = in_array( $lang, array( 'ca', 'eu', 'gl' ), true ) ? array( 'language' => 'es' ) : array();
	$html = \EUGN\Renderer::render( $args );

	if ( '' === $html ) {
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
 * Classic checkout: right above the "Place order" button.
 */
function ayudawp_eugn_classic_checkout() {
	echo wp_kses_post( ayudawp_eugn_checkout_html() );
}
add_action( 'woocommerce_review_order_before_submit', 'ayudawp_eugn_classic_checkout' );

/**
 * Block checkout: prepended to the Actions block, right above the button.
 *
 * @param string $block_content Block HTML.
 * @return string
 */
function ayudawp_eugn_block_checkout( $block_content ) {
	return wp_kses_post( ayudawp_eugn_checkout_html() ) . $block_content;
}
add_filter( 'render_block_woocommerce/checkout-actions-block', 'ayudawp_eugn_block_checkout' );

/**
 * Load the plugin's stylesheet on the checkout (it only loads it where the plugin places the notice).
 */
function ayudawp_eugn_styles() {
	if ( ! class_exists( '\EUGN\Assets' ) || ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}
	\EUGN\Assets::enqueue();
	wp_add_inline_style( 'eugn-notice', '.ayudawp-gl{margin:1em 0}.ayudawp-gl__nota{font-size:.875em;margin:.5em 0 0}' );
}
add_action( 'wp_enqueue_scripts', 'ayudawp_eugn_styles' );
