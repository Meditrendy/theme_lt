(function () {
  const summarySelector = '.wp-block-woocommerce-checkout-order-summary-block';
  const titleSelector = '.wc-block-components-checkout-order-summary__title[role="button"]';
  let lastExpandedAt = 0;

  function forceOpenSummary(title) {
    const summary = title.closest(summarySelector);
    const contentId = title.getAttribute('aria-controls');
    const content = contentId ? document.getElementById(contentId) : summary && summary.querySelector('.wc-block-components-checkout-order-summary__content');

    title.setAttribute('aria-expanded', 'true');
    title.classList.add('is-open');

    if (content) {
      content.classList.add('is-open');
      content.hidden = false;
      content.removeAttribute('hidden');
      content.removeAttribute('aria-hidden');
      content.style.display = '';
    }
  }

  function openTitle(title) {
    if (title.getAttribute('aria-expanded') !== 'false') {
      return false;
    }

    title.click();

    window.requestAnimationFrame(function () {
      if (title.getAttribute('aria-expanded') === 'false') {
        title.dispatchEvent(new MouseEvent('click', {
          bubbles: true,
          cancelable: true,
          view: window,
        }));
      }

      window.requestAnimationFrame(function () {
        if (title.getAttribute('aria-expanded') === 'false') {
          forceOpenSummary(title);
        }
      });
    });

    return true;
  }

  function expandOrderSummary() {
    document.querySelectorAll(`${summarySelector} ${titleSelector}`).forEach(function (title) {
      if (openTitle(title)) {
        lastExpandedAt = Date.now();
      }
    });
  }

  function markDuplicateMobileSummaries() {
    document.querySelectorAll(summarySelector).forEach(function (summary, index) {
      summary.classList.toggle('meditrendy-checkout-order-summary--mobile-duplicate', index > 0);
    });
  }

  function scheduleExpand() {
    window.requestAnimationFrame(function () {
      expandOrderSummary();
      markDuplicateMobileSummaries();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleExpand);
  } else {
    scheduleExpand();
  }

  window.setTimeout(expandOrderSummary, 250);
  window.setTimeout(expandOrderSummary, 750);
  window.setTimeout(expandOrderSummary, 1500);
  window.setTimeout(expandOrderSummary, 3000);

  const observer = new MutationObserver(function () {
    if (Date.now() - lastExpandedAt < 500) {
      return;
    }

    scheduleExpand();
  });

  observer.observe(document.documentElement, {
    childList: true,
    subtree: true,
  });
})();
