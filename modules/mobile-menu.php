<?php

add_action( 'wp_head', 'meditrendy_early_mobile_menu_toggle', 0 );
add_action( 'wp_head', 'meditrendy_mobile_parent_menu_links', 1 );

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

function meditrendy_mobile_parent_menu_links() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <script>
    (function () {
      function hasRealHref(anchor) {
        var href = (anchor.getAttribute('href') || '').trim();

        return href && href !== '#' && href.indexOf('javascript:') !== 0;
      }

      function setToggleState(id, state) {
        document.querySelectorAll('[data-x-toggleable="' + id + '"]').forEach(function (element) {
          if (element.hasAttribute('aria-hidden')) {
            element.setAttribute('aria-hidden', state ? 'false' : 'true');
          }

          if (element.hasAttribute('aria-expanded')) {
            element.setAttribute('aria-expanded', state ? 'true' : 'false');
          }

          if (element.hasAttribute('data-x-toggle-collapse')) {
            element.classList.toggle('x-collapsed', !state);
          } else if (element.getAttribute('data-x-toggle') === 'collapse-b') {
            element.classList.toggle('collapsed', !state);
          } else {
            element.classList.toggle('x-active', state);
          }
        });

        if (window.TCOToggleStates && typeof window.TCOToggleStates.set === 'function') {
          window.TCOToggleStates.set(id, state);
        }
      }

      function enhanceMobileMenuParents(root) {
        (root || document).querySelectorAll(
          '.x-off-canvas ul.x-menu-collapsed li.menu-item-has-children > a.x-anchor[data-x-toggle="collapse"]:not([data-mt-parent-link-ready])'
        ).forEach(function (anchor) {
          if (!hasRealHref(anchor)) {
            return;
          }

          var toggleId = anchor.getAttribute('data-x-toggleable');
          var controls = anchor.getAttribute('aria-controls');
          var item = anchor.parentElement;

          if (!toggleId || !item || item.querySelector(':scope > .mt-mobile-submenu-toggle')) {
            return;
          }

          anchor.setAttribute('data-mt-parent-link-ready', '1');
          anchor.classList.add('mt-mobile-parent-link');
          anchor.removeAttribute('data-x-toggle');
          anchor.removeAttribute('data-x-toggleable');
          anchor.removeAttribute('aria-controls');
          anchor.removeAttribute('aria-expanded');
          anchor.removeAttribute('aria-haspopup');
          anchor.removeAttribute('aria-label');
          anchor.addEventListener('click', function (event) {
            event.stopPropagation();

            if (typeof event.stopImmediatePropagation === 'function') {
              event.stopImmediatePropagation();
            }
          }, true);

          item.classList.add('mt-mobile-parent-split');

          var button = document.createElement('button');
          button.type = 'button';
          button.className = 'mt-mobile-submenu-toggle';
          button.setAttribute('data-mt-toggleable', toggleId);
          button.setAttribute('aria-expanded', 'false');
          button.setAttribute('aria-label', 'Išskleisti submeniu');

          if (controls) {
            button.setAttribute('aria-controls', controls);
          }

          button.innerHTML = '<span aria-hidden="true"></span>';
          anchor.insertAdjacentElement('afterend', button);
        });
      }

      document.addEventListener('click', function (event) {
        var button = event.target.closest('.mt-mobile-submenu-toggle');

        if (!button) {
          return;
        }

        var toggleId = button.getAttribute('data-mt-toggleable');

        if (!toggleId) {
          return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (typeof event.stopImmediatePropagation === 'function') {
          event.stopImmediatePropagation();
        }

        var nextState = button.getAttribute('aria-expanded') !== 'true';
        button.setAttribute('aria-expanded', nextState ? 'true' : 'false');
        button.setAttribute('aria-label', nextState ? 'Suskleisti submeniu' : 'Išskleisti submeniu');
        button.classList.toggle('x-active', nextState);
        setToggleState(toggleId, nextState);
      }, true);

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
          enhanceMobileMenuParents(document);
        });
      } else {
        enhanceMobileMenuParents(document);
      }

      new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          mutation.addedNodes.forEach(function (node) {
            if (node.nodeType === 1) {
              enhanceMobileMenuParents(node);
            }
          });
        });
      }).observe(document.documentElement, {
        childList: true,
        subtree: true
      });
    }());
    </script>
    <?php
}