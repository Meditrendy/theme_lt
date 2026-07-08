<?php
/**
 * Displayed when no products are found matching the current query.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_search() ) {
    echo '<div class="woocommerce-no-products-found">';
    wc_print_notice( esc_html__( 'No products were found matching your selection.', 'woocommerce' ), 'notice' );
    echo '</div>';
    return;
}

?>

<div class="woocommerce-no-products-found">
    <?php meditrendy_render_search_empty_state(); ?>
</div>
