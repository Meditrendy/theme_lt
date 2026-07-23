<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_stylesheet_directory() . '/modules/mobile-menu.php';

add_filter( 'gettext', 'meditrendy_translate_404_text', 20, 3 );

function meditrendy_translate_404_text( $translation, $text, $domain ) {
    if ( $domain !== '__x__' || ! is_404() ) {
        return $translation;
    }

    $translations = array(
        'Oops!' => 'Puslapis nerastas',
        'The page you are looking for is no longer here, or never existed in the first place (bummer). You can try searching for what you are looking for using the form below. If that still doesn\'t provide the results you are looking for, you can always start over from the home page.' => 'Puslapis, kurio ieškote, nerastas. Pabandykite pasinaudoti paieška arba grįžkite į pagrindinį puslapį.',
    );

    if ( isset( $translations[ $text ] ) ) {
        return $translations[ $text ];
    }

    return $translation;
}

/**
 * Load parent + child styles and scripts
 */

add_action( 'wp_enqueue_scripts', 'meditrendy_child_styles' );

function meditrendy_child_styles() {
    $header_css_path = get_stylesheet_directory() . '/styles/header.css';

    if ( file_exists( $header_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-header',
            get_stylesheet_directory_uri() . '/styles/header.css',
            array(),
            filemtime( $header_css_path )
        );
    }

    $marketing_banner_css_path = get_stylesheet_directory() . '/styles/marketing-banner.css';

    if (
        file_exists( $marketing_banner_css_path ) &&
        function_exists( 'meditrendy_marketing_banner_is_visible' ) &&
        meditrendy_marketing_banner_is_visible()
    ) {
        wp_enqueue_style(
            'meditrendy-marketing-banner',
            get_stylesheet_directory_uri() . '/styles/marketing-banner.css',
            array(),
            filemtime( $marketing_banner_css_path )
        );
    }

    $footer_css_path = get_stylesheet_directory() . '/styles/footer.css';

    if ( file_exists( $footer_css_path ) ) {
        wp_add_inline_style(
            'meditrendy-header',
            file_get_contents( $footer_css_path )
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

    if (
        file_exists( $side_cart_css_path ) &&
        ( ! function_exists( 'meditrendy_cart_module_enabled' ) || meditrendy_cart_module_enabled() )
    ) {
        wp_enqueue_style(
            'meditrendy-side-cart',
            get_stylesheet_directory_uri() . '/styles/side-cart.css',
            array(),
            filemtime( $side_cart_css_path )
        );
    }

    $categories_css_path = get_stylesheet_directory() . '/styles/categories.css';

    if ( file_exists( $categories_css_path ) && meditrendy_should_enqueue_category_styles() ) {
        wp_enqueue_style(
            'meditrendy-categories',
            get_stylesheet_directory_uri() . '/styles/categories.css',
            array(),
            filemtime( $categories_css_path )
        );
    }

    $search_empty_css_path = get_stylesheet_directory() . '/styles/search-empty.css';

    if ( is_search() && file_exists( $search_empty_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-search-empty',
            get_stylesheet_directory_uri() . '/styles/search-empty.css',
            array(),
            filemtime( $search_empty_css_path )
        );
    }

    $blog_css_path = get_stylesheet_directory() . '/styles/blog.css';

    if ( file_exists( $blog_css_path ) && meditrendy_should_enqueue_blog_styles() ) {
        wp_enqueue_style(
            'meditrendy-blog',
            get_stylesheet_directory_uri() . '/styles/blog.css',
            array(),
            filemtime( $blog_css_path )
        );
    }

    $homepage_css_path = get_stylesheet_directory() . '/styles/homepage.css';

    if ( ( is_front_page() || is_page_template( 'template-cornerstone-canvas.php' ) ) && file_exists( $homepage_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-homepage',
            get_stylesheet_directory_uri() . '/styles/homepage.css',
            array(),
            filemtime( $homepage_css_path )
        );

        $homepage_js_path = get_stylesheet_directory() . '/scripts/homepage.js';

        if ( file_exists( $homepage_js_path ) ) {
            wp_enqueue_script(
                'meditrendy-homepage',
                get_stylesheet_directory_uri() . '/scripts/homepage.js',
                array(),
                filemtime( $homepage_js_path ),
                true
            );
        }
    }

    $product_css_path = get_stylesheet_directory() . '/styles/product.css';

    if ( function_exists( 'is_product' ) && is_product() && file_exists( $product_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-product',
            get_stylesheet_directory_uri() . '/styles/product.css',
            array(),
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
                    'selectSize' => 'Pasirinkite dydį',
                ),
            )
        );

        $meditrendy_select_size_label = 'Pasirinkite dydį';

        if ( function_exists( 'meditrendy_core_current_language' ) || function_exists( 'pll_current_language' ) ) {
            $meditrendy_language = function_exists( 'meditrendy_core_current_language' )
                ? meditrendy_core_current_language()
                : pll_current_language( 'slug' );

            if ( 'lv' === $meditrendy_language ) {
                $meditrendy_select_size_label = 'Izvēlieties izmēru';
            } elseif ( 'et' === $meditrendy_language || 'ee' === $meditrendy_language ) {
                $meditrendy_select_size_label = 'Vali suurus';
            }
        }

        wp_localize_script(
            'meditrendy-buy-now-pdp-button',
            'MeditrendyBuyNowPdpButton',
            array(
                'labels' => array(
                    'selectSize' => $meditrendy_select_size_label,
                    'woosbAlertSelection' => $meditrendy_select_size_label,
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
            array(),
            filemtime( $cart_shipping_css_path )
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

function meditrendy_should_enqueue_category_styles() {
    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) ) {
        return true;
    }

    if ( ! is_singular() ) {
        return false;
    }

    $post = get_post();

    return $post && has_shortcode( $post->post_content, 'meditrendy_subcategories' );
}

function meditrendy_should_enqueue_blog_styles() {
    if ( is_home() || is_category() || is_tag() || is_date() || is_author() || is_singular( 'post' ) || is_page_template( 'template-blog-archive.php' ) ) {
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

function meditrendy_is_product_search_request() {
    if ( ! is_search() ) {
        return false;
    }

    $post_type = get_query_var( 'post_type' );

    if ( is_array( $post_type ) ) {
        return in_array( 'product', $post_type, true );
    }

    return 'product' === $post_type || ( isset( $_GET['post_type'] ) && 'product' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) );
}

function meditrendy_search_empty_copy() {
    $labels = array(
        'lt' => array(
            'eyebrow'      => __( 'Paieškos rezultatai', 'meditrendy-child' ),
            'title'        => __( 'Nieko neradome', 'meditrendy-child' ),
            'description'  => __( 'Pabandykite pakeisti paieškos frazę arba peržiūrėkite populiariausias prekių kategorijas.', 'meditrendy-child' ),
            'search_label' => __( 'Ieškoti dar kartą', 'meditrendy-child' ),
            'placeholder'  => __( 'Įveskite paieškos frazę', 'meditrendy-child' ),
            'submit'       => __( 'Ieškoti', 'meditrendy-child' ),
            'shop'         => __( 'Peržiūrėti visas prekes', 'meditrendy-child' ),
            'home'         => __( 'Grįžti į pradžią', 'meditrendy-child' ),
            'query_prefix' => __( 'Ieškota:', 'meditrendy-child' ),
        ),
        'lv' => array(
            'eyebrow'      => __( 'Meklēšanas rezultāti', 'meditrendy-child' ),
            'title'        => __( 'Nekas netika atrasts', 'meditrendy-child' ),
            'description'  => __( 'Mēģiniet mainīt meklēšanas frāzi vai apskatiet populārākās preču kategorijas.', 'meditrendy-child' ),
            'search_label' => __( 'Meklēt vēlreiz', 'meditrendy-child' ),
            'placeholder'  => __( 'Ievadiet meklēšanas frāzi', 'meditrendy-child' ),
            'submit'       => __( 'Meklēt', 'meditrendy-child' ),
            'shop'         => __( 'Skatīt visas preces', 'meditrendy-child' ),
            'home'         => __( 'Atgriezties sākumā', 'meditrendy-child' ),
            'query_prefix' => __( 'Meklēts:', 'meditrendy-child' ),
        ),
        'et' => array(
            'eyebrow'      => __( 'Otsingu tulemused', 'meditrendy-child' ),
            'title'        => __( 'Tulemusi ei leitud', 'meditrendy-child' ),
            'description'  => __( 'Proovige otsingufraasi muuta või vaadake populaarsemaid tootekategooriaid.', 'meditrendy-child' ),
            'search_label' => __( 'Otsi uuesti', 'meditrendy-child' ),
            'placeholder'  => __( 'Sisestage otsingufraas', 'meditrendy-child' ),
            'submit'       => __( 'Otsi', 'meditrendy-child' ),
            'shop'         => __( 'Vaata kõiki tooteid', 'meditrendy-child' ),
            'home'         => __( 'Tagasi avalehele', 'meditrendy-child' ),
            'query_prefix' => __( 'Otsiti:', 'meditrendy-child' ),
        ),
        'en' => array(
            'eyebrow'      => __( 'Search results', 'meditrendy-child' ),
            'title'        => __( 'No results found', 'meditrendy-child' ),
            'description'  => __( 'Try changing your search phrase or browse the most popular product categories.', 'meditrendy-child' ),
            'search_label' => __( 'Search again', 'meditrendy-child' ),
            'placeholder'  => __( 'Enter a search phrase', 'meditrendy-child' ),
            'submit'       => __( 'Search', 'meditrendy-child' ),
            'shop'         => __( 'View all products', 'meditrendy-child' ),
            'home'         => __( 'Back to home', 'meditrendy-child' ),
            'query_prefix' => __( 'Searched for:', 'meditrendy-child' ),
        ),
        'pl' => array(
            'eyebrow'      => __( 'Wyniki wyszukiwania', 'meditrendy-child' ),
            'title'        => __( 'Nic nie znaleziono', 'meditrendy-child' ),
            'description'  => __( 'Spróbuj zmienić wyszukiwaną frazę albo przejrzyj najpopularniejsze kategorie produktów.', 'meditrendy-child' ),
            'search_label' => __( 'Wyszukaj ponownie', 'meditrendy-child' ),
            'placeholder'  => __( 'Wpisz szukaną frazę', 'meditrendy-child' ),
            'submit'       => __( 'Szukaj', 'meditrendy-child' ),
            'shop'         => __( 'Zobacz wszystkie produkty', 'meditrendy-child' ),
            'home'         => __( 'Wróć na stronę główną', 'meditrendy-child' ),
            'query_prefix' => __( 'Szukano:', 'meditrendy-child' ),
        ),
    );

    $language = function_exists( 'meditrendy_core_current_language' )
        ? meditrendy_core_current_language()
        : ( function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'lt' );

    if ( 'ee' === $language ) {
        $language = 'et';
    }

    return isset( $labels[ $language ] ) ? $labels[ $language ] : $labels['lt'];
}

function meditrendy_render_search_empty_state() {
    $search_query = get_search_query();
    $copy         = meditrendy_search_empty_copy();
    $shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
    ?>

    <section class="mt-search-empty" aria-labelledby="mt-search-empty-title">
        <div class="mt-search-empty__inner">
            <p class="mt-search-empty__eyebrow"><?php echo esc_html( $copy['eyebrow'] ); ?></p>
            <h2 id="mt-search-empty-title" class="mt-search-empty__title"><?php echo esc_html( $copy['title'] ); ?></h2>

            <?php if ( $search_query ) : ?>
                <p class="mt-search-empty__query">
                    <span><?php echo esc_html( $copy['query_prefix'] ); ?></span>
                    <strong><?php echo esc_html( $search_query ); ?></strong>
                </p>
            <?php endif; ?>

            <p class="mt-search-empty__description"><?php echo esc_html( $copy['description'] ); ?></p>

            <form class="mt-search-empty__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label class="screen-reader-text" for="mt-search-empty-field"><?php echo esc_html( $copy['search_label'] ); ?></label>
                <input
                    id="mt-search-empty-field"
                    class="mt-search-empty__input"
                    type="search"
                    name="s"
                    value="<?php echo esc_attr( $search_query ); ?>"
                    placeholder="<?php echo esc_attr( $copy['placeholder'] ); ?>"
                >
                <input type="hidden" name="post_type" value="product">
                <input type="hidden" name="dgwt_wcas" value="1">
                <button class="mt-search-empty__submit" type="submit"><?php echo esc_html( $copy['submit'] ); ?></button>
            </form>

            <div class="mt-search-empty__actions">
                <a class="mt-search-empty__button mt-search-empty__button--primary" href="<?php echo esc_url( $shop_url ); ?>">
                    <?php echo esc_html( $copy['shop'] ); ?>
                </a>
                <a class="mt-search-empty__button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php echo esc_html( $copy['home'] ); ?>
                </a>
            </div>
        </div>
    </section>
    <?php
}

function meditrendy_should_render_search_empty_state() {
    global $wp_query;

    return meditrendy_is_product_search_request() && $wp_query instanceof WP_Query && 0 === (int) $wp_query->found_posts;
}

add_shortcode( 'meditrendy_search_empty_state', 'meditrendy_search_empty_state_shortcode' );

function meditrendy_search_empty_state_shortcode() {
    if ( ! meditrendy_should_render_search_empty_state() ) {
        return '';
    }

    ob_start();
    echo '<div class="woocommerce-no-products-found">';
    meditrendy_render_search_empty_state();
    echo '</div>';

    return ob_get_clean();
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
                <?php echo wp_kses_post($details_field['value']); ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if (!empty($fabric_field['value'])): ?>
        <details>
            <summary><?php echo esc_html($fabric_field['label']); ?></summary>
            <div class="acc-content">
                <?php echo wp_kses_post($fabric_field['value']); ?>
            </div>
        </details>
        <?php endif; ?>

        <?php if (!empty($delivery_field['value'])): ?>
        <details>
            <summary><?php echo esc_html($delivery_field['label']); ?></summary>
            <div class="acc-content">
                <?php echo wp_kses_post($delivery_field['value']); ?>
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
        $language = function_exists('meditrendy_core_current_language')
            ? meditrendy_core_current_language()
            : (function_exists('pll_current_language') ? pll_current_language() : 'lt');

        if ($language === 'pl') {
            return 'MoĹĽe Ci siÄ™ spodobaÄ‡';
        }
        if ($language === 'lt') {
            return 'Jums taip pat gali patikti';
        }
        if ($language === 'en') {
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

/**
 * Load the site-wide brand typeface after Pro and Cornerstone styles.
 */
add_action( 'wp_enqueue_scripts', 'meditrendy_enqueue_raleway_typography', 999 );

function meditrendy_enqueue_raleway_typography() {
    $typography_css_path = get_stylesheet_directory() . '/styles/typography.css';

    if ( ! file_exists( $typography_css_path ) ) {
        return;
    }

    wp_enqueue_style(
        'meditrendy-raleway-font',
        'https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'meditrendy-typography',
        get_stylesheet_directory_uri() . '/styles/typography.css',
        array( 'meditrendy-raleway-font' ),
        filemtime( $typography_css_path )
    );
}
