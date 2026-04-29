/**
 * nav-scroll.js
 *
 * Affiche des flèches chevron gauche/droite dans la barre de navigation
 * horizontale quand le menu déborde et peut être scrollé.
 */

function initNavScroll() {
  // init.js pose data-placement après un setTimeout(200) — on attend 300 ms
  setTimeout(() => {
    const menuContainer = document.querySelector('#nav .menu-container');
    if (!menuContainer || menuContainer.dataset.navScrollInit === 'true') return;
    menuContainer.dataset.navScrollInit = 'true';

    const navContent = menuContainer.closest('.nav-content');
    if (!navContent) return;

    // Créer les deux boutons flèche et les insérer autour du menu-container
    const btnLeft  = createArrowButton('left');
    const btnRight = createArrowButton('right');

    navContent.insertBefore(btnLeft, menuContainer);
    menuContainer.insertAdjacentElement('afterend', btnRight);

    // Scroll au clic
    btnLeft.addEventListener('click', () => {
      menuContainer.scrollBy({ left: -160, behavior: 'smooth' });
    });
    btnRight.addEventListener('click', () => {
      menuContainer.scrollBy({ left: 160, behavior: 'smooth' });
    });

    function syncDropdownState() {
      const hasOpenDropdown = Boolean(menuContainer.querySelector('#menu > li.show, #menu > li > ul.show, #menu .dropdown-menu.show'));
      menuContainer.classList.toggle('menu-container--dropdown-open', hasOpenDropdown);
      updateArrows();
    }

    function updateArrows() {
      if (menuContainer.classList.contains('menu-container--dropdown-open')) {
        btnLeft.classList.remove('nav-scroll-arrow--visible');
        btnRight.classList.remove('nav-scroll-arrow--visible');
        return;
      }

      const { scrollLeft, scrollWidth, clientWidth } = menuContainer;
      btnLeft.classList.toggle('nav-scroll-arrow--visible', scrollLeft > 2);
      btnRight.classList.toggle(
        'nav-scroll-arrow--visible',
        scrollLeft + clientWidth < scrollWidth - 2
      );
    }

    menuContainer.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows, { passive: true });

    // ResizeObserver : réagit si le contenu du menu change de taille
    if (typeof ResizeObserver !== 'undefined') {
      new ResizeObserver(updateArrows).observe(menuContainer);
    }

    if (typeof MutationObserver !== 'undefined') {
      new MutationObserver(() => {
        syncDropdownState();
      }).observe(menuContainer, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['class'],
      });
    }

    syncDropdownState();
    updateArrows();
  }, 300);
}

function createArrowButton(direction) {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = `nav-scroll-arrow nav-scroll-arrow--${direction}`;
  btn.setAttribute('aria-label', direction === 'left' ? 'Défiler vers la gauche' : 'Défiler vers la droite');
  btn.innerHTML = `<i class="bi bi-chevron-${direction}"></i>`;
  return btn;
}

export { initNavScroll };

