 jQuery(function ($) {

    const form = $('form.variations_form');

    if (!form.length) {
        return;
    }

    const buttonSelectors = [
        '.single_add_to_cart_button',
        '.buy-now-button',
        '.buy_now_button',
        '.wc-buy-now-button'
    ];

    function getButtons() {
        return $(buttonSelectors.join(','));
    }

    function keepButtonsActive() {
        getButtons().each(function () {
            $(this)
                .prop('disabled', false)
                .removeAttr('disabled')
                .removeClass('disabled');
        });
    }

    function hasSelectedVariation() {
        const variationId = form.find('input[name="variation_id"]').val();

        return variationId && variationId !== '0';
    }

    function showTooltip(button) {
        let tooltip = $('.mt-size-tooltip');

        if (!tooltip.length) {
            tooltip = $('<div class="mt-size-tooltip">Pasirinkite dydį prieš įdėdami prekę į krepšelį.</div>');
            $('body').append(tooltip);
        }

        const offset = button.offset();

        tooltip.css({
            top: offset.top + button.outerHeight() + 10,
            left: offset.left
        });

        tooltip.addClass('is-visible');

        clearTimeout(window.mtSizeTooltipTimer);

        window.mtSizeTooltipTimer = setTimeout(function () {
            tooltip.removeClass('is-visible');
        }, 3000);
    }

    function focusFirstVariationField() {
        const firstSelect = form.find('select').first();

        if (firstSelect.length) {
            firstSelect.trigger('focus');
        }
    }

    keepButtonsActive();

    /**
     * Important:
     * WooCommerce may re-disable the button after variation events.
     */
    form.on('woocommerce_variation_has_changed found_variation reset_data hide_variation check_variations', function () {
        setTimeout(keepButtonsActive, 10);
        setTimeout(keepButtonsActive, 100);
    });

    /**
     * MutationObserver watches if WooCommerce/theme adds disabled again.
     */
    const observer = new MutationObserver(function () {
        keepButtonsActive();
    });

    getButtons().each(function () {
        observer.observe(this, {
            attributes: true,
            attributeFilter: ['disabled', 'class']
        });
    });

    /**
     * Block click when size is not selected.
     */
    $(document).on('click', buttonSelectors.join(','), function (event) {
        const button = $(this);

        if (hasSelectedVariation()) {
            return true;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        keepButtonsActive();
        showTooltip(button);
        focusFirstVariationField();

        return false;
    });

    /**
     * Extra safety: block form submit too.
     */
    form.on('submit', function (event) {
        if (hasSelectedVariation()) {
            return true;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const button = form.find('.single_add_to_cart_button').first();

        if (button.length) {
            showTooltip(button);
        }

        focusFirstVariationField();

        return false;
    });

    /**
     * Last safety net because some themes/plugins re-render PDP controls.
     */
    setInterval(keepButtonsActive, 500);
});