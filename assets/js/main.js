/**
 * Si Mosaic - Main JS
 */

/** @type {{ MIN_WIDTH?: number, MOBILE_MAX?: number, TABLET_MIN?: number, TABLET_MAX?: number, DESKTOP_MIN?: number }} */
const BREAKPOINTS = window.MOSAIC_BREAKPOINTS || {};

const getBreakpointValue = (key, fallback) => {
    const value = BREAKPOINTS?.[key];
    return typeof value === 'number' ? value : fallback;
};

const MIN_WIDTH = getBreakpointValue('MIN_WIDTH', 360);
const MOBILE_MAX = getBreakpointValue('MOBILE_MAX', 1279);
const TABLET_MIN = getBreakpointValue('TABLET_MIN', 1280);
const TABLET_MAX = getBreakpointValue('TABLET_MAX', 1919);
const DESKTOP_MIN = getBreakpointValue('DESKTOP_MIN', 1920);

const getViewportWidth = () => window.innerWidth || document.documentElement.clientWidth || 0;

const isMobile = (width) => width <= MOBILE_MAX;
const isTablet = (width) => width >= TABLET_MIN && width <= TABLET_MAX;
const isDesktop = (width) => width >= DESKTOP_MIN;

// Общие breakpoints для всего проекта (используется в Portfolio Slider)
const getPortfolioSlidesPerView = () => {
    const width = getViewportWidth();
    if (isDesktop(width)) return 4;
    if (isTablet(width)) return 3;
    return 1;
};

document.addEventListener('DOMContentLoaded', () => {
    initHeaderMenu();
    initPortfolioSlider();
    initRelatedProductsSlider();
    initProcessSlider();
    initNewsSlider();
    initCatalogHoverVideos();
    initBenefitsCarouselDrag();
    initBenefitsCarouselAutoplay();
    initShowroomHeroSlider();
    initShowroomCollectionsSlider();
    initShowroomEventsSlider();
    initShowroomMap();
    initShowroomLightbox();
});

/**
 * Benefits mobile carousel: mouse drag + touch swipe without breaking layout.
 */
const initBenefitsCarouselDrag = () => {
    /** @type {HTMLElement | null} */
    const track = document.querySelector('[data-benefits-carousel-track]');
    if (!track) return;

    const isEnabled = () => {
        const width = getViewportWidth();
        return isMobile(width);
    };

    let isPointerDown = false;
    let startX = 0;
    let startScrollLeft = 0;

    const handlePointerDown = (e) => {
        if (!isEnabled()) return;
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        isPointerDown = true;
        startX = e.clientX;
        startScrollLeft = track.scrollLeft;
        track.setPointerCapture(e.pointerId);
        track.classList.add('is-dragging');
    };

    const handlePointerMove = (e) => {
        if (!isPointerDown) return;
        if (!isEnabled()) return;
        const deltaX = e.clientX - startX;
        track.scrollLeft = startScrollLeft - deltaX;
    };

    const endDrag = (e) => {
        if (!isPointerDown) return;
        isPointerDown = false;
        try {
            track.releasePointerCapture(e.pointerId);
        } catch (_) {
            // ignore
        }
        track.classList.remove('is-dragging');
    };

    track.addEventListener('pointerdown', handlePointerDown);
    track.addEventListener('pointermove', handlePointerMove);
    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('pointerleave', endDrag);
};

/**
 * Benefits mobile carousel: continuous autoplay loop (like Portfolio).
 */
const initBenefitsCarouselAutoplay = () => {
    /** @type {HTMLElement | null} */
    const track = document.querySelector('[data-benefits-carousel-track]');
    if (!track) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    /** @type {{ rafId: number | null, lastTs: number | null, originalWidth: number, pausedUntil: number }} */
    const state = { rafId: null, lastTs: null, originalWidth: 0, pausedUntil: 0 };
    const SPEED_PX_PER_S = 38;
    const USER_PAUSE_MS = 2200;

    const stop = () => {
        if (state.rafId !== null) cancelAnimationFrame(state.rafId);
        state.rafId = null;
        state.lastTs = null;
        track.classList.remove('is-autoplay');
    };

    const ensureClones = () => {
        Array.from(track.querySelectorAll('[data-benefits-clone="1"]')).forEach((n) => n.remove());
        state.originalWidth = track.scrollWidth;
        if (state.originalWidth <= 0) return false;

        const originals = Array.from(track.children);
        if (originals.length === 0) return false;

        while (track.scrollWidth < state.originalWidth * 2) {
            originals.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.benefitsClone = '1';
                track.appendChild(clone);
            });
        }
        return true;
    };

    const tick = (ts) => {
        const width = getViewportWidth();
        if (!isMobile(width)) {
            stop();
            return;
        }

        if (track.classList.contains('is-dragging')) {
            state.lastTs = ts;
            state.rafId = requestAnimationFrame(tick);
            return;
        }

        if (Date.now() < state.pausedUntil) {
            state.lastTs = ts;
            state.rafId = requestAnimationFrame(tick);
            return;
        }

        track.classList.add('is-autoplay');
        if (state.lastTs === null) state.lastTs = ts;
        const dt = Math.min(48, ts - state.lastTs);
        state.lastTs = ts;

        const dx = (SPEED_PX_PER_S * dt) / 1000;
        track.scrollLeft += dx;

        if (state.originalWidth > 0 && track.scrollLeft >= state.originalWidth) {
            track.scrollLeft -= state.originalWidth;
        }

        state.rafId = requestAnimationFrame(tick);
    };

    const start = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return;
        stop();
        if (!ensureClones()) return;
        track.scrollLeft = 0;
        state.pausedUntil = Date.now() + 700;
        state.rafId = requestAnimationFrame(tick);
    };

    const pauseByUser = () => {
        state.pausedUntil = Date.now() + USER_PAUSE_MS;
        track.classList.remove('is-autoplay');
    };

    track.addEventListener('pointerdown', pauseByUser, { passive: true });
    track.addEventListener('touchstart', pauseByUser, { passive: true });
    track.addEventListener('wheel', pauseByUser, { passive: true });
    // Do NOT pause on 'scroll' here: autoplay itself updates scrollLeft and would self-pause.

    window.addEventListener('resize', () => {
        window.clearTimeout(track.__benefitsAutoplayResizeTimer);
        track.__benefitsAutoplayResizeTimer = window.setTimeout(() => {
            start();
        }, 120);
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
            return;
        }
        start();
    });

    start();
};

/**
 * Catalog hover videos (tablet+desktop): fade image -> video on hover, play once, reset on leave.
 */
const initCatalogHoverVideos = () => {
    const cards = Array.from(document.querySelectorAll('[data-catalog-card][data-has-video="1"]'));
    if (cards.length === 0) return;

    const isTabletOrDesktopHover = () => {
        const width = getViewportWidth();
        return !isMobile(width) && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    };

    const resetVideo = (video) => {
        try {
            video.pause();
            video.currentTime = 0;
        } catch (_) {
            // ignore
        }
    };

    const playVideo = async (video) => {
        try {
            video.currentTime = 0;
            await video.play();
        } catch (_) {
            // ignore autoplay restrictions
        }
    };

    cards.forEach((card) => {
        const video = card.querySelector('[data-catalog-video]');
        if (!(video instanceof HTMLVideoElement)) return;

        // Ensure a stable initial state
        resetVideo(video);
        card.dataset.videoReady = '0';

        const setReady = () => {
            card.dataset.videoReady = '1';
        };

        if (video.readyState >= 2) {
            setReady();
        } else {
            // Warm up once so hover doesn't cause a black flash
            try {
                video.load();
            } catch (_) {
                // ignore
            }
            video.addEventListener('loadeddata', setReady, { once: true });
            video.addEventListener('canplay', setReady, { once: true });
        }

        const handleEnter = () => {
            if (!isTabletOrDesktopHover()) return;
            playVideo(video);
        };

        const handleLeave = () => {
            if (!isTabletOrDesktopHover()) return;
            resetVideo(video);
        };

        card.addEventListener('mouseenter', handleEnter);
        card.addEventListener('mouseleave', handleLeave);

        video.addEventListener('ended', () => {
            // Stop on last frame, do not loop
            try {
                video.pause();
            } catch (_) {
                // ignore
            }
        });
    });
};

/**
 * Header Menu (Mobile: <=1279 offcanvas, Tablet: 1280-1919 dropdown, Desktop: >=1920)
 */
const initHeaderMenu = () => {
    const dropdownToggle = document.querySelector('[data-header-menu-toggle="dropdown"]');
    const dropdownPanel = document.querySelector('[data-header-menu-panel="dropdown"]');

    const mobileToggle = document.querySelector('[data-header-menu-toggle="mobile"]');
    const mobilePanel = document.querySelector('[data-header-menu-panel="mobile"]');
    const mobileOverlay = document.querySelector('[data-header-menu-overlay="mobile"]');
    const mobileCloseButtons = document.querySelectorAll('[data-header-menu-close="mobile"]');

    const hasAnyMenu = !!(dropdownToggle && dropdownPanel) || !!(mobileToggle && mobilePanel && mobileOverlay);
    if (!hasAnyMenu) return;

    /** @type {HTMLElement | null} */
    let lastFocusedElement = null;
    /** @type {{ bodyOverflow: string, bodyPaddingRight: string }} */
    const scrollState = { bodyOverflow: '', bodyPaddingRight: '' };

    const setButtonExpanded = (button, isExpanded) => {
        if (!button) return;
        button.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    };

    const setDropdownIcons = (button, isExpanded) => {
        // Анимация управляется через CSS на основе aria-expanded
        // Ничего не делаем здесь, CSS сам обработает
    };

    const focusFirstFocusable = (root) => {
        if (!root) return;
        const el = root.querySelector('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
        if (!(el instanceof HTMLElement)) return;
        el.focus();
    };

    const rememberFocus = () => {
        lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    };

    const restoreFocus = () => {
        if (!lastFocusedElement) return;
        lastFocusedElement.focus();
        lastFocusedElement = null;
    };

    const openDropdown = () => {
        if (!dropdownToggle || !dropdownPanel) return;
        if (dropdownPanel.classList.contains('is-open')) return;
        // Работает только на планшете (1280-1919)
        if (!isTablet(getViewportWidth())) return;

        rememberFocus();
        dropdownPanel.classList.remove('hidden');
        // Небольшая задержка для запуска анимации
        requestAnimationFrame(() => {
            dropdownPanel.classList.add('is-open');
        });
        setButtonExpanded(dropdownToggle, true);
        setDropdownIcons(dropdownToggle, true);
        focusFirstFocusable(dropdownPanel);
    };

    const closeDropdown = () => {
        if (!dropdownToggle || !dropdownPanel) return;
        if (!dropdownPanel.classList.contains('is-open')) return;

        dropdownPanel.classList.remove('is-open');
        setButtonExpanded(dropdownToggle, false);
        setDropdownIcons(dropdownToggle, false);
        restoreFocus();
        
        // Добавляем hidden после завершения анимации
        setTimeout(() => {
            if (!dropdownPanel.classList.contains('is-open')) {
                dropdownPanel.classList.add('hidden');
            }
        }, 300);
    };

    const toggleDropdown = () => {
        if (!dropdownToggle || !dropdownPanel) return;
        // Работает только на планшете (1280-1919)
        if (!isTablet(getViewportWidth())) {
            closeDropdown();
            return;
        }
        if (!dropdownPanel.classList.contains('is-open')) {
            openDropdown();
            return;
        }
        closeDropdown();
    };

    const lockBodyScroll = () => {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        scrollState.bodyOverflow = document.body.style.overflow || '';
        scrollState.bodyPaddingRight = document.body.style.paddingRight || '';

        document.body.style.overflow = 'hidden';
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = `${scrollbarWidth}px`;
        }
    };

    const unlockBodyScroll = () => {
        document.body.style.overflow = scrollState.bodyOverflow;
        document.body.style.paddingRight = scrollState.bodyPaddingRight;
    };

    const openMobile = () => {
        if (!mobileToggle || !mobilePanel || !mobileOverlay) return;
        if (!mobileOverlay.classList.contains('hidden')) return;

        rememberFocus();
        mobileOverlay.classList.remove('hidden');
        mobilePanel.classList.remove('translate-x-full');
        setButtonExpanded(mobileToggle, true);
        lockBodyScroll();
        focusFirstFocusable(mobilePanel);
    };

    const closeMobile = () => {
        if (!mobileToggle || !mobilePanel || !mobileOverlay) return;
        if (mobileOverlay.classList.contains('hidden')) return;

        mobilePanel.classList.add('translate-x-full');
        setButtonExpanded(mobileToggle, false);
        unlockBodyScroll();
        restoreFocus();

        window.setTimeout(() => {
            mobileOverlay.classList.add('hidden');
        }, 320);
    };

    const toggleMobile = () => {
        if (!mobileOverlay || !mobilePanel) return;
        if (mobileOverlay.classList.contains('hidden')) {
            openMobile();
            return;
        }
        closeMobile();
    };

    if (dropdownToggle && dropdownPanel) {
        dropdownToggle.addEventListener('click', (e) => {
            e.preventDefault();
            toggleDropdown();
        });

        dropdownPanel.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const link = target.closest('a[href]');
            if (!link) return;
            closeDropdown();
        });
    }

    if (mobileToggle && mobilePanel && mobileOverlay) {
        mobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            toggleMobile();
        });

        mobileOverlay.addEventListener('click', () => {
            closeMobile();
        });

        mobilePanel.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const link = target.closest('a[href]');
            if (!link) return;
            closeMobile();
        });
    }

    document.addEventListener('click', (e) => {
        if (!dropdownToggle || !dropdownPanel) return;
        if (!dropdownPanel.classList.contains('is-open')) return;
        // Работает только на планшете (1280-1919)
        if (!isTablet(getViewportWidth())) {
            closeDropdown();
            return;
        }

        const target = e.target;
        if (!(target instanceof Node)) return;
        if (dropdownPanel.contains(target) || dropdownToggle.contains(target)) return;
        closeDropdown();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeDropdown();
        closeMobile();
    });

    let resizeTimeout;
    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimeout);
        resizeTimeout = window.setTimeout(() => {
            const width = getViewportWidth();
            // если ушли в десктоп (>=1920) или мобилку (<=1279) — закрыть dropdown
            if (isDesktop(width) || isMobile(width)) {
                closeDropdown();
            }
            // если ушли с мобилки (>1279) — закрыть offcanvas
            if (!isMobile(width)) {
                closeMobile();
            }
        }, 150);
    });
};

/**
 * Portfolio Slider
 */
const initPortfolioSlider = () => {
    const slider = document.querySelector('.portfolio-slider');
    if (!slider) return;

    const track = slider.querySelector('.portfolio-track');
    const slides = slider.querySelectorAll('.portfolio-slide');
    const prevBtn = document.querySelector('.portfolio-prev');
    const nextBtn = document.querySelector('.portfolio-next');

    if (!track || slides.length === 0) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setScrollMode = () => {
        slider.classList.add('is-scroll');
        track.style.transform = 'none';
        track.style.transition = 'none';
    };

    const setTranslateMode = () => {
        slider.classList.remove('is-scroll');
        slider.classList.remove('is-autoplay');
        track.style.transition = '';
    };

    // Функция получения ширины слайда с учетом gap
    const getSlideStep = () => {
        const slide = slides[0];
        const style = window.getComputedStyle(track);
        const gap = parseInt(style.gap) || 24;
        return slide.offsetWidth + gap;
    };

    let currentIndex = 0;

    const updateSlider = () => {
        if (isMobile(getViewportWidth())) {
            // Mobile uses native scrolling/swipe; no translate transforms.
            setScrollMode();
            return;
        }

        setTranslateMode();
        const step = getSlideStep();
        const offset = currentIndex * step;
        track.style.transform = `translateX(-${offset}px)`;
    };

    const handlePrev = () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    };

    const handleNext = () => {
        const slidesPerView = getPortfolioSlidesPerView();
        const maxIdx = Math.max(0, slides.length - slidesPerView);
        if (currentIndex < maxIdx) {
            currentIndex++;
            updateSlider();
        }
    };

    /**
     * Marquee autoplay:
     * - tablet+desktop: transform-based loop
     * - mobile: scrollLeft-based loop (keeps swipe possible)
     */
    const SPEED_PX_PER_S = 38;

    /** @type {{ rafId: number | null, lastTs: number | null, offsetX: number, originalWidth: number, paused: boolean }} */
    const marqueeTransform = { rafId: null, lastTs: null, offsetX: 0, originalWidth: 0, paused: false };
    /** @type {{ rafId: number | null, lastTs: number | null, originalWidth: number, pausedUntil: number }} */
    const marqueeScroll = { rafId: null, lastTs: null, originalWidth: 0, pausedUntil: 0 };
    const MOBILE_USER_PAUSE_MS = 2200;

    const stopMarquee = () => {
        if (marqueeTransform.rafId !== null) cancelAnimationFrame(marqueeTransform.rafId);
        marqueeTransform.rafId = null;
        marqueeTransform.lastTs = null;
        if (marqueeScroll.rafId !== null) cancelAnimationFrame(marqueeScroll.rafId);
        marqueeScroll.rafId = null;
        marqueeScroll.lastTs = null;
        slider.classList.remove('is-autoplay');
    };

    // Attach arrow button event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopMarquee();
            handlePrev();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopMarquee();
            handleNext();
        });
    }

    const ensureClonesForTransform = () => {
        const width = getViewportWidth();
        if (isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        // reset any previous clones
        Array.from(track.querySelectorAll('[data-portfolio-clone="1"]')).forEach((n) => n.remove());

        const originalChildren = Array.from(track.children);
        if (originalChildren.length === 0) return false;

        // Measure original width (one set)
        marqueeTransform.originalWidth = track.scrollWidth;
        if (marqueeTransform.originalWidth <= 0) return false;

        // Append enough clones to cover at least 2x width for seamless wrap
        while (track.scrollWidth < marqueeTransform.originalWidth * 2) {
            originalChildren.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.portfolioClone = '1';
                track.appendChild(clone);
            });
        }

        track.style.transition = 'none';
        track.style.willChange = 'transform';
        slider.classList.remove('is-scroll');
        return true;
    };

    const tickTransform = (ts) => {
        if (marqueeTransform.paused) {
            marqueeTransform.lastTs = ts;
            marqueeTransform.rafId = requestAnimationFrame(tickTransform);
            return;
        }

        if (marqueeTransform.lastTs === null) marqueeTransform.lastTs = ts;
        const dt = Math.min(48, ts - marqueeTransform.lastTs);
        marqueeTransform.lastTs = ts;

        marqueeTransform.offsetX += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeTransform.originalWidth > 0 && marqueeTransform.offsetX >= marqueeTransform.originalWidth) {
            marqueeTransform.offsetX -= marqueeTransform.originalWidth;
        }
        track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const startMarqueeDesktop = () => {
        stopMarquee();
        marqueeTransform.offsetX = 0;
        marqueeTransform.paused = false;
        if (!ensureClonesForTransform()) {
            updateSlider();
            return;
        }
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const ensureClonesForScroll = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-portfolio-clone="1"]')).forEach((n) => n.remove());
        marqueeScroll.originalWidth = track.scrollWidth;
        if (marqueeScroll.originalWidth <= 0) return false;

        const originals = Array.from(track.children);
        if (originals.length === 0) return false;

        while (track.scrollWidth < marqueeScroll.originalWidth * 2) {
            originals.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.portfolioClone = '1';
                track.appendChild(clone);
            });
        }
        return true;
    };

    const tickScroll = (ts) => {
        const width = getViewportWidth();
        if (!isMobile(width)) {
            stopMarquee();
            return;
        }

        if (Date.now() < marqueeScroll.pausedUntil) {
            marqueeScroll.lastTs = ts;
            marqueeScroll.rafId = requestAnimationFrame(tickScroll);
            return;
        }

        slider.classList.add('is-autoplay');
        if (marqueeScroll.lastTs === null) marqueeScroll.lastTs = ts;
        const dt = Math.min(48, ts - marqueeScroll.lastTs);
        marqueeScroll.lastTs = ts;

        slider.scrollLeft += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeScroll.originalWidth > 0 && slider.scrollLeft >= marqueeScroll.originalWidth) {
            slider.scrollLeft -= marqueeScroll.originalWidth;
        }
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    const startMarqueeMobile = () => {
        stopMarquee();
        setScrollMode();
        if (!ensureClonesForScroll()) return;
        slider.scrollLeft = 0;
        marqueeScroll.pausedUntil = Date.now() + 700;
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    // pause on hover/focus for readability
    slider.addEventListener('mouseenter', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('mouseleave', () => {
        marqueeTransform.paused = false;
    });
    slider.addEventListener('focusin', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('focusout', () => {
        marqueeTransform.paused = false;
    });

    // mobile user interaction pauses autoplay so swipe feels normal
    const pauseMobileByUser = () => {
        marqueeScroll.pausedUntil = Date.now() + MOBILE_USER_PAUSE_MS;
        slider.classList.remove('is-autoplay');
    };
    slider.addEventListener('pointerdown', pauseMobileByUser, { passive: true });
    slider.addEventListener('touchstart', pauseMobileByUser, { passive: true });
    slider.addEventListener('wheel', pauseMobileByUser, { passive: true });
    // Do NOT pause on 'scroll' here: marquee updates scrollLeft continuously.

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopMarquee();
            return;
        }
        const width = getViewportWidth();
        if (isMobile(width)) {
            startMarqueeMobile();
            return;
        }
        startMarqueeDesktop();
    });

    // Recalculate on resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (isMobile(getViewportWidth())) {
                currentIndex = 0;
                setScrollMode();
                slider.scrollLeft = 0;
                stopMarquee();
                return;
            }

            startMarqueeDesktop();
        }, 100);
    });

    // Initialize correct mode on load
    if (isMobile(getViewportWidth())) {
        startMarqueeMobile();
    } else {
        startMarqueeDesktop();
    }
};

/**
 * Related Products Slider (похожие товары)
 * Если товаров <= 3 - статичное отображение без анимации
 * Если товаров > 3 - бегущая лента (marquee)
 */
const initRelatedProductsSlider = () => {
    const slider = document.querySelector('.related-slider');
    if (!slider) return;

    const track = slider.querySelector('.related-track');
    const slides = slider.querySelectorAll('.related-slide');
    const prevBtn = document.querySelector('.related-prev');
    const nextBtn = document.querySelector('.related-next');

    if (!track || slides.length === 0) return;

    const MIN_SLIDES_FOR_MARQUEE = 4; // Минимум товаров для запуска marquee
    const isStaticMode = slides.length < MIN_SLIDES_FOR_MARQUEE;

    // Если товаров мало - статичный режим
    if (isStaticMode) {
        slider.classList.add('is-static');
        // Скрываем кнопки навигации
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return; // Выходим, никакой анимации не нужно
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setScrollMode = () => {
        slider.classList.add('is-scroll');
        track.style.transform = 'none';
        track.style.transition = 'none';
    };

    const setTranslateMode = () => {
        slider.classList.remove('is-scroll');
        slider.classList.remove('is-autoplay');
        track.style.transition = '';
    };

    const getSlideStep = () => {
        const slide = slides[0];
        const style = window.getComputedStyle(track);
        const gap = parseInt(style.gap) || 24;
        return slide.offsetWidth + gap;
    };

    let currentIndex = 0;

    const updateSlider = () => {
        if (isMobile(getViewportWidth())) {
            setScrollMode();
            return;
        }

        setTranslateMode();
        const step = getSlideStep();
        const offset = currentIndex * step;
        track.style.transform = `translateX(-${offset}px)`;
    };

    const handlePrev = () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    };

    const handleNext = () => {
        const slidesPerView = getPortfolioSlidesPerView();
        const maxIdx = Math.max(0, slides.length - slidesPerView);
        if (currentIndex < maxIdx) {
            currentIndex++;
            updateSlider();
        }
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', handlePrev);
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', handleNext);
    }

    /**
     * Marquee autoplay (бегущая лента)
     */
    const SPEED_PX_PER_S = 38;

    /** @type {{ rafId: number | null, lastTs: number | null, offsetX: number, originalWidth: number, paused: boolean }} */
    const marqueeTransform = { rafId: null, lastTs: null, offsetX: 0, originalWidth: 0, paused: false };
    /** @type {{ rafId: number | null, lastTs: number | null, originalWidth: number, pausedUntil: number }} */
    const marqueeScroll = { rafId: null, lastTs: null, originalWidth: 0, pausedUntil: 0 };
    const MOBILE_USER_PAUSE_MS = 2200;

    const stopMarquee = () => {
        if (marqueeTransform.rafId !== null) cancelAnimationFrame(marqueeTransform.rafId);
        marqueeTransform.rafId = null;
        marqueeTransform.lastTs = null;
        if (marqueeScroll.rafId !== null) cancelAnimationFrame(marqueeScroll.rafId);
        marqueeScroll.rafId = null;
        marqueeScroll.lastTs = null;
        slider.classList.remove('is-autoplay');
    };

    const ensureClonesForTransform = () => {
        const width = getViewportWidth();
        if (isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-related-clone="1"]')).forEach((n) => n.remove());

        const originalChildren = Array.from(track.children);
        if (originalChildren.length === 0) return false;

        marqueeTransform.originalWidth = track.scrollWidth;
        if (marqueeTransform.originalWidth <= 0) return false;

        while (track.scrollWidth < marqueeTransform.originalWidth * 2) {
            originalChildren.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.relatedClone = '1';
                track.appendChild(clone);
            });
        }

        track.style.transition = 'none';
        track.style.willChange = 'transform';
        slider.classList.remove('is-scroll');
        return true;
    };

    const tickTransform = (ts) => {
        if (marqueeTransform.paused) {
            marqueeTransform.lastTs = ts;
            marqueeTransform.rafId = requestAnimationFrame(tickTransform);
            return;
        }

        if (marqueeTransform.lastTs === null) marqueeTransform.lastTs = ts;
        const dt = Math.min(48, ts - marqueeTransform.lastTs);
        marqueeTransform.lastTs = ts;

        marqueeTransform.offsetX += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeTransform.originalWidth > 0 && marqueeTransform.offsetX >= marqueeTransform.originalWidth) {
            marqueeTransform.offsetX -= marqueeTransform.originalWidth;
        }
        track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const startMarqueeDesktop = () => {
        stopMarquee();
        marqueeTransform.offsetX = 0;
        marqueeTransform.paused = false;
        if (!ensureClonesForTransform()) {
            updateSlider();
            return;
        }
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const ensureClonesForScroll = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-related-clone="1"]')).forEach((n) => n.remove());
        marqueeScroll.originalWidth = track.scrollWidth;
        if (marqueeScroll.originalWidth <= 0) return false;

        const originals = Array.from(track.children);
        if (originals.length === 0) return false;

        while (track.scrollWidth < marqueeScroll.originalWidth * 2) {
            originals.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.relatedClone = '1';
                track.appendChild(clone);
            });
        }
        return true;
    };

    const tickScroll = (ts) => {
        const width = getViewportWidth();
        if (!isMobile(width)) {
            stopMarquee();
            return;
        }

        if (Date.now() < marqueeScroll.pausedUntil) {
            marqueeScroll.lastTs = ts;
            marqueeScroll.rafId = requestAnimationFrame(tickScroll);
            return;
        }

        slider.classList.add('is-autoplay');
        if (marqueeScroll.lastTs === null) marqueeScroll.lastTs = ts;
        const dt = Math.min(48, ts - marqueeScroll.lastTs);
        marqueeScroll.lastTs = ts;

        slider.scrollLeft += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeScroll.originalWidth > 0 && slider.scrollLeft >= marqueeScroll.originalWidth) {
            slider.scrollLeft -= marqueeScroll.originalWidth;
        }
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    const startMarqueeMobile = () => {
        stopMarquee();
        setScrollMode();
        if (!ensureClonesForScroll()) return;
        slider.scrollLeft = 0;
        marqueeScroll.pausedUntil = Date.now() + 700;
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    // pause on hover/focus
    slider.addEventListener('mouseenter', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('mouseleave', () => {
        marqueeTransform.paused = false;
    });
    slider.addEventListener('focusin', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('focusout', () => {
        marqueeTransform.paused = false;
    });

    // mobile user interaction pauses autoplay
    const pauseMobileByUser = () => {
        marqueeScroll.pausedUntil = Date.now() + MOBILE_USER_PAUSE_MS;
        slider.classList.remove('is-autoplay');
    };
    slider.addEventListener('pointerdown', pauseMobileByUser, { passive: true });
    slider.addEventListener('touchstart', pauseMobileByUser, { passive: true });
    slider.addEventListener('wheel', pauseMobileByUser, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopMarquee();
            return;
        }
        const width = getViewportWidth();
        if (isMobile(width)) {
            startMarqueeMobile();
            return;
        }
        startMarqueeDesktop();
    });

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (isMobile(getViewportWidth())) {
                currentIndex = 0;
                setScrollMode();
                slider.scrollLeft = 0;
                stopMarquee();
                return;
            }

            startMarqueeDesktop();
        }, 100);
    });

    // Initialize
    if (isMobile(getViewportWidth())) {
        startMarqueeMobile();
    } else {
        startMarqueeDesktop();
    }
};

/**
 * Process Slider
 */
const initProcessSlider = () => {
    const slider = document.querySelector('.process-slider');
    if (!slider) return;

    const track = slider.querySelector('.process-track');
    const slides = slider.querySelectorAll('.process-slide');
    const prevBtn = document.querySelector('.process-prev');
    const nextBtn = document.querySelector('.process-next');

    if (!track || slides.length === 0) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setScrollMode = () => {
        slider.classList.add('is-scroll');
        track.style.transform = 'none';
        track.style.transition = 'none';
    };

    const setTranslateMode = () => {
        slider.classList.remove('is-scroll');
        slider.classList.remove('is-autoplay');
        track.style.transition = '';
    };

    const getSlideStep = () => {
        const slide = slides[0];
        const style = window.getComputedStyle(track);
        const gap = parseInt(style.gap) || 24;
        return slide.offsetWidth + gap;
    };

    let currentIndex = 0;

    const updateSlider = () => {
        if (isMobile(getViewportWidth())) {
            setScrollMode();
            return;
        }

        setTranslateMode();
        const step = getSlideStep();
        const offset = currentIndex * step;
        track.style.transform = `translateX(-${offset}px)`;
    };

    const handlePrev = () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    };

    const handleNext = () => {
        const slidesPerView = 4;
        const maxIdx = Math.max(0, slides.length - slidesPerView);
        if (currentIndex < maxIdx) {
            currentIndex++;
            updateSlider();
        }
    };

    /**
     * Marquee autoplay (аналогично Portfolio)
     */
    const SPEED_PX_PER_S = 38;

    const marqueeTransform = { rafId: null, lastTs: null, offsetX: 0, originalWidth: 0, paused: false };
    const marqueeScroll = { rafId: null, lastTs: null, originalWidth: 0, pausedUntil: 0 };
    const MOBILE_USER_PAUSE_MS = 2200;

    const stopMarquee = () => {
        if (marqueeTransform.rafId !== null) cancelAnimationFrame(marqueeTransform.rafId);
        marqueeTransform.rafId = null;
        marqueeTransform.lastTs = null;
        if (marqueeScroll.rafId !== null) cancelAnimationFrame(marqueeScroll.rafId);
        marqueeScroll.rafId = null;
        marqueeScroll.lastTs = null;
        slider.classList.remove('is-autoplay');
    };

    // Attach arrow button event listeners (after stopMarquee is defined)
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopMarquee();
            handlePrev();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopMarquee();
            handleNext();
        });
    }

    const ensureClonesForTransform = () => {
        const width = getViewportWidth();
        if (isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        // reset any previous clones
        Array.from(track.querySelectorAll('[data-process-clone="1"]')).forEach((n) => n.remove());

        const originalChildren = Array.from(track.children);
        if (originalChildren.length === 0) return false;

        // Measure original width (one set)
        marqueeTransform.originalWidth = track.scrollWidth;
        if (marqueeTransform.originalWidth <= 0) return false;

        // Append enough clones to cover at least 2x width for seamless wrap
        while (track.scrollWidth < marqueeTransform.originalWidth * 2) {
            originalChildren.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.processClone = '1';
                track.appendChild(clone);
            });
        }

        track.style.transition = 'none';
        track.style.willChange = 'transform';
        slider.classList.remove('is-scroll');
        return true;
    };

    const tickTransform = (ts) => {
        if (marqueeTransform.paused) {
            marqueeTransform.lastTs = ts;
            marqueeTransform.rafId = requestAnimationFrame(tickTransform);
            return;
        }

        if (marqueeTransform.lastTs === null) marqueeTransform.lastTs = ts;
        const dt = Math.min(48, ts - marqueeTransform.lastTs);
        marqueeTransform.lastTs = ts;

        marqueeTransform.offsetX += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeTransform.originalWidth > 0 && marqueeTransform.offsetX >= marqueeTransform.originalWidth) {
            marqueeTransform.offsetX -= marqueeTransform.originalWidth;
        }
        track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const startMarqueeDesktop = () => {
        stopMarquee();
        marqueeTransform.offsetX = 0;
        marqueeTransform.paused = false;
        if (!ensureClonesForTransform()) {
            updateSlider();
            return;
        }
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const ensureClonesForScroll = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-process-clone="1"]')).forEach((n) => n.remove());
        marqueeScroll.originalWidth = track.scrollWidth;
        if (marqueeScroll.originalWidth <= 0) return false;

        const originals = Array.from(track.children);
        if (originals.length === 0) return false;

        while (track.scrollWidth < marqueeScroll.originalWidth * 2) {
            originals.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.processClone = '1';
                track.appendChild(clone);
            });
        }
        return true;
    };

    const tickScroll = (ts) => {
        const width = getViewportWidth();
        if (!isMobile(width)) {
            stopMarquee();
            return;
        }

        if (Date.now() < marqueeScroll.pausedUntil) {
            marqueeScroll.lastTs = ts;
            marqueeScroll.rafId = requestAnimationFrame(tickScroll);
            return;
        }

        slider.classList.add('is-autoplay');
        if (marqueeScroll.lastTs === null) marqueeScroll.lastTs = ts;
        const dt = Math.min(48, ts - marqueeScroll.lastTs);
        marqueeScroll.lastTs = ts;

        slider.scrollLeft += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeScroll.originalWidth > 0 && slider.scrollLeft >= marqueeScroll.originalWidth) {
            slider.scrollLeft -= marqueeScroll.originalWidth;
        }
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    const startMarqueeMobile = () => {
        stopMarquee();
        setScrollMode();
        if (!ensureClonesForScroll()) return;
        slider.scrollLeft = 0;
        marqueeScroll.pausedUntil = Date.now() + 700;
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    // pause on hover/focus for readability
    slider.addEventListener('mouseenter', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('mouseleave', () => {
        marqueeTransform.paused = false;
    });
    slider.addEventListener('focusin', () => {
        marqueeTransform.paused = true;
    });
    slider.addEventListener('focusout', () => {
        marqueeTransform.paused = false;
    });

    // mobile user interaction pauses autoplay so swipe feels normal
    const pauseMobileByUser = () => {
        marqueeScroll.pausedUntil = Date.now() + MOBILE_USER_PAUSE_MS;
        slider.classList.remove('is-autoplay');
    };
    slider.addEventListener('pointerdown', pauseMobileByUser, { passive: true });
    slider.addEventListener('touchstart', pauseMobileByUser, { passive: true });
    slider.addEventListener('wheel', pauseMobileByUser, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopMarquee();
            return;
        }
        const width = getViewportWidth();
        if (isMobile(width)) {
            startMarqueeMobile();
            return;
        }
        startMarqueeDesktop();
    });

    // Recalculate on resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (isMobile(getViewportWidth())) {
                currentIndex = 0;
                setScrollMode();
                slider.scrollLeft = 0;
                stopMarquee();
                startMarqueeMobile();
                return;
            }

            startMarqueeDesktop();
        }, 100);
    });

    // Initialize correct mode on load
    if (isMobile(getViewportWidth())) {
        startMarqueeMobile();
    } else {
        startMarqueeDesktop();
    }
};

/**
 * News Slider
 */
const initNewsSlider = () => {
    const slider = document.querySelector('.news-slider');
    if (!slider) return;

    const track = slider.querySelector('.news-track');
    const slides = slider.querySelectorAll('.news-slide');
    const prevBtn = document.querySelector('.news-prev');
    const nextBtn = document.querySelector('.news-next');

    if (!track || slides.length === 0) return;

    const setScrollMode = () => {
        slider.classList.add('is-scroll');
        track.style.transform = 'none';
        track.style.transition = 'none';
    };

    const getNewsSlidesPerView = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return 4;
        if (width > MIN_WIDTH) return 2;
        return 1;
    };

    const getSlideStep = () => {
        const slide = slides[0];
        const style = window.getComputedStyle(track);
        const gap = parseInt(style.gap) || 24;
        return slide.offsetWidth + gap;
    };

    let currentIndex = 0;

    const updateSlider = () => {
        if (isMobile(getViewportWidth())) {
            setScrollMode();
            return;
        }
        const step = getSlideStep();
        const offset = currentIndex * step;
        track.style.transform = `translateX(-${offset}px)`;
    };

    const handlePrev = () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    };

    const handleNext = () => {
        const slidesPerView = getNewsSlidesPerView();
        const maxIdx = Math.max(0, slides.length - slidesPerView);
        if (currentIndex < maxIdx) {
            currentIndex++;
            updateSlider();
        }
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', handlePrev);
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', handleNext);
    }

    // init
    updateSlider();

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (isMobile(getViewportWidth())) {
                currentIndex = 0;
                setScrollMode();
                slider.scrollLeft = 0;
                return;
            }
            const slidesPerView = getNewsSlidesPerView();
            const maxIdx = Math.max(0, slides.length - slidesPerView);
            if (currentIndex > maxIdx) {
                currentIndex = maxIdx;
            }
            updateSlider();
        }, 100);
    });
};

/**
 * Showroom Hero Slider (full-width gallery)
 */
const initShowroomHeroSlider = () => {
    const slider = document.querySelector('[data-showroom-hero-slider]');
    if (!slider) return;

    const track = slider.querySelector('[data-showroom-hero-track]');
    const slides = slider.querySelectorAll('[data-showroom-hero-slide]');
    const prevBtn = slider.querySelector('[data-showroom-hero-prev]');
    const nextBtn = slider.querySelector('[data-showroom-hero-next]');

    if (!track || slides.length <= 1) return;

    let currentIndex = 0;
    const totalSlides = slides.length;

    const updateSlider = () => {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
    };

    const handlePrev = () => {
        currentIndex = currentIndex <= 0 ? totalSlides - 1 : currentIndex - 1;
        updateSlider();
    };

    const handleNext = () => {
        currentIndex = currentIndex >= totalSlides - 1 ? 0 : currentIndex + 1;
        updateSlider();
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', handlePrev);
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', handleNext);
    }

    // Auto-advance every 5 seconds
    let autoplayInterval = setInterval(handleNext, 5000);

    slider.addEventListener('mouseenter', () => {
        clearInterval(autoplayInterval);
    });

    slider.addEventListener('mouseleave', () => {
        autoplayInterval = setInterval(handleNext, 5000);
    });

    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                handleNext();
            } else {
                handlePrev();
            }
        }
    }, { passive: true });
};

/**
 * Showroom Collections Slider (marquee like Portfolio if > 4 items)
 */
const initShowroomCollectionsSlider = () => {
    const slider = document.querySelector('[data-collections-slider]');
    if (!slider) return;

    const track = slider.querySelector('[data-collections-track]');
    const slides = slider.querySelectorAll('[data-collections-slide]');
    const prevBtn = document.querySelector('[data-collections-prev]');
    const nextBtn = document.querySelector('[data-collections-next]');

    if (!track || slides.length === 0) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const SPEED_PX_PER_S = 38; // Same as Portfolio

    const setScrollMode = () => {
        slider.classList.add('is-scroll');
        track.style.transform = 'none';
        track.style.transition = 'none';
    };

    const setTranslateMode = () => {
        slider.classList.remove('is-scroll');
        slider.classList.remove('is-autoplay');
        track.style.transition = '';
    };

    /** @type {{ rafId: number | null, lastTs: number | null, offsetX: number, originalWidth: number, paused: boolean }} */
    const marqueeTransform = { rafId: null, lastTs: null, offsetX: 0, originalWidth: 0, paused: false };
    /** @type {{ rafId: number | null, lastTs: number | null, originalWidth: number, pausedUntil: number }} */
    const marqueeScroll = { rafId: null, lastTs: null, originalWidth: 0, pausedUntil: 0 };
    const MOBILE_USER_PAUSE_MS = 2200;

    const stopMarquee = () => {
        if (marqueeTransform.rafId !== null) cancelAnimationFrame(marqueeTransform.rafId);
        marqueeTransform.rafId = null;
        marqueeTransform.lastTs = null;
        if (marqueeScroll.rafId !== null) cancelAnimationFrame(marqueeScroll.rafId);
        marqueeScroll.rafId = null;
        marqueeScroll.lastTs = null;
        slider.classList.remove('is-autoplay');
    };

    const ensureClonesForTransform = () => {
        const width = getViewportWidth();
        if (isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-collections-clone="1"]')).forEach((n) => n.remove());

        const originalChildren = Array.from(track.children);
        if (originalChildren.length === 0) return false;

        marqueeTransform.originalWidth = track.scrollWidth;
        if (marqueeTransform.originalWidth <= 0) return false;

        while (track.scrollWidth < marqueeTransform.originalWidth * 2) {
            originalChildren.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.collectionsClone = '1';
                track.appendChild(clone);
            });
        }

        track.style.transition = 'none';
        track.style.willChange = 'transform';
        slider.classList.remove('is-scroll');
        return true;
    };

    const tickTransform = (ts) => {
        if (marqueeTransform.paused) {
            marqueeTransform.lastTs = ts;
            marqueeTransform.rafId = requestAnimationFrame(tickTransform);
            return;
        }

        if (marqueeTransform.lastTs === null) marqueeTransform.lastTs = ts;
        const dt = Math.min(48, ts - marqueeTransform.lastTs);
        marqueeTransform.lastTs = ts;

        marqueeTransform.offsetX += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeTransform.originalWidth > 0 && marqueeTransform.offsetX >= marqueeTransform.originalWidth) {
            marqueeTransform.offsetX -= marqueeTransform.originalWidth;
        }
        track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const startMarqueeDesktop = () => {
        stopMarquee();
        marqueeTransform.offsetX = 0;
        marqueeTransform.paused = false;
        if (!ensureClonesForTransform()) return;
        marqueeTransform.rafId = requestAnimationFrame(tickTransform);
    };

    const ensureClonesForScroll = () => {
        const width = getViewportWidth();
        if (!isMobile(width)) return false;
        if (prefersReducedMotion) return false;
        if (!document.body.contains(slider)) return false;

        Array.from(track.querySelectorAll('[data-collections-clone="1"]')).forEach((n) => n.remove());
        marqueeScroll.originalWidth = track.scrollWidth;
        if (marqueeScroll.originalWidth <= 0) return false;

        const originals = Array.from(track.children);
        if (originals.length === 0) return false;

        while (track.scrollWidth < marqueeScroll.originalWidth * 2) {
            originals.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.collectionsClone = '1';
                track.appendChild(clone);
            });
        }
        return true;
    };

    const tickScroll = (ts) => {
        const width = getViewportWidth();
        if (!isMobile(width)) {
            stopMarquee();
            return;
        }

        if (Date.now() < marqueeScroll.pausedUntil) {
            marqueeScroll.lastTs = ts;
            marqueeScroll.rafId = requestAnimationFrame(tickScroll);
            return;
        }

        slider.classList.add('is-autoplay');
        if (marqueeScroll.lastTs === null) marqueeScroll.lastTs = ts;
        const dt = Math.min(48, ts - marqueeScroll.lastTs);
        marqueeScroll.lastTs = ts;

        slider.scrollLeft += (SPEED_PX_PER_S * dt) / 1000;
        if (marqueeScroll.originalWidth > 0 && slider.scrollLeft >= marqueeScroll.originalWidth) {
            slider.scrollLeft -= marqueeScroll.originalWidth;
        }
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    const startMarqueeMobile = () => {
        stopMarquee();
        setScrollMode();
        if (!ensureClonesForScroll()) return;
        slider.scrollLeft = 0;
        marqueeScroll.pausedUntil = Date.now() + 700;
        marqueeScroll.rafId = requestAnimationFrame(tickScroll);
    };

    // Pause on hover/focus for readability
    slider.addEventListener('mouseenter', () => { marqueeTransform.paused = true; });
    slider.addEventListener('mouseleave', () => { marqueeTransform.paused = false; });
    slider.addEventListener('focusin', () => { marqueeTransform.paused = true; });
    slider.addEventListener('focusout', () => { marqueeTransform.paused = false; });

    // Mobile user interaction pauses autoplay
    const pauseMobileByUser = () => {
        marqueeScroll.pausedUntil = Date.now() + MOBILE_USER_PAUSE_MS;
        slider.classList.remove('is-autoplay');
    };
    slider.addEventListener('pointerdown', pauseMobileByUser, { passive: true });
    slider.addEventListener('touchstart', pauseMobileByUser, { passive: true });
    slider.addEventListener('wheel', pauseMobileByUser, { passive: true });

    // Manual navigation (desktop)
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            marqueeTransform.offsetX = Math.max(0, marqueeTransform.offsetX - 300);
            track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            marqueeTransform.offsetX += 300;
            if (marqueeTransform.originalWidth > 0 && marqueeTransform.offsetX >= marqueeTransform.originalWidth) {
                marqueeTransform.offsetX -= marqueeTransform.originalWidth;
            }
            track.style.transform = `translateX(-${marqueeTransform.offsetX}px)`;
        });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopMarquee();
            return;
        }
        const width = getViewportWidth();
        if (isMobile(width)) {
            startMarqueeMobile();
            return;
        }
        startMarqueeDesktop();
    });

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (isMobile(getViewportWidth())) {
                setScrollMode();
                slider.scrollLeft = 0;
                stopMarquee();
                startMarqueeMobile();
                return;
            }
            startMarqueeDesktop();
        }, 100);
    });

    // Initialize
    if (isMobile(getViewportWidth())) {
        startMarqueeMobile();
    } else {
        startMarqueeDesktop();
    }
};

/**
 * Showroom Events Slider (slow marquee if > 4 items)
 */
const initShowroomEventsSlider = () => {
    const slider = document.querySelector('[data-events-slider]');
    if (!slider) return;

    const track = slider.querySelector('[data-events-track]');
    const slides = slider.querySelectorAll('[data-events-slide]');
    const prevBtn = document.querySelector('[data-events-prev]');
    const nextBtn = document.querySelector('[data-events-next]');

    if (!track || slides.length === 0) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const SPEED_PX_PER_S = 30;

    /** @type {{ rafId: number | null, lastTs: number | null, offsetX: number, originalWidth: number, paused: boolean }} */
    const marquee = { rafId: null, lastTs: null, offsetX: 0, originalWidth: 0, paused: false };

    const stopMarquee = () => {
        if (marquee.rafId !== null) cancelAnimationFrame(marquee.rafId);
        marquee.rafId = null;
        marquee.lastTs = null;
    };

    const ensureClones = () => {
        if (prefersReducedMotion) return false;

        Array.from(track.querySelectorAll('[data-events-clone="1"]')).forEach((n) => n.remove());

        const originalChildren = Array.from(track.children);
        if (originalChildren.length === 0) return false;

        marquee.originalWidth = track.scrollWidth;
        if (marquee.originalWidth <= 0) return false;

        while (track.scrollWidth < marquee.originalWidth * 2) {
            originalChildren.forEach((child) => {
                const clone = child.cloneNode(true);
                if (clone instanceof HTMLElement) clone.dataset.eventsClone = '1';
                track.appendChild(clone);
            });
        }

        track.style.transition = 'none';
        track.style.willChange = 'transform';
        return true;
    };

    const tick = (ts) => {
        if (marquee.paused) {
            marquee.lastTs = ts;
            marquee.rafId = requestAnimationFrame(tick);
            return;
        }

        if (marquee.lastTs === null) marquee.lastTs = ts;
        const dt = Math.min(48, ts - marquee.lastTs);
        marquee.lastTs = ts;

        marquee.offsetX += (SPEED_PX_PER_S * dt) / 1000;
        if (marquee.originalWidth > 0 && marquee.offsetX >= marquee.originalWidth) {
            marquee.offsetX -= marquee.originalWidth;
        }
        track.style.transform = `translateX(-${marquee.offsetX}px)`;
        marquee.rafId = requestAnimationFrame(tick);
    };

    const startMarquee = () => {
        stopMarquee();
        marquee.offsetX = 0;
        marquee.paused = false;
        if (!ensureClones()) return;
        marquee.rafId = requestAnimationFrame(tick);
    };

    slider.addEventListener('mouseenter', () => { marquee.paused = true; });
    slider.addEventListener('mouseleave', () => { marquee.paused = false; });
    slider.addEventListener('focusin', () => { marquee.paused = true; });
    slider.addEventListener('focusout', () => { marquee.paused = false; });

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            marquee.offsetX = Math.max(0, marquee.offsetX - 300);
            track.style.transform = `translateX(-${marquee.offsetX}px)`;
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            marquee.offsetX += 300;
            if (marquee.originalWidth > 0 && marquee.offsetX >= marquee.originalWidth) {
                marquee.offsetX -= marquee.originalWidth;
            }
            track.style.transform = `translateX(-${marquee.offsetX}px)`;
        });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopMarquee();
            return;
        }
        startMarquee();
    });

    startMarquee();
};

/**
 * Showroom Map (Yandex Maps) - серая карта с прямоугольным маркером
 */
const initShowroomMap = () => {
    const mapContainer = document.getElementById('showroom-map');
    if (!mapContainer) return;

    const lat = parseFloat(mapContainer.dataset.lat || '45.0355');
    const lng = parseFloat(mapContainer.dataset.lng || '38.9753');
    const zoom = parseInt(mapContainer.dataset.zoom || '15', 10);
    const address = mapContainer.dataset.address || '';
    const phone = mapContainer.dataset.phone || '';
    const hours = mapContainer.dataset.hours || '';

    // Формируем содержимое balloon
    const balloonContent = `
        <div style="font-size:13px;line-height:1.4;">
            <b>Si Mosaic Showroom</b><br>
            ${address ? `${address}<br>` : ''}
            ${hours ? `${hours}<br>` : ''}
            ${phone ? `<a href="tel:${phone.replace(/[^+\d]/g, '')}" style="color:#A36217;">${phone}</a>` : ''}
        </div>
    `.trim();

    // Load Yandex Maps API
    const script = document.createElement('script');
    script.src = 'https://api-maps.yandex.ru/2.1/?apikey=&lang=ru_RU';
    script.async = true;
    script.onload = () => {
        // eslint-disable-next-line no-undef
        ymaps.ready(() => {
            // eslint-disable-next-line no-undef
            const map = new ymaps.Map(mapContainer, {
                center: [lat, lng],
                zoom: zoom,
                controls: []
            });

            // Добавляем кнопки зума (+ и -)
            // eslint-disable-next-line no-undef
            map.controls.add('zoomControl', {
                size: 'small',
                position: {
                    right: 10,
                    top: 10
                }
            });

            // Кастомный маркер-пин с градиентом
            const svgIcon = '<svg width="48" height="62" viewBox="0 0 48 62" fill="none" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="pinGrad" x1="24" y1="0" x2="24" y2="62" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#E8B94A"/><stop offset="100%" stop-color="#C76A1A"/></linearGradient></defs><path d="M24 0C10.745 0 0 10.745 0 24c0 18 24 38 24 38s24-20 24-38C48 10.745 37.255 0 24 0z" fill="url(#pinGrad)"/><circle cx="24" cy="24" r="11" fill="white"/></svg>';
            // eslint-disable-next-line no-undef
            const placemark = new ymaps.Placemark([lat, lng], {
                hintContent: 'Si Mosaic Showroom',
                balloonContentBody: balloonContent
            }, {
                iconLayout: 'default#image',
                iconImageHref: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
                iconImageSize: [48, 62],
                iconImageOffset: [-24, -62]
            });

            map.geoObjects.add(placemark);
            map.behaviors.disable('scrollZoom');
        });
    };
    document.head.appendChild(script);
};

/**
 * Showroom Lightbox - fullscreen image viewer
 */
const initShowroomLightbox = () => {
    const modal = document.querySelector('[data-lightbox-modal]');
    if (!modal) return;

    const image = modal.querySelector('[data-lightbox-image]');
    const title = modal.querySelector('[data-lightbox-title]');
    const closeBtn = modal.querySelector('[data-lightbox-close]');
    const triggers = document.querySelectorAll('[data-lightbox-open]');

    if (!image || !title || triggers.length === 0) return;

    const open = (src, titleText) => {
        image.src = src;
        image.alt = titleText;
        title.textContent = titleText;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        image.src = '';
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const src = trigger.dataset.lightboxSrc || '';
            const titleText = trigger.dataset.lightboxTitle || '';
            if (src) {
                open(src, titleText);
            }
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', close);
    }

    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            close();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });
};