(function () {
    const config = window.MeditrendyCartShippingLoading || {};
    const label = config.label || 'Atnaujinamas pristatymas...';
    const shippingSelector = '#shipping-option, .wc-block-checkout__shipping-option, .wc-block-components-totals-shipping';

    let activeRoot = null;
    let indicator = null;
    let pendingRequests = 0;
    let stopTimer = 0;

    function getRoot(target) {
        if (target && target.closest) {
            const targetRoot = target.closest(shippingSelector);

            if (targetRoot) {
                activeRoot = targetRoot;
            }
        }

        if (activeRoot && document.body.contains(activeRoot)) {
            return activeRoot;
        }

        return document.querySelector(shippingSelector);
    }

    function ensureIndicator(root) {
        if (indicator && document.body.contains(indicator)) {
            return;
        }

        indicator = document.createElement('div');
        indicator.className = 'mt-cart-shipping-loading-indicator';
        indicator.setAttribute('role', 'status');
        indicator.setAttribute('aria-live', 'polite');
        indicator.innerHTML = '<span class="mt-cart-shipping-loading-spinner" aria-hidden="true"></span><span>' + label + '</span>';
        root.appendChild(indicator);
    }

    function setLoading(isLoading, target) {
        const root = getRoot(target);

        document.documentElement.classList.toggle('mt-cart-shipping-is-loading', isLoading);

        if (!root) {
            return;
        }

        root.classList.toggle('mt-cart-shipping-loading-target', isLoading);
        root.setAttribute('aria-busy', isLoading ? 'true' : 'false');

        if (isLoading) {
            ensureIndicator(root);
        }
    }

    function showLoading(target) {
        window.clearTimeout(stopTimer);
        setLoading(true, target);

        stopTimer = window.setTimeout(function () {
            if (pendingRequests === 0) {
                setLoading(false);
            }
        }, 5000);
    }

    function hideLoadingSoon() {
        window.clearTimeout(stopTimer);

        stopTimer = window.setTimeout(function () {
            if (pendingRequests === 0) {
                setLoading(false);
            }
        }, 450);
    }

    function isStoreApiRequest(resource, options) {
        const url = typeof resource === 'string' ? resource : resource && resource.url;
        const method = options && options.method ? String(options.method).toUpperCase() : 'GET';

        return method !== 'GET' && !!url && (
            url.indexOf('/wc/store/') !== -1 ||
            url.indexOf('wc/store') !== -1
        );
    }

    function patchFetch() {
        if (!window.fetch || window.fetch.__meditrendyShippingLoadingPatched) {
            return;
        }

        const originalFetch = window.fetch.bind(window);

        window.fetch = function (resource, options) {
            const shouldTrack = isStoreApiRequest(resource, options);

            if (shouldTrack) {
                pendingRequests += 1;
                showLoading();
            }

            return originalFetch(resource, options).finally(function () {
                if (shouldTrack) {
                    pendingRequests = Math.max(0, pendingRequests - 1);
                    hideLoadingSoon();
                }
            });
        };

        window.fetch.__meditrendyShippingLoadingPatched = true;
    }

    function bindShippingChanges() {
        document.addEventListener('change', function (event) {
            if (!event.target.closest || !event.target.closest(shippingSelector)) {
                return;
            }

            showLoading(event.target);
            hideLoadingSoon();
        }, true);
    }

    function init() {
        patchFetch();
        bindShippingChanges();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
