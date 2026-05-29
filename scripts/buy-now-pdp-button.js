jQuery(function ($) {

    const buttonSelectors = [
        '.single_add_to_cart_button',
        '.buy-now-button',
        '.buy_now_button',
        '.wc-buy-now-button'
    ];

    const label =
        window.MeditrendyBuyNowPdpButton &&
        window.MeditrendyBuyNowPdpButton.labels &&
        window.MeditrendyBuyNowPdpButton.labels.selectSize
            ? window.MeditrendyBuyNowPdpButton.labels.selectSize
            : 'Pasirinkite dydį prieš įdėdami prekę į krepšelį.';

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

    function showTooltip(button) {
        let tooltip = $('.mt-size-tooltip');

        if (!tooltip.length) {
            tooltip = $('<div class="mt-size-tooltip"></div>');
            $('body').append(tooltip);
        }

        tooltip.text(label);

        const offset = button.offset();

        if (!offset) {
            return;
        }

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

    function getMainProductVariationForm(button) {
        const cartForm = button.closest('form.cart');

        if (cartForm.hasClass('variations_form')) {
            return cartForm;
        }

        const previousVariationForm = cartForm.prevAll('form.variations_form').first();

        if (previousVariationForm.length) {
            return previousVariationForm;
        }

        return $('form.variations_form:not(.woosb_variations_form)').first();
    }

    function isNormalVariableProductReady(button) {
        const form = getMainProductVariationForm(button);

        if (!form.length) {
            return true;
        }

        const variationId = form.find('input[name="variation_id"]').val();

        return !!variationId && variationId !== '0';
    }

    function getSmartBundleWrap(button) {
        const cartForm = button.closest('form.cart');

        let wrap = cartForm.prevAll('.woosb-wrap').first();

        if (!wrap.length) {
            wrap = $('.woosb-wrap.woosb-bundled').first();
        }

        return wrap;
    }

    function isSmartBundleProduct(button) {
        const cartForm = button.closest('form.cart');

        return cartForm.find('input[name="woosb_ids"]').length > 0 || getSmartBundleWrap(button).length > 0;
    }

    function isSmartBundleReady(button) {
        const wrap = getSmartBundleWrap(button);

        if (!wrap.length) {
            return true;
        }

        let isReady = true;

        wrap.find('form.variations_form.woosb_variations_form').each(function () {
            const variationForm = $(this);

            variationForm.find('select[name^="attribute_"]').each(function () {
                const select = $(this);

                if (!select.val()) {
                    isReady = false;
                    return false;
                }
            });

            if (!isReady) {
                return false;
            }
        });

        return isReady;
    }

    function focusFirstMissingSmartBundleField(button) {
        const wrap = getSmartBundleWrap(button);

        if (!wrap.length) {
            return;
        }

        const emptySelect = wrap.find('select[name^="attribute_"]').filter(function () {
            return !$(this).val();
        }).first();

        if (emptySelect.length) {
            const variation = emptySelect.closest('.variation');

            if (variation.length) {
                $('html, body').animate({
                    scrollTop: variation.offset().top - 120
                }, 200);
            }

            emptySelect.trigger('focus');
        }
    }

    function focusFirstMissingNormalVariationField(button) {
        const form = getMainProductVariationForm(button);

        if (!form.length) {
            return;
        }

        const emptySelect = form.find('select[name^="attribute_"]').filter(function () {
            return !$(this).val();
        }).first();

        if (emptySelect.length) {
            emptySelect.trigger('focus');
        }
    }

    function isProductReady(button) {
        if (isSmartBundleProduct(button)) {
            return isSmartBundleReady(button);
        }

        return isNormalVariableProductReady(button);
    }

    function focusFirstMissingField(button) {
        if (isSmartBundleProduct(button)) {
            focusFirstMissingSmartBundleField(button);
            return;
        }

        focusFirstMissingNormalVariationField(button);
    }

    keepButtonsActive();

    $(document).on(
        'woocommerce_variation_has_changed found_variation reset_data hide_variation check_variations woosb_calc_price woosb_init woosb_update',
        function () {
            setTimeout(keepButtonsActive, 10);
            setTimeout(keepButtonsActive, 100);
        }
    );

    const observer = new MutationObserver(function () {
        keepButtonsActive();
    });

    getButtons().each(function () {
        observer.observe(this, {
            attributes: true,
            attributeFilter: ['disabled', 'class']
        });
    });

    $(document).on('click', buttonSelectors.join(','), function (event) {
        const button = $(this);

        if (isProductReady(button)) {
            return true;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        keepButtonsActive();
        showTooltip(button);
        focusFirstMissingField(button);

        return false;
    });

    $(document).on('submit', 'form.cart, form.variations_form', function (event) {
        const form = $(this);
        const button = form.find('.single_add_to_cart_button').first();

        if (!button.length) {
            return true;
        }

        if (isProductReady(button)) {
            return true;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        keepButtonsActive();
        showTooltip(button);
        focusFirstMissingField(button);

        return false;
    });

    setInterval(keepButtonsActive, 500);
});