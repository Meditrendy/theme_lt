(() => {
  const desktopQuery = window.matchMedia('(min-width: 980px)');

  const getScrollAmount = (track) => {
    const item = track.querySelector('.x-col');
    const itemWidth = item ? item.getBoundingClientRect().width : track.clientWidth;
    return Math.max(1, Math.round(itemWidth + 16));
  };

  const updateButtons = (section, track) => {
    const prev = section.querySelector('.mt-home-color-carousel__button--prev');
    const next = section.querySelector('.mt-home-color-carousel__button--next');

    if (!prev || !next) return;

    const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth - 1);
    prev.disabled = track.scrollLeft <= 1;
    next.disabled = track.scrollLeft >= maxScroll;
  };

  const addControls = (section, titleRow, track) => {
    if (section.querySelector('.mt-home-color-carousel__controls')) return;

    const titleInner = titleRow ? (titleRow.querySelector(':scope > .x-row-inner') || titleRow) : section;
    const controls = document.createElement('div');
    controls.className = 'mt-home-color-carousel__controls';
    controls.innerHTML = `
      <button class="mt-home-color-carousel__button mt-home-color-carousel__button--prev" type="button" aria-label="Slinkti atgal"></button>
      <button class="mt-home-color-carousel__button mt-home-color-carousel__button--next" type="button" aria-label="Slinkti pirmyn"></button>
    `;

    titleInner.appendChild(controls);

    controls.addEventListener('click', (event) => {
      const button = event.target.closest('.mt-home-color-carousel__button');
      if (!button) return;

      const direction = button.classList.contains('mt-home-color-carousel__button--prev') ? -1 : 1;
      track.scrollBy({
        left: getScrollAmount(track) * direction,
        behavior: 'smooth',
      });
    });
  };

  const getColorLinks = (root) => (
    [...root.querySelectorAll('.x-image[href]')].filter((link) => {
      const href = link.getAttribute('href') || '';
      return href.includes('filters=color') || href.includes('mt_color');
    })
  );

  const enhanceColorSection = () => {
    let imageRow = document.querySelector('.homepage-colors');
    let section = imageRow ? imageRow.closest('.x-section') : null;
    let titleRow = null;

    if (imageRow && section) {
      titleRow = imageRow.previousElementSibling?.classList?.contains('x-row')
        ? imageRow.previousElementSibling
        : null;
    } else {
      const headings = [...document.querySelectorAll('h3')];
      const heading = headings.find((item) => {
        const text = item.textContent.trim().toLowerCase();
        return text.includes('pasirinkite') && text.includes('spalv');
      });

      if (!heading) return;

      section = heading.closest('.x-section');
      if (!section) return;

      const rows = [...section.querySelectorAll(':scope > .x-row')];
      titleRow = heading.closest('.x-row');
      imageRow = rows.find((row) => row !== titleRow && getColorLinks(row).length > 1);
    }

    if (!section || !imageRow || section.classList.contains('mt-home-color-carousel')) return;

    const track = imageRow.querySelector(':scope > .x-row-inner');

    if (!track) return;

    section.classList.add('mt-home-color-carousel');
    if (titleRow) {
      titleRow.classList.add('mt-home-color-carousel__header');
    }
    imageRow.classList.add('mt-home-color-carousel__row');
    track.classList.add('mt-home-color-carousel__track');

    addControls(section, titleRow, track);
    updateButtons(section, track);

    track.addEventListener('scroll', () => {
      window.requestAnimationFrame(() => updateButtons(section, track));
    }, { passive: true });

    desktopQuery.addEventListener('change', () => updateButtons(section, track));
    window.addEventListener('resize', () => updateButtons(section, track), { passive: true });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceColorSection);
  } else {
    enhanceColorSection();
  }
})();
