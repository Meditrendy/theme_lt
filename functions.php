<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_stylesheet_directory() . '/modules/mobile-menu.php';

/**
 * Load parent + child styles and scripts
 */

add_action( 'wp_enqueue_scripts', 'meditrendy_child_styles' );

function meditrendy_child_styles() {
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_uri(),
        array('parent-style'),
        filemtime( get_stylesheet_directory() . '/style.css' )
    );

    $header_css_path = get_stylesheet_directory() . '/styles/header.css';

    if ( file_exists( $header_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-header',
            get_stylesheet_directory_uri() . '/styles/header.css',
            array('child-style'),
            filemtime( $header_css_path )
        );
    }

    $desktop_menu_js_path = get_stylesheet_directory() . '/scripts/desktop-menu.js';

    if ( file_exists( $desktop_menu_js_path ) ) {
        wp_enqueue_script(
            'meditrendy-desktop-menu',
            get_stylesheet_directory_uri() . '/scripts/desktop-menu.js',
            array(),
            filemtime( $desktop_menu_js_path ),
            true
        );
    }

    $side_cart_css_path = get_stylesheet_directory() . '/styles/side-cart.css';

    if ( file_exists( $side_cart_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-side-cart',
            get_stylesheet_directory_uri() . '/styles/side-cart.css',
            array('child-style'),
            filemtime( $side_cart_css_path )
        );
    }

    $categories_css_path = get_stylesheet_directory() . '/styles/categories.css';

    if ( file_exists( $categories_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-categories',
            get_stylesheet_directory_uri() . '/styles/categories.css',
            array('child-style'),
            filemtime( $categories_css_path )
        );
    }

    $blog_css_path = get_stylesheet_directory() . '/styles/blog.css';

    if ( file_exists( $blog_css_path ) && meditrendy_should_enqueue_blog_styles() ) {
        wp_enqueue_style(
            'meditrendy-blog',
            get_stylesheet_directory_uri() . '/styles/blog.css',
            array('child-style'),
            filemtime( $blog_css_path )
        );
    }

    $homepage_css_path = get_stylesheet_directory() . '/styles/homepage.css';

    if ( ( is_front_page() || is_page_template( 'template-cornerstone-canvas.php' ) ) && file_exists( $homepage_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-homepage',
            get_stylesheet_directory_uri() . '/styles/homepage.css',
            array('child-style'),
            filemtime( $homepage_css_path )
        );
    }

    $product_css_path = get_stylesheet_directory() . '/styles/product.css';

    if ( function_exists( 'is_product' ) && is_product() && file_exists( $product_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-product',
            get_stylesheet_directory_uri() . '/styles/product.css',
            array('child-style'),
            filemtime( $product_css_path )
        );
    }

    $buy_now_js_path = get_stylesheet_directory() . '/scripts/buy-now-pdp-button.js';

    if ( function_exists( 'is_product' ) && is_product() && file_exists( $buy_now_js_path ) ) {
        wp_enqueue_script(
            'meditrendy-buy-now-pdp-button',
            get_stylesheet_directory_uri() . '/scripts/buy-now-pdp-button.js',
            array( 'jquery' ),
            filemtime( $buy_now_js_path ),
            true
        );

        wp_localize_script(
            'meditrendy-buy-now-pdp-button',
            'MeditrendyBuyNowPdpButton',
            array(
                'labels' => array(
                    'selectSize' => 'Pasirinkite dydį prieš įdėdami prekę į krepšelį.',
                ),
            )
        );
    }

    $cart_shipping_css_path = get_stylesheet_directory() . '/styles/cart-shipping-loading.css';

    $is_cart_or_checkout = ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() );

    if ( $is_cart_or_checkout && file_exists( $cart_shipping_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-cart-shipping-loading',
            get_stylesheet_directory_uri() . '/styles/cart-shipping-loading.css',
            array( 'child-style' ),
            filemtime( $cart_shipping_css_path )
        );
    }

    $checkout_css_path = get_stylesheet_directory() . '/styles/checkout.css';

    if ( function_exists( 'is_checkout' ) && is_checkout() && file_exists( $checkout_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-checkout',
            get_stylesheet_directory_uri() . '/styles/checkout.css',
            array( 'child-style' ),
            filemtime( $checkout_css_path )
        );
    }

    $cart_shipping_js_path = get_stylesheet_directory() . '/scripts/cart-shipping-loading.js';

    if ( $is_cart_or_checkout && file_exists( $cart_shipping_js_path ) ) {
        wp_enqueue_script(
            'meditrendy-cart-shipping-loading',
            get_stylesheet_directory_uri() . '/scripts/cart-shipping-loading.js',
            array(),
            filemtime( $cart_shipping_js_path ),
            true
        );

        wp_localize_script(
            'meditrendy-cart-shipping-loading',
            'MeditrendyCartShippingLoading',
            array(
                'label' => 'Atnaujinamas pristatymas...',
            )
        );
    }
}

function meditrendy_should_enqueue_blog_styles() {
    if ( is_front_page() || is_home() || is_category() || is_tag() || is_date() || is_author() || is_singular( 'post' ) || is_page_template( 'template-blog-archive.php' ) ) {
        return true;
    }

    if ( ! is_singular() ) {
        return false;
    }

    $post = get_post();

    return $post && (
        has_shortcode( $post->post_content, 'meditrendy_blog_archive' ) ||
        has_shortcode( $post->post_content, 'meditrendy_blog_home' )
    );
}

/* =========================================
    product quantity 
   ========================================= */

add_action('wp_footer', function() {
?>
<script>
document.addEventListener("DOMContentLoaded", function() {

  document.querySelectorAll('.quantity').forEach(function(qtyBox){

      if(qtyBox.querySelector('.qty-btn')) return;

      const input = qtyBox.querySelector('input.qty');
      if(!input) return;

      const minus = document.createElement('button');
      minus.type = 'button';
      minus.className = 'qty-btn minus';
      minus.textContent = '\u2212';

      const plus = document.createElement('button');
      plus.type = 'button';
      plus.className = 'qty-btn plus';
      plus.textContent = '+';

      qtyBox.prepend(minus);
      qtyBox.appendChild(plus);

      minus.addEventListener('click', function(){
          let current = parseInt(input.value) || 1;
          if(current > 1){
              input.value = current - 1;
              input.dispatchEvent(new Event('change'));
          }
      });

      plus.addEventListener('click', function(){
          let current = parseInt(input.value) || 1;
          input.value = current + 1;
          input.dispatchEvent(new Event('change'));
      });

  });

});
</script>
<?php
});
function mt_preset_accordion() {

    $post_id = get_the_ID();
    $preset_id = get_field('preset', $post_id);

    if (!$preset_id) return '';

    $fabric_field   = get_field_object('fabric', $preset_id);
    $details_field  = get_field_object('details_fit', $preset_id);
    $delivery_field = get_field_object('delivery_info', $preset_id);

    ob_start();
    ?>

    <div class="mt-accordion">

        <?php if (!empty($details_field['value'])): ?>
        <details>
            <summary><?php echo esc_html($details_field['label']); ?></summary>
            <div class="acc-content">
                <?php echo $details_field['value']; ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if (!empty($fabric_field['value'])): ?>
        <details>
            <summary><?php echo esc_html($fabric_field['label']); ?></summary>
            <div class="acc-content">
                <?php echo $fabric_field['value']; ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if (!empty($delivery_field['value'])): ?>
        <details>
            <summary><?php echo esc_html($delivery_field['label']); ?></summary>
            <div class="acc-content">
                <?php echo $delivery_field['value']; ?>
            </div>
        </details>
        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('preset_accordion', 'mt_preset_accordion');
/* =========================================
   PRESET ICONS (REPEATER)
   ========================================= */

function mt_preset_icons() {

    $post_id = get_the_ID();
    $preset_id = get_field('preset', $post_id);

    if (!$preset_id) return '';

    $icons = get_field('icons', $preset_id);

    if (!$icons) return '';

    ob_start();
    ?>

    <div class="mt-icons">

        <?php foreach ($icons as $item): ?>
            <div class="mt-icon-item">

                <?php if (!empty($item['icon'])): ?>
                    <img 
                        src="<?php echo esc_url($item['icon']['url']); ?>" 
                        alt=""
                    >
                <?php endif; ?>

                <?php if (!empty($item['text'])): ?>
                    <div class="mt-icon-text">
                        <?php echo esc_html($item['text']); ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('preset_icons', 'mt_preset_icons');

/* =========================================
   upsel title
   ========================================= */
add_filter('gettext', function($translated, $text, $domain) {
    if ($text === 'You may also likeâ€¦') {
        if (pll_current_language() === 'pl') {
            return 'MoĹĽe Ci siÄ™ spodobaÄ‡';
        }
        if (pll_current_language() === 'lt') {
            return 'Jums taip pat gali patikti';
        }
        if (pll_current_language() === 'en') {
            return 'You may also like';
        }
    }
    return $translated;
}, 20, 3);

add_shortcode('mt_omnibus', function() {
    if (!is_product()) return '';

    global $product;

    return '<div class="omnibus-price">' .
        do_shortcode('[wc_price_history id="' . $product->get_id() . '" show_currency="1"]') .
    '</div>';
});

add_filter('loop_shop_columns', function($cols) {
    return 4;
}, 999);

add_action('wp_enqueue_scripts', function() {
    if (class_exists('DGWT_WCAS')) {
        do_action('dgwt/wcas/scripts');
    }
});
