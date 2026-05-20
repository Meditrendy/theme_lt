(function () {
  var filterSelector = '.x-col.e246-e7';
  var mobileBreakpoint = 980;

  function isMobile() {
    var visualWidth = window.visualViewport ? window.visualViewport.width : window.innerWidth;
    return Math.min(window.innerWidth, visualWidth) <= mobileBreakpoint;
  }

  function setDocumentMode() {
    var mobile = isMobile();

    document.documentElement.classList.toggle('mt-filters-is-mobile', mobile);
    document.documentElement.classList.toggle('mt-filters-is-desktop', !mobile);
  }

  function setMode(panel) {
    var mobile = isMobile();

    setDocumentMode();
    panel.classList.toggle('mt-filters-mobile', mobile);
    panel.classList.toggle('mt-filters-desktop', !mobile);

    if (!mobile) {
      closePanel(panel);
      panel.querySelectorAll('.mt-filter-open').forEach(function (item) {
        item.classList.remove('mt-filter-open');
      });
    }
  }

  function closePanel(panel) {
    panel.classList.remove('mt-filters-open');
    document.documentElement.classList.remove('mt-filters-lock');
    document.body.classList.remove('mt-filters-lock');
  }

  function openPanel(panel) {
    panel.classList.add('mt-filters-open');
    document.documentElement.classList.add('mt-filters-lock');
    document.body.classList.add('mt-filters-lock');
  }

  function setLoading(panel) {
    if (!panel) return;

    panel.classList.add('mt-filters-loading');
    document.documentElement.classList.add('mt-filters-loading');
    document.body.classList.add('mt-filters-loading');
  }

  function clearLoading() {
    document.documentElement.classList.remove('mt-filters-loading');
    document.body.classList.remove('mt-filters-loading');
    document.querySelectorAll(filterSelector + '.mt-filters-loading').forEach(function (panel) {
      panel.classList.remove('mt-filters-loading');
    });
  }

  function toggleFilter(filter) {
    if (!isMobile()) return;

    var panel = filter.closest(filterSelector);
    if (!panel) return;

    panel.querySelectorAll('.bapf_sfilter.mt-filter-open').forEach(function (item) {
      if (item !== filter) {
        item.classList.remove('mt-filter-open');
      }
    });

    filter.classList.toggle('mt-filter-open');
  }

  function setupPanel(panel) {
    if (!panel.querySelector('.berocket_single_filter_widget, .bapf_sfilter, .bapf_button.bapf_reset')) {
      return false;
    }

    if (panel.dataset.mtFiltersReady === '1') return;

    panel.dataset.mtFiltersReady = '1';
    panel.classList.add('mt-filters-panel');

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'mt-filters-trigger';
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span class="mt-filters-trigger-icon" aria-hidden="true"></span><span>Filtrai</span>';

    var header = document.createElement('div');
    header.className = 'mt-filters-panel-header';
    header.innerHTML = '<span>Filtrai</span><button type="button" class="mt-filters-close" aria-label="Uzdaryti filtrus"></button>';

    var footer = document.createElement('div');
    footer.className = 'mt-filters-panel-footer';

    var apply = document.createElement('button');
    apply.type = 'button';
    apply.className = 'mt-filters-apply';
    apply.textContent = 'Rodyti rezultatus';

    footer.appendChild(apply);
    panel.insertBefore(header, panel.firstChild);
    panel.appendChild(footer);
    panel.parentNode.insertBefore(trigger, panel);

    trigger.addEventListener('click', function () {
      openPanel(panel);
      trigger.setAttribute('aria-expanded', 'true');
    });

    panel.querySelector('.mt-filters-close').addEventListener('click', function () {
      closePanel(panel);
      trigger.setAttribute('aria-expanded', 'false');
    });

    apply.addEventListener('click', function () {
      setLoading(panel);
    });

    panel.addEventListener('click', function (event) {
      var heading = event.target.closest('.bapf_head');
      var reset = event.target.closest('.bapf_reset');

      if (reset && panel.contains(reset)) {
        setLoading(panel);
        return;
      }

      if (!heading || !panel.contains(heading)) return;

      toggleFilter(heading.closest('.bapf_sfilter'));
    });

    panel.addEventListener('change', function (event) {
      if (!event.target.matches('input, select')) return;

      setLoading(panel);
    });

    setMode(panel);

    return true;
  }

  function initFilters() {
    setDocumentMode();

    document.querySelectorAll(filterSelector).forEach(function (panel) {
      if (setupPanel(panel) || panel.dataset.mtFiltersReady === '1') {
        setMode(panel);
      }
    });
  }

  setDocumentMode();
  document.addEventListener('DOMContentLoaded', initFilters);
  document.addEventListener('berocket_ajax_filtering_start', function () {
    document.querySelectorAll(filterSelector).forEach(setLoading);
  });
  document.addEventListener('berocket_ajax_filtering_end', initFilters);
  document.addEventListener('berocket_ajax_filtering_end', clearLoading);
  document.addEventListener('bapf_update_products', clearLoading);

  if (window.jQuery) {
    window.jQuery(document).on('berocket_ajax_filtering_start', function () {
      document.querySelectorAll(filterSelector).forEach(setLoading);
    });
    window.jQuery(document).on('berocket_ajax_filtering_end bapf_update_products ajaxStop', clearLoading);
  }

  window.addEventListener('pageshow', clearLoading);

  document.addEventListener('keyup', function (event) {
    if (event.key !== 'Escape') return;

    document.querySelectorAll(filterSelector + '.mt-filters-open').forEach(closePanel);
    document.querySelectorAll('.mt-filters-trigger[aria-expanded="true"]').forEach(function (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    });
  });

  window.addEventListener('resize', initFilters);
  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', initFilters);
  }
})();
