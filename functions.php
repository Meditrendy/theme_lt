<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load parent + child styles and scripts
 */

add_action( 'wp_enqueue_scripts', 'meditrendy_child_styles' );
add_action( 'wp_head', 'meditrendy_early_mobile_menu_toggle', 0 );
add_action( 'wp_footer', 'meditrendy_filter_script_tag', 99 );

function meditrendy_early_mobile_menu_toggle() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <script>
    (function () {
      var lastPointerToggle = 0;

      function toggleClass(element, className, state) {
        if (!element || !element.classList) return;
        element.classList.toggle(className, state);
      }

      function setToggleState(id, state) {
        document.querySelectorAll('[data-x-toggleable="' + id + '"]').forEach(function (element) {
          if (element.hasAttribute('aria-hidden')) {
            element.setAttribute('aria-hidden', state ? 'false' : 'true');
          }

          if (element.hasAttribute('aria-expanded')) {
            element.setAttribute('aria-expanded', state ? 'true' : 'false');
          }

          if (element.getAttribute('data-x-toggle') === 'collapse-b') {
            toggleClass(element, 'collapsed', !state);
          } else {
            toggleClass(element, 'x-active', state);
          }

          var icon = element.querySelector('.x-toggle');
          toggleClass(icon, 'x-active', state);
        });

        if (window.TCOToggleStates && typeof window.TCOToggleStates.set === 'function') {
          window.TCOToggleStates.set(id, state);
        }
      }

      function handleEarlyOffCanvas(event) {
        if (event.type === 'click' && Date.now() - lastPointerToggle < 600) {
          event.preventDefault();
          event.stopPropagation();
          return;
        }

        var toggle = event.target.closest('[data-x-toggle][data-x-toggleable]');
        if (!toggle || toggle.closest('.x-anchor-layered-back')) return;

        var isOffCanvasToggle = toggle.hasAttribute('data-x-toggle-overlay') && toggle.getAttribute('aria-controls');
        if (!isOffCanvasToggle) {
          return;
        }

        var id = toggle.getAttribute('data-x-toggleable');
        if (!id) return;

        event.preventDefault();
        event.stopPropagation();

        if (event.type === 'pointerdown') {
          lastPointerToggle = Date.now();
        }

        var isCollapsedToggle = toggle.getAttribute('data-x-toggle') === 'collapse-b';
        var nextState = isCollapsedToggle ? toggle.classList.contains('collapsed') : !toggle.classList.contains('x-active');

        setToggleState(id, nextState);

        document.querySelectorAll('[data-x-toggleable="' + id + '"]').forEach(function (element) {
          element.dispatchEvent(new CustomEvent('tco-toggle', {
            bubbles: false,
            detail: {
              state: nextState,
              id: id
            }
          }));
        });
      }

      document.addEventListener('pointerdown', handleEarlyOffCanvas, true);
      document.addEventListener('click', handleEarlyOffCanvas, true);
    }());
    </script>
    <?php
}

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
            'meditrendy-filters',
            get_stylesheet_directory_uri() . '/styles/header.css',
            array('child-style'),
            filemtime( $header_css_path )
        );
    }

    $filters_css_path = get_stylesheet_directory() . '/styles/filters.css';

    if ( file_exists( $filters_css_path ) ) {
        wp_enqueue_style(
            'meditrendy-filters',
            get_stylesheet_directory_uri() . '/styles/filters.css',
            array('child-style'),
            filemtime( $filters_css_path )
        );
    }

    $filters_js_path = get_stylesheet_directory() . '/scripts/filters.js';

    if ( file_exists( $filters_js_path ) ) {
        wp_enqueue_script(
            'meditrendy-filters-js',
            get_stylesheet_directory_uri() . '/scripts/filters.js',
            array(),
            filemtime( $filters_js_path ),
            true
        );
    }
}

function meditrendy_filter_script_tag() {
    $filters_js_path = get_stylesheet_directory() . '/scripts/filters.js';

    if ( ! file_exists( $filters_js_path ) ) {
        return;
    }

    printf(
        '<script id="meditrendy-filters-fallback" src="%s"></script>' . "\n",
        esc_url( get_stylesheet_directory_uri() . '/scripts/filters.js?ver=' . filemtime( $filters_js_path ) )
    );
}

/* =========================================
    przyciski ilości produktu w single cart
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
      minus.textContent = '−';

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
add_action('wp_footer', function() {
?>
<script>
jQuery(function($){

    function initMiniCartQty(){

        $('.mini-cart-qty').each(function(){

            var wrapper = $(this);
            var input   = wrapper.find('input[type="number"]');
            var minus   = wrapper.find('.qty-minus');
            var plus    = wrapper.find('.qty-plus');
            var key     = wrapper.data('key');

            function updateQty(qty){

                $.post(
                    wc_cart_fragments_params.ajax_url,
                    {
                        action: 'woocommerce_update_cart_item_quantity',
                        cart_item_key: key,
                        quantity: qty
                    },
                    function(){
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                );

            }

            minus.off('click').on('click', function(){

                var min = parseInt(input.attr('min')) || 1;
                var val = parseInt(input.val()) || 1;

                if(val > min){
                    val--;
                    input.val(val);
                    updateQty(val);
                }

            });

            plus.off('click').on('click', function(){

                var max = parseInt(input.attr('max')) || 999;
                var val = parseInt(input.val()) || 1;

                if(val < max){
                    val++;
                    input.val(val);
                    updateQty(val);
                }

            });

        });

    }

    initMiniCartQty();

    $(document.body).on('wc_fragments_refreshed', function(){
        initMiniCartQty();
    });

});
</script>
<?php
});
/* =========================================
    mini cart clasic
   ========================================= */
add_shortcode('classic_mini_cart', function(){
    ob_start();
    woocommerce_mini_cart();
    return ob_get_clean();
});

/* =========================================
   MINI CART QTY – ULTRA FAST (FRAGMENTS)
   ========================================= */

/* 1️⃣ Nadpisanie ilości w classic mini cart */
add_filter('woocommerce_widget_cart_item_quantity', 'meditrendy_figs_qty', 10, 3);
function meditrendy_figs_qty($html, $cart_item, $cart_item_key) {

    $_product = $cart_item['data'];

    if (!$_product->is_sold_individually()) {

        $qty = $cart_item['quantity'];

        ob_start(); ?>

        <div class="figs-qty-wrapper">

            <div class="figs-qty" data-key="<?php echo esc_attr($cart_item_key); ?>">

                <button class="figs-minus" type="button">−</button>

                <span class="figs-count"><?php echo esc_html($qty); ?></span>

                <button class="figs-plus" type="button">+</button>

            </div>

            <span class="figs-price">
                <?php echo WC()->cart->get_product_price($_product); ?>
            </span>

        </div>

        <?php
        return ob_get_clean();
    }

    return $html;
}


/* 2️⃣ AJAX handler */
add_action('wp_ajax_woocommerce_update_cart_item', 'meditrendy_update_cart_item');
add_action('wp_ajax_nopriv_woocommerce_update_cart_item', 'meditrendy_update_cart_item');

function meditrendy_update_cart_item() {

    if (empty($_POST['cart_item_key'])) {
        wp_die();
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity      = intval($_POST['new_qty']);

    if ($quantity < 1) {
        $quantity = 1;
    }

    if (isset(WC()->cart->cart_contents[$cart_item_key])) {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
    }

    WC()->cart->calculate_totals();

    wp_die();
}
/* 3️⃣ JS */
add_action('wp_footer', function() {
?>
<script>
jQuery(function($){

    function animateSubtotalStart(){
        $('.woocommerce-mini-cart__total')
            .addClass('is-calculating');
    }

    function animateSubtotalStop(){
        $('.woocommerce-mini-cart__total')
            .removeClass('is-calculating');
    }

    function updateMiniCart(key, qty){

        animateSubtotalStart();

        $.ajax({
            type: 'POST',
            url: "<?php echo admin_url('admin-ajax.php'); ?>",
            data: {
                action: 'woocommerce_update_cart_item',
                cart_item_key: key,
                new_qty: qty
            },
            success: function(){
                $(document.body).trigger('wc_fragment_refresh');
            }
        });
    }

    $(document).on('click', '.figs-plus', function(){

        let wrapper = $(this).closest('.figs-qty');
        let key = wrapper.data('key');
        let countEl = wrapper.find('.figs-count');
        let qty = parseInt(countEl.text()) + 1;

        countEl.text(qty);
        updateMiniCart(key, qty);
    });

    $(document).on('click', '.figs-minus', function(){

        let wrapper = $(this).closest('.figs-qty');
        let key = wrapper.data('key');
        let countEl = wrapper.find('.figs-count');
        let qty = parseInt(countEl.text()) - 1;

        if(qty >= 1){
            countEl.text(qty);
            updateMiniCart(key, qty);
        }
    });

    /* zatrzymanie animacji po odświeżeniu fragmentów */
    $(document.body).on('wc_fragments_refreshed', function(){
        animateSubtotalStop();
    });

});
</script>
<script>
jQuery(function($){

    function updateMiniCart(key, qty){

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: {
                action: 'meditrendy_update_mini_cart',
                key: key,
                qty: qty
            },
            success: function(response){
                if(response.success){
                    $('.woocommerce-mini-cart').replaceWith(response.data.mini_cart);
                }
            }
        });

    }

    $(document).on('click', '.qty-plus', function(){

        let wrapper = $(this).closest('.mini-cart-qty');
        let input = wrapper.find('input');
        let key = wrapper.data('key');
        let max = parseInt(input.attr('max'));

        let qty = parseInt(input.val()) + 1;

        if(!max || qty <= max){
            input.val(qty);
            updateMiniCart(key, qty);
        }

    });

    $(document).on('click', '.qty-minus', function(){

        let wrapper = $(this).closest('.mini-cart-qty');
        let input = wrapper.find('input');
        let key = wrapper.data('key');

        let qty = parseInt(input.val()) - 1;

        if(qty >= 1){
            input.val(qty);
            updateMiniCart(key, qty);
        }

    });

});
</script>
<?php
});


function mt_preset_accordion() {

    $post_id = get_the_ID();
    $preset_id = get_field('preset', $post_id);

    if (!$preset_id) return '';

    // Pobranie całych obiektów pól
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
    if ($text === 'You may also like…') {
        if (pll_current_language() === 'pl') {
            return 'Może Ci się spodobać';
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
