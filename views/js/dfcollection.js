/* dfcollection.js — preload 2 images (left), AJAX only for right+slider, Slick kept intact + PREFETCH CACHE (no skeleton) */
(function () {
    function qs(s, ctx) { return (ctx || document).querySelector(s); }
    function qsa(s, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(s)); }

    function isDesktopDFC() {
        return window.matchMedia('(min-width: 992px)').matches;
    }

    function safeRefreshSlider($slider) {
        try { $slider.slick('setPosition'); } catch (e) {}
        setTimeout(function () {
            try { $slider.slick('setPosition'); } catch (e) {}
        }, 50);
    }

    function buildManualDots($slider, $dots, ns) {
        if (!$slider.length || !$dots.length) {
            return;
        }

        function rebuildDots() {
            var slick;
            try {
                slick = $slider.slick('getSlick');
            } catch (e) {
                return;
            }

            if (!slick) {
                return;
            }

            var slideCount = slick.slideCount || 0;

            if (slideCount <= 1) {
                $dots.empty();
                return;
            }

            var html = '<ul class="df-dots-list">';
            for (var i = 0; i < slideCount; i++) {
                html += '<li data-slide-index="' + i + '"' + (i === 0 ? ' class="is-active"' : '') + '></li>';
            }
            html += '</ul>';

            $dots.html(html);
        }

        function setActiveDot(index) {
            $dots.find('li').removeClass('is-active').eq(index).addClass('is-active');
        }

        function updateActiveDot() {
            var slick;
            try {
                slick = $slider.slick('getSlick');
            } catch (e) {
                return;
            }

            if (!slick) {
                return;
            }

            var slideCount = slick.slideCount || 0;
            if (!slideCount) {
                return;
            }

            if (slick.options.infinite) {
                var real = (slick.currentSlide || 0) % slideCount;
                setActiveDot(real);
                return;
            }

            var currentSlide = slick.currentSlide || 0;
            var slidesToShow = slick.options.slidesToShow || 1;
            var lastVisible = currentSlide + slidesToShow - 1;

            var clickedIndex = $slider.data('dfCollectionClickedDot');

            if (
                typeof clickedIndex === 'number' &&
                clickedIndex >= currentSlide &&
                clickedIndex <= lastVisible &&
                clickedIndex < slideCount
            ) {
                setActiveDot(clickedIndex);
                return;
            }

            setActiveDot(currentSlide);
        }

        rebuildDots();

        $dots.off('.' + ns).on('click.' + ns, 'li', function () {
            var wantedIndex = parseInt(jQuery(this).attr('data-slide-index'), 10) || 0;

            var slick;
            try {
                slick = $slider.slick('getSlick');
            } catch (e) {
                return;
            }

            if (!slick) {
                return;
            }

            if (slick.options.infinite) {
                try {
                    $slider.slick('slickGoTo', wantedIndex);
                } catch (e) {}
                setActiveDot(wantedIndex);
                return;
            }

            var slideCount = slick.slideCount || 0;
            var slidesToShow = slick.options.slidesToShow || 1;
            var maxIndex = Math.max(slideCount - slidesToShow, 0);

            $slider.data('dfCollectionClickedDot', wantedIndex);

            var goTo = Math.min(wantedIndex, maxIndex);

            try {
                $slider.slick('slickGoTo', goTo);
            } catch (e) {}

            setActiveDot(wantedIndex);
        });

        $slider
            .off('afterChange.' + ns)
            .on('afterChange.' + ns, function () {
                updateActiveDot();
            });

        $slider
            .off('reInit.' + ns + ' breakpoint.' + ns + ' setPosition.' + ns)
            .on('reInit.' + ns + ' breakpoint.' + ns + ' setPosition.' + ns, function () {
                rebuildDots();
                updateActiveDot();
            });
    }

    function loadDfImagesInSlide($slide) {
        if (!$slide || !$slide.length) return;

        $slide.find('img[data-df-src]').each(function () {
            var $img = jQuery(this);
            var curr = $img.attr('src') || '';

            if (curr && curr.indexOf('data:image') !== 0) {
                $img.removeAttr('data-df-src');
                return;
            }

            var realSrc = $img.attr('data-df-src');
            if (!realSrc) return;

            $img.attr('src', realSrc);
            $img.removeAttr('data-df-src');
        });
    }

    function loadActivePlusNeighbors($slider) {
        if (!$slider || !$slider.length) return;

        var $active = $slider.find('.slick-slide.slick-active');
        if (!$active.length) return;

        $active.each(function () {
            loadDfImagesInSlide(jQuery(this));
        });

        var slick;
        try {
            slick = $slider.slick('getSlick');
        } catch (e) {
            slick = null;
        }

        var isInfinite = !!(slick && slick.options && slick.options.infinite);

        var $first = $active.first();
        var $prev = $first.prevAll('.slick-slide').not('.slick-cloned').first();
        if (!$prev.length && isInfinite) {
            $prev = $slider.find('.slick-slide').not('.slick-cloned').last();
        }
        if ($prev.length) {
            loadDfImagesInSlide($prev);
        }

        var $last = $active.last();
        var $next = $last.nextAll('.slick-slide').not('.slick-cloned').first();
        if (!$next.length && isInfinite) {
            $next = $slider.find('.slick-slide').not('.slick-cloned').first();
        }
        if ($next.length) {
            loadDfImagesInSlide($next);
        }
    }

    function attachDfSliderLazyHandlers($slider, ns) {
        if (!$slider || !$slider.length) return;

        var bindKey = 'dfLazyBound_' + ns;
        if ($slider.data(bindKey)) return;
        $slider.data(bindKey, true);

        $slider.off('init.' + ns);
        $slider.off('beforeChange.' + ns);
        $slider.off('afterChange.' + ns);
        $slider.off('setPosition.' + ns);

        $slider.data('dfLazyReady', false);

        $slider.on('init.' + ns, function () {
            setTimeout(function () {
                $slider.data('dfLazyReady', true);
                loadActivePlusNeighbors($slider);
            }, 0);
        });

        $slider.on('beforeChange.' + ns, function () {
            setTimeout(function () {
                loadActivePlusNeighbors($slider);
            }, 0);
        });

        $slider.on('afterChange.' + ns, function () {
            loadActivePlusNeighbors($slider);
        });

        $slider.on('setPosition.' + ns, function () {
            if (!$slider.data('dfLazyReady')) return;
            loadActivePlusNeighbors($slider);
        });
    }
    // --- prościutki cache odpowiedzi i współdzielone fetch'e ---
    var DFC_CACHE = new Map();  // catId -> {title, htmlMain, htmlSlider, productsCount}
    var DFC_FETCHING = new Map();  // catId -> Promise
    var DFC_SWITCH_TOKEN = 0;
    var DFC_FEAT_TOKEN = 0; // ← anty-dubel / anty-wyścig dla featured
    var DFC_MAX_FEATURED_HEIGHT = 0;
    var DFC_MAX_MAIN_HEIGHT = 0;
    var DFC_IMAGE_PRELOAD = new Map(); // src -> true / Promise
    var DFC_STORAGE_KEY = 'dfc_last_category';

    function saveLastCollection(catId) {
        catId = parseInt(catId, 10) || 0;
        if (!catId) return;

        try {
            localStorage.setItem(DFC_STORAGE_KEY, String(catId));
        } catch (e) {}
    }

    function getLastCollection() {
        try {
            return parseInt(localStorage.getItem(DFC_STORAGE_KEY), 10) || 0;
        } catch (e) {
            return 0;
        }
    }

    function preloadImage(src) {
        src = (src || '').trim();
        if (!src) return Promise.resolve(false);

        if (DFC_IMAGE_PRELOAD.has(src)) {
            var cached = DFC_IMAGE_PRELOAD.get(src);
            if (cached === true) {
                return Promise.resolve(true);
            }
            return cached;
        }

        var p = new Promise(function (resolve) {
            var img = new Image();

            img.onload = function () {
                DFC_IMAGE_PRELOAD.set(src, true);
                resolve(true);
            };

            img.onerror = function () {
                DFC_IMAGE_PRELOAD.delete(src);
                resolve(false);
            };

            img.src = src;
        });

        DFC_IMAGE_PRELOAD.set(src, p);
        return p;
    }

    function preloadCompareImages(meta) {
        if (!meta) return Promise.resolve();

        var jobs = [];

        if (meta.compareImg) {
            jobs.push(preloadImage(meta.compareImg));
        }

        // opcjonalnie preload także głównych obrazów tej kolekcji
        if (meta.img) {
            jobs.push(preloadImage(meta.img));
        }
        if (meta.imgMobile) {
            jobs.push(preloadImage(meta.imgMobile));
        }
        if (meta.imgXS) {
            jobs.push(preloadImage(meta.imgXS));
        }

        if (!jobs.length) {
            return Promise.resolve();
        }

        return Promise.all(jobs).then(function () {
            return true;
        });
    }

    // --- poinformuj inne moduły (np. wishlist) że DOM z produktami się zmienił
    function notifyUpdatedProducts() {
        try {
            if (window.prestashop && typeof prestashop.emit === 'function') {
                prestashop.emit('updatedProductList', {});
            } else {
                // miękki fallback dla starszych motywów
                document.dispatchEvent(new Event('updatedProductList'));
            }
        } catch (e) { }
    }

    function refreshMiniAtc(root) {
        if (!root) return;

        qsa('.df-mini-atc__btn[disabled]', root).forEach(function (btn) {
            var form = btn.closest('form');
            if (!form) return;

            var productInput = form.querySelector('input[name="id_product"]');
            if (!productInput || !productInput.value) return;

            btn.disabled = false;
            btn.removeAttribute('disabled');
            btn.setAttribute('aria-disabled', 'false');
        });

        setTimeout(function () {
            qsa('.df-mini-atc__btn[disabled]', root).forEach(function (btn) {
                var form = btn.closest('form');
                if (!form) return;

                var productInput = form.querySelector('input[name="id_product"]');
                if (!productInput || !productInput.value) return;

                btn.disabled = false;
                btn.removeAttribute('disabled');
                btn.setAttribute('aria-disabled', 'false');
            });
        }, 120);
    }

    function getFeaturedBox(root) {
        return qs('.dfc-featured', root);
    }

    function resetDFCHeights(root) {
        var grid = qs('.dfc-main-grid', root);
        var leftWrap = qs('.dfc-left-wrap', root);
        var right = qs('.dfc-right', root);
        var featured = qs('.dfc-featured', root);

        if (grid) grid.style.minHeight = '';
        if (leftWrap) leftWrap.style.minHeight = '';
        if (right) right.style.minHeight = '';
        if (featured) featured.style.minHeight = '';
    }

    function applyFeaturedMinHeight(root, height) {
        var box = getFeaturedBox(root);
        if (!box || !height) return;

        box.style.minHeight = Math.ceil(height) + 'px';
    }

    function updateFeaturedMinHeight(root, height) {
        if (!isDesktopDFC()) return;
      
        height = Math.ceil(parseFloat(height) || 0);
        if (!height) return;

        if (height > DFC_MAX_FEATURED_HEIGHT) {
            DFC_MAX_FEATURED_HEIGHT = height;
            applyFeaturedMinHeight(root, DFC_MAX_FEATURED_HEIGHT);
        }
    }

    function applyMainColumnsHeight(root, height) {
        height = Math.ceil(parseFloat(height) || 0);
        if (!height) return;

        var grid = qs('.dfc-main-grid', root);
        var leftWrap = qs('.dfc-left-wrap', root);
        var right = qs('.dfc-right', root);

        if (grid) {
            grid.style.minHeight = height + 'px';
        }

        if (leftWrap) {
            leftWrap.style.minHeight = height + 'px';
        }

        if (right) {
            right.style.minHeight = height + 'px';
        }
    }

    function measureMainColumnsHeight(root) {
        var leftWrap = qs('.dfc-left-wrap', root);
        var right = qs('.dfc-right', root);

        var leftH = leftWrap ? Math.ceil(leftWrap.offsetHeight || leftWrap.scrollHeight || 0) : 0;
        var rightH = right ? Math.ceil(right.offsetHeight || right.scrollHeight || 0) : 0;

        return Math.max(leftH, rightH);
    }

    function updateMainColumnsHeight(root, height) {
        if (!isDesktopDFC()) return;
      
        height = Math.ceil(parseFloat(height) || 0);
        if (!height) return;

        if (height > DFC_MAX_MAIN_HEIGHT) {
            DFC_MAX_MAIN_HEIGHT = height;
        }

        applyMainColumnsHeight(root, DFC_MAX_MAIN_HEIGHT);
    }

    function syncMainColumnsHeight(root) {
        if (!isDesktopDFC()) return;
      
        updateMainColumnsHeight(root, measureMainColumnsHeight(root));
    }

    function measureCurrentFeaturedHeight(root) {
        var current = qs('.dfc-featured .dfc-featured-item.current', root);
        if (!current) return 0;

        return Math.ceil(current.offsetHeight || current.scrollHeight || 0);
    }

    function measureFeaturedHeightFromHtml(html, root) {
        if (!html) return 0;

        var rightCol = qs('.dfc-right', root);
        var width = rightCol ? Math.ceil(rightCol.getBoundingClientRect().width) : 331;

        var tmp = document.createElement('div');
        tmp.style.position = 'absolute';
        tmp.style.left = '-99999px';
        tmp.style.top = '0';
        tmp.style.visibility = 'hidden';
        tmp.style.pointerEvents = 'none';
        tmp.style.width = width + 'px';
        tmp.style.zIndex = '-1';

        var wrap = document.createElement('div');
        wrap.className = 'dfc-featured-measure';
        wrap.innerHTML = html;

        tmp.appendChild(wrap);
        document.body.appendChild(tmp);

        var h = Math.ceil(wrap.offsetHeight || wrap.scrollHeight || 0);

        document.body.removeChild(tmp);
        return h;
    }

    function warmupFeaturedHeights(root, items, ajaxUrl) {
        if (!isDesktopDFC()) return;
      
        updateFeaturedMinHeight(root, measureCurrentFeaturedHeight(root));

        items.forEach(function (item) {
            if (!item || !item.cat) return;

            getData(item.cat, ajaxUrl)
                .then(function (resp) {
                    if (!resp || !resp.htmlMain) return;

                    var tmp = document.createElement('div');
                    tmp.innerHTML = resp.htmlMain;

                    var featured =
                        tmp.querySelector('.dfc-right .product-miniature') ||
                        tmp.querySelector('.dfc-right .dfc-placeholder') ||
                        tmp.querySelector('.dfc-right');

                    if (!featured) return;

                    var h = measureFeaturedHeightFromHtml(featured.outerHTML, root);
                    updateFeaturedMinHeight(root, h);
                })
                .catch(function () {});
        });
    }

    function getData(catId, ajaxUrl) {
        if (DFC_CACHE.has(catId)) {
            return Promise.resolve(DFC_CACHE.get(catId));
        }
        if (DFC_FETCHING.has(catId)) {
            return DFC_FETCHING.get(catId);
        }
        var p = fetch(ajaxUrl + '?id_category=' + encodeURIComponent(catId) + '&_ts=' + Date.now(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                if (!resp || resp.error) throw new Error(resp.error || 'Brak danych');
                DFC_CACHE.set(catId, resp);
                return resp;
            })
            .finally(function () { DFC_FETCHING.delete(catId); });

        DFC_FETCHING.set(catId, p);
        return p;
    }

    function prefetch(catId, ajaxUrl) {
        if (!catId) return;

        var meta = null;
        var root = qs('#dfcollection');
        if (root) {
            var items = readCollectionsMeta(root);
            meta = items.find(function (x) {
                return x.cat === catId;
            }) || null;
        }

        if (meta) {
            preloadCompareImages(meta);
        }

        if (DFC_CACHE.has(catId) || DFC_FETCHING.has(catId)) return;
        getData(catId, ajaxUrl).catch(function () { /* cicho */ });
    }

    function getSliderInfinite(root) {
        var holder = qs('#dfc-slider', root) || qs('.dfc-products', root) || root;
        if (!holder) return true;

        var raw = holder.getAttribute('data-slider-infinite');

        if (raw === null || raw === undefined || raw === '') {
            return true;
        }

        raw = String(raw).toLowerCase().trim();
        return raw === '1' || raw === 'true' || raw === 'yes';
    }

    function updateArrowVisibility($slider) {
        if (!$slider || !$slider.length || !$slider.hasClass('slick-initialized')) {
            return;
        }

        var slick;
        try {
            slick = $slider.slick('getSlick');
        } catch (e) {
            return;
        }

        if (!slick) {
            return;
        }

        var isInfinite = !!slick.options.infinite;
        var $prev = jQuery(slick.$prevArrow);
        var $next = jQuery(slick.$nextArrow);

        if (!$prev.length || !$next.length) {
            return;
        }

        if (isInfinite) {
            $prev.removeClass('is-hidden');
            $next.removeClass('is-hidden');
            return;
        }

        $prev.toggleClass('is-hidden', $prev.hasClass('slick-disabled'));
        $next.toggleClass('is-hidden', $next.hasClass('slick-disabled'));
    }
  
    function initSlider(root) {
        if (typeof jQuery === 'undefined' || !jQuery.fn || !jQuery.fn.slick) return;

        var $ = jQuery;
        var $root = $(root);
        var $wrap = $root.find('.dfc-products');
        if (!$wrap.length) return;

        var $section = $wrap.closest('#dfcollection');
        var $dots = $section.find('.df-slider-dots--dfcollection').first();
        var isInfinite = getSliderInfinite(root);

        attachDfSliderLazyHandlers($wrap, 'dfCollectionLazy');

        if ($wrap.hasClass('slick-initialized')) {
            try { $wrap.slick('unslick'); } catch (e) {}
        }

        $wrap.off('init.dfCollectionArrows afterChange.dfCollectionArrows setPosition.dfCollectionArrows reInit.dfCollectionArrows breakpoint.dfCollectionArrows');
        $wrap.on('init.dfCollectionArrows afterChange.dfCollectionArrows setPosition.dfCollectionArrows reInit.dfCollectionArrows breakpoint.dfCollectionArrows', function () {
            updateArrowVisibility($wrap);
        });

        $wrap.slick({
            infinite: isInfinite,
            slidesToShow: 4,
            slidesToScroll: 1,
            dots: false,
            prevArrow:
                '<a class="slider-prev slick-prev" role="button" aria-label="Previous">' +
                '  <svg width="40" height="40" viewBox="0 0 24 24">' +
                '    <path d="M18 12H6M6 12L11 17M6 12L11 7" stroke="#232323" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '  </svg>' +
                '</a>',
            nextArrow:
                '<a class="slider-next slick-next" role="button" aria-label="Next">' +
                '  <svg width="40" height="40" viewBox="0 0 24 24">' +
                '    <path d="M6 12H18M18 12L13 7M18 12L13 17" stroke="#232323" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '  </svg>' +
                '</a>',
            responsive: [
                { breakpoint: 2050, settings: { slidesToShow: 3 } },
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 992, settings: { slidesToShow: 2 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 575, settings: { slidesToShow: 1 } }
            ]
        });

        safeRefreshSlider($wrap);
        buildManualDots($wrap, $dots, 'dfDotsCollection');
        updateArrowVisibility($wrap);
    }

    function enableTabsDrag(root) {
        var bar = qs('.dfc-tabs', root);
        if (!bar) return;

        let isDown = false, startX = 0, startScroll = 0, moved = 0;

        const down = e => {
            isDown = true;
            moved = 0;
            startX = (e.touches ? e.touches[0].pageX : e.pageX);
            startScroll = bar.scrollLeft;
            bar.classList.add('dragging');
        };

        const move = e => {
            if (!isDown) return;
            if (e.touches && e.cancelable) e.preventDefault();
            const x = (e.touches ? e.touches[0].pageX : e.pageX);
            const dx = x - startX;
            moved += Math.abs(dx);
            bar.scrollLeft = startScroll - dx;
        };

        const up = () => { isDown = false; bar.classList.remove('dragging'); };

        // Mouse
        bar.addEventListener('mousedown', down, { passive: true });
        bar.addEventListener('mousemove', move, { passive: false });
        bar.addEventListener('mouseleave', up, { passive: true });
        bar.addEventListener('mouseup', up, { passive: true });

        // Touch
        bar.addEventListener('touchstart', down, { passive: true });
        bar.addEventListener('touchmove', move, { passive: false });
        bar.addEventListener('touchend', up, { passive: true });

        // Kółko myszy przewija w poziomie
        bar.addEventListener('wheel', function (e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                bar.scrollLeft += e.deltaY;
                e.preventDefault();
            }
        }, { passive: false });

        // Blokuj klik po realnym drag-u
        bar.addEventListener('click', function (e) {
            if (moved > 6) {
                e.stopPropagation();
                e.preventDefault();
            }
        }, true);
    }

    function ensureTabsScrollbar(root) {
        if (!root) return null;

        var tabs = qs('.dfc-tabs', root);
        if (!tabs) return null;

        var existing = qs('.dfc-tabs-scrollbar', root);
        if (existing) return existing;

        var bar = document.createElement('div');
        bar.className = 'dfc-tabs-scrollbar';
        bar.innerHTML =
            '<div class="dfc-tabs-scrollbar-track">' +
                '<div class="dfc-tabs-scrollbar-thumb"></div>' +
            '</div>';

        tabs.insertAdjacentElement('afterend', bar);

        return bar;
    }

    function updateTabsScrollbar(root) {
        if (!root) return;

        var tabs = qs('.dfc-tabs', root);
        var scrollbar = qs('.dfc-tabs-scrollbar', root);
        var track = scrollbar ? qs('.dfc-tabs-scrollbar-track', scrollbar) : null;
        var thumb = scrollbar ? qs('.dfc-tabs-scrollbar-thumb', scrollbar) : null;

        if (!tabs || !scrollbar || !track || !thumb) return;

        var scrollWidth = tabs.scrollWidth || 0;
        var clientWidth = tabs.clientWidth || 0;
        var scrollLeft = tabs.scrollLeft || 0;

        if (!scrollWidth || !clientWidth || scrollWidth <= clientWidth + 2) {
            scrollbar.classList.remove('is-visible');
            thumb.style.width = '';
            thumb.style.transform = 'translateX(0)';
            return;
        }

        scrollbar.classList.add('is-visible');

        var trackWidth = track.clientWidth || 0;
        if (!trackWidth) return;

        var ratio = clientWidth / scrollWidth;
        var thumbWidth = Math.max(Math.round(trackWidth * ratio), 48);
        var maxThumbX = Math.max(trackWidth - thumbWidth, 0);
        var maxScroll = Math.max(scrollWidth - clientWidth, 1);
        var thumbX = Math.round((scrollLeft / maxScroll) * maxThumbX);

        thumb.style.width = thumbWidth + 'px';
        thumb.style.transform = 'translateX(' + thumbX + 'px)';
    }

    function bindTabsScrollbar(root) {
        if (!root || root.__dfcTabsScrollbarBound) return;
        root.__dfcTabsScrollbarBound = true;

        var tabs = qs('.dfc-tabs', root);
        var scrollbar = ensureTabsScrollbar(root);
        var track = scrollbar ? qs('.dfc-tabs-scrollbar-track', scrollbar) : null;
        var thumb = scrollbar ? qs('.dfc-tabs-scrollbar-thumb', scrollbar) : null;

        if (!tabs || !scrollbar || !track || !thumb) return;

        var dragging = false;
        var startX = 0;
        var startScrollLeft = 0;

        function pointerDown(clientX) {
            dragging = true;
            startX = clientX;
            startScrollLeft = tabs.scrollLeft;
            scrollbar.classList.add('is-dragging');
        }

        function pointerMove(clientX) {
            if (!dragging) return;

            var scrollWidth = tabs.scrollWidth || 0;
            var clientWidth = tabs.clientWidth || 0;
            var trackWidth = track.clientWidth || 0;
            var thumbWidth = thumb.offsetWidth || 0;

            var maxScroll = Math.max(scrollWidth - clientWidth, 1);
            var maxThumbX = Math.max(trackWidth - thumbWidth, 1);

            var dx = clientX - startX;
            var scrollDx = (dx / maxThumbX) * maxScroll;

            tabs.scrollLeft = startScrollLeft + scrollDx;
            updateTabsScrollbar(root);
        }

        function pointerUp() {
            dragging = false;
            scrollbar.classList.remove('is-dragging');
        }

        tabs.addEventListener('scroll', function () {
            updateTabsScrollbar(root);
        }, { passive: true });

        window.addEventListener('resize', function () {
            updateTabsScrollbar(root);
        });

        track.addEventListener('click', function (e) {
            if (e.target === thumb) return;

            var rect = track.getBoundingClientRect();
            var clickX = e.clientX - rect.left;
            var thumbHalf = (thumb.offsetWidth || 0) / 2;
            var targetX = clickX - thumbHalf;

            var maxThumbX = Math.max(track.clientWidth - thumb.offsetWidth, 1);
            var ratio = Math.max(Math.min(targetX / maxThumbX, 1), 0);

            tabs.scrollLeft = ratio * (tabs.scrollWidth - tabs.clientWidth);
            updateTabsScrollbar(root);
        });

        thumb.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            pointerDown(e.clientX);
        });

        window.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            e.preventDefault();
            pointerMove(e.clientX);
        });

        window.addEventListener('mouseup', function () {
            if (!dragging) return;
            pointerUp();
        });

        thumb.addEventListener('touchstart', function (e) {
            var touch = e.touches && e.touches[0];
            if (!touch) return;
            pointerDown(touch.clientX);
        }, { passive: true });

        window.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            var touch = e.touches && e.touches[0];
            if (!touch) return;
            pointerMove(touch.clientX);
        }, { passive: true });

        window.addEventListener('touchend', function () {
            if (!dragging) return;
            pointerUp();
        });

        updateTabsScrollbar(root);
    }

    function enableStickyTabsDrag(root) {
        var bar = getStickyTabs(root);
        if (!bar || bar.__dfcDragBound) return;

        bar.__dfcDragBound = true;

        var isDown = false;
        var startX = 0;
        var startScroll = 0;
        var moved = 0;

        function down(e) {
            isDown = true;
            moved = 0;
            startX = (e.touches ? e.touches[0].pageX : e.pageX);
            startScroll = bar.scrollLeft;
            bar.classList.add('dragging');
        }

        function move(e) {
            if (!isDown) return;
            if (e.touches && e.cancelable) e.preventDefault();

            var x = (e.touches ? e.touches[0].pageX : e.pageX);
            var dx = x - startX;
            moved += Math.abs(dx);
            bar.scrollLeft = startScroll - dx;

            updateStickyTabsScrollbar(root);
        }

        function up() {
            isDown = false;
            bar.classList.remove('dragging');
        }

        bar.addEventListener('mousedown', down, { passive: true });
        bar.addEventListener('mousemove', move, { passive: false });
        bar.addEventListener('mouseleave', up, { passive: true });
        bar.addEventListener('mouseup', up, { passive: true });

        bar.addEventListener('touchstart', down, { passive: true });
        bar.addEventListener('touchmove', move, { passive: false });
        bar.addEventListener('touchend', up, { passive: true });

        bar.addEventListener('wheel', function (e) {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                bar.scrollLeft += e.deltaY;
                updateStickyTabsScrollbar(root);
                e.preventDefault();
            }
        }, { passive: false });

        bar.addEventListener('click', function (e) {
            if (moved > 6) {
                e.stopPropagation();
                e.preventDefault();
            }
        }, true);
    }

    function ensureStickyTabsScrollbar(root) {
        if (!root) return null;

        var tabsWrap = qs('.dfc-sticky-bar__left', root) || qs('.dfc-sticky-bar__tabs-wrap', root);
        var tabs = getStickyTabs(root);

        if (!tabsWrap || !tabs) return null;

        var existing = qs('.dfc-sticky-tabs-scrollbar', root);
        if (existing) return existing;

        var bar = document.createElement('div');
        bar.className = 'dfc-sticky-tabs-scrollbar';
        bar.innerHTML =
            '<div class="dfc-sticky-tabs-scrollbar-track">' +
                '<div class="dfc-sticky-tabs-scrollbar-thumb"></div>' +
            '</div>';

        tabsWrap.insertAdjacentElement('beforeend', bar);

        return bar;
    }

    function updateStickyTabsScrollbar(root) {
        if (!root) return;

        var tabs = getStickyTabs(root);
        var scrollbar = qs('.dfc-sticky-tabs-scrollbar', root);
        var track = scrollbar ? qs('.dfc-sticky-tabs-scrollbar-track', scrollbar) : null;
        var thumb = scrollbar ? qs('.dfc-sticky-tabs-scrollbar-thumb', scrollbar) : null;

        if (!tabs || !scrollbar || !track || !thumb) return;

        var scrollWidth = tabs.scrollWidth || 0;
        var clientWidth = tabs.clientWidth || 0;
        var scrollLeft = tabs.scrollLeft || 0;

        if (!scrollWidth || !clientWidth || scrollWidth <= clientWidth + 2) {
            scrollbar.classList.remove('is-visible');
            thumb.style.width = '';
            thumb.style.transform = 'translateX(0)';
            return;
        }

        scrollbar.classList.add('is-visible');

        var trackWidth = track.clientWidth || 0;
        if (!trackWidth) return;

        var ratio = clientWidth / scrollWidth;
        var thumbWidth = Math.max(Math.round(trackWidth * ratio), 48);
        var maxThumbX = Math.max(trackWidth - thumbWidth, 0);
        var maxScroll = Math.max(scrollWidth - clientWidth, 1);
        var thumbX = Math.round((scrollLeft / maxScroll) * maxThumbX);

        thumb.style.width = thumbWidth + 'px';
        thumb.style.transform = 'translateX(' + thumbX + 'px)';
    }

    function bindStickyTabsScrollbar(root) {
        if (!root || root.__dfcStickyTabsScrollbarBound) return;
        root.__dfcStickyTabsScrollbarBound = true;

        var tabs = getStickyTabs(root);
        var scrollbar = ensureStickyTabsScrollbar(root);
        var track = scrollbar ? qs('.dfc-sticky-tabs-scrollbar-track', scrollbar) : null;
        var thumb = scrollbar ? qs('.dfc-sticky-tabs-scrollbar-thumb', scrollbar) : null;

        if (!tabs || !scrollbar || !track || !thumb) return;

        var dragging = false;
        var startX = 0;
        var startScrollLeft = 0;

        function pointerDown(clientX) {
            dragging = true;
            startX = clientX;
            startScrollLeft = tabs.scrollLeft;
            scrollbar.classList.add('is-dragging');
        }

        function pointerMove(clientX) {
            if (!dragging) return;

            var scrollWidth = tabs.scrollWidth || 0;
            var clientWidth = tabs.clientWidth || 0;
            var trackWidth = track.clientWidth || 0;
            var thumbWidth = thumb.offsetWidth || 0;

            var maxScroll = Math.max(scrollWidth - clientWidth, 1);
            var maxThumbX = Math.max(trackWidth - thumbWidth, 1);

            var dx = clientX - startX;
            var scrollDx = (dx / maxThumbX) * maxScroll;

            tabs.scrollLeft = startScrollLeft + scrollDx;
            updateStickyTabsScrollbar(root);
        }

        function pointerUp() {
            dragging = false;
            scrollbar.classList.remove('is-dragging');
        }

        tabs.addEventListener('scroll', function () {
            updateStickyTabsScrollbar(root);
        }, { passive: true });

        window.addEventListener('resize', function () {
            updateStickyTabsScrollbar(root);
        });

        track.addEventListener('click', function (e) {
            if (e.target === thumb) return;

            var rect = track.getBoundingClientRect();
            var clickX = e.clientX - rect.left;
            var thumbHalf = (thumb.offsetWidth || 0) / 2;
            var targetX = clickX - thumbHalf;

            var maxThumbX = Math.max(track.clientWidth - thumb.offsetWidth, 1);
            var ratio = Math.max(Math.min(targetX / maxThumbX, 1), 0);

            tabs.scrollLeft = ratio * (tabs.scrollWidth - tabs.clientWidth);
            updateStickyTabsScrollbar(root);
        });

        thumb.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            pointerDown(e.clientX);
        });

        window.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            e.preventDefault();
            pointerMove(e.clientX);
        });

        window.addEventListener('mouseup', function () {
            if (!dragging) return;
            pointerUp();
        });

        thumb.addEventListener('touchstart', function (e) {
            var touch = e.touches && e.touches[0];
            if (!touch) return;
            pointerDown(touch.clientX);
        }, { passive: true });

        window.addEventListener('touchmove', function (e) {
            if (!dragging) return;
            var touch = e.touches && e.touches[0];
            if (!touch) return;
            pointerMove(touch.clientX);
        }, { passive: true });

        window.addEventListener('touchend', function () {
            if (!dragging) return;
            pointerUp();
        });

        updateStickyTabsScrollbar(root);
    }
  
    // --- warstwy dla lewego obrazu ---
    function ensureLeftLayers(root) {
        var box = qs('.dfc-left', root);
        if (!box) return;

        var linkEl = qs('.dfc-left-link', root) || box;

        var cur = qs('.dfc-left .dfc-img.current', root);
        var next = qs('.dfc-left .dfc-img.next', root);

        if (!cur) {
            var orig = qs('.dfc-left img', root);
            if (!orig) return;
            orig.classList.add('dfc-img', 'current');
            cur = orig;

            next = document.createElement('img');
            next.className = 'dfc-img next';
            next.src = cur.currentSrc || cur.src || '';
            next.alt = '';
            next.loading = 'lazy';
            next.style.transform = 'translateX(100%)';
            linkEl.appendChild(next);
        }

        if (!next) {
            next = document.createElement('img');
            next.className = 'dfc-img next';
            next.src = cur.currentSrc || cur.src || '';
            next.alt = '';
            next.loading = 'lazy';
            next.style.transform = 'translateX(100%)';
            linkEl.appendChild(next);
        }
    }

    function animateLeft(root, dir, nextImgUrl, nextTitle) {
        var box = qs('.dfc-left', root);
        if (!box) return;
        var cur = qs('.dfc-left .dfc-img.current', root);
        var next = qs('.dfc-left .dfc-img.next', root);
        var titleEl = qs('.dfc-title', root);

        if (!cur || !next) return;

        if (nextImgUrl) next.src = nextImgUrl;
        next.style.transform = (dir === 'prev') ? 'translateX(-100%)' : 'translateX(100%)';

        if (titleEl && typeof nextTitle === 'string' && nextTitle.trim()) {
            titleEl.textContent = nextTitle;
        }
        if (typeof nextTitle === 'string' && nextTitle.trim()) {
            next.alt = nextTitle;
        }

        requestAnimationFrame(function () {
            box.classList.remove('to-next', 'to-prev');
            box.classList.add(dir === 'prev' ? 'to-prev' : 'to-next');
            next.style.transform = 'translateX(0)';
        });

        var handler = function (ev) {
            if (!ev || ev.target !== cur) return;
            box.removeEventListener('transitionend', handler);

            cur.classList.remove('current');
            cur.classList.add('next');
            cur.style.transform = '';

            next.classList.remove('next');
            next.classList.add('current');
            next.style.transform = '';

            box.classList.remove('to-next', 'to-prev');

            var nowCurImg = qs('.dfc-left .dfc-img.current', root);
            var nowNextImg = qs('.dfc-left .dfc-img.next', root);
            var nowCurPic = nowCurImg ? nowCurImg.closest('picture') : null;
            var nowNextPic = nowNextImg ? nowNextImg.closest('picture') : null;
            if (nowCurPic) {
                nowCurPic.classList.add('dfc-pic-current');
                nowCurPic.classList.remove('dfc-pic-next');
            }
            if (nowNextPic) {
                nowNextPic.classList.add('dfc-pic-next');
                nowNextPic.classList.remove('dfc-pic-current');
            }

            syncNextFromCurrent(root);

            if (typeof nextTitle === 'string' && nextTitle.trim()) {
                var nowCur = qs('.dfc-left .dfc-img.current', root);
                var nowNext = qs('.dfc-left .dfc-img.next', root);
                if (nowCur) nowCur.alt = nextTitle;
                if (nowNext) nowNext.alt = '';
            }
        };
        box.addEventListener('transitionend', handler);
    }

    // --- warstwy dla FEATURED (prawa kolumna) ---
    function ensureFeaturedLayers(root) {
        var box = qs('.dfc-featured', root);
        if (!box) return;

        var cur = qs('.dfc-featured .dfc-featured-item.current', root);
        var next = qs('.dfc-featured .dfc-featured-item.next', root);

        if (!cur) {
            var curDiv = document.createElement('div');
            curDiv.className = 'dfc-featured-item current';
            curDiv.innerHTML = '';
            box.appendChild(curDiv);
        }
        if (!next) {
            var nextDiv = document.createElement('div');
            nextDiv.className = 'dfc-featured-item next';
            box.appendChild(nextDiv);
        }
    }

    // na górze pliku zostaw: var DFC_FEAT_TOKEN = 0;

    function animateFeatured(root, dir, nextHTML) {
        var box = qs('.dfc-featured', root);
        if (!box) return;

        var cur = qs('.dfc-featured .dfc-featured-item.current', root);
        var next = qs('.dfc-featured .dfc-featured-item.next', root);
        if (!cur || !next) return;

        // Jeżeli treść identyczna – nic nie rób
        var curHTML = (cur.innerHTML || '').replace(/\s+/g, ' ').trim();
        var newHTML = (nextHTML || '').replace(/\s+/g, ' ').trim();
        if (curHTML && newHTML && curHTML === newHTML) {
            box.classList.remove('anim');
            box.style.height = '';
            box.style.pointerEvents = '';
            return;
        }

        var token = ++DFC_FEAT_TOKEN;

        // Zablokuj wysokość na czas animacji
        var h = cur.offsetHeight || box.offsetHeight || 0;
        if (h > 0) box.style.height = h + 'px';

        // Wstaw nową zawartość do "next"
        next.innerHTML = nextHTML || '<div class="dfc-placeholder">Wybierz polecany produkt</div>';

        // Kierunek
        var startX = (dir === 'prev') ? '-100%' : '100%';  // skąd wjedzie next
        var outX = (dir === 'prev') ? '100%' : '-100%'; // dokąd wyjedzie current

        // Warunki początkowe (bez transition)
        cur.style.zIndex = '1';
        next.style.zIndex = '2';

        cur.style.transition = 'none';
        next.style.transition = 'none';

        cur.style.transform = 'translateX(0)';              // current zaczyna w centrum
        next.style.transform = 'translateX(' + startX + ')'; // next czeka poza ekranem

        // Reflow
        next.offsetHeight;

        // Start ruchu
        var duration = 1000;  // ms
        var easing = 'ease';

        cur.style.transition = 'transform ' + duration + 'ms ' + easing;
        next.style.transition = 'transform ' + duration + 'ms ' + easing;

        box.classList.add('anim');
        box.style.pointerEvents = 'none';

        requestAnimationFrame(function () {
            cur.style.transform = 'translateX(' + outX + ')'; // current wyjeżdża
            next.style.transform = 'translateX(0)';            // next wjeżdża
        });

        // Kończymy na "next" (on zawsze animuje)
        var onEnd = function (ev) {
            if (ev.target !== next) return;
            if (token !== DFC_FEAT_TOKEN) return;

            next.removeEventListener('transitionend', onEnd);

            // Zamiana ról
            cur.classList.remove('current');
            cur.classList.add('next');

            next.classList.remove('next');
            next.classList.add('current');

            // ⚠️ KLUCZ: zaparkuj "stary" slajd (teraz .next) poza ekranem,
            // żeby nie przykrywał aktualnego .current z powodu z-indexu i position:absolute
            cur.style.transition = 'none';
            next.style.transition = 'none';

            // nowy current stoi w centrum
            next.style.transform = 'translateX(0)';

            // stary (teraz "next") parkujemy po tej stronie, w którą wyjechał
            cur.style.transform = 'translateX(' + outX + ')';

            // wyczyść tymczasowe z-indexy
            cur.style.zIndex = '';
            next.style.zIndex = '';

            // reflow i odblokowanie
            next.offsetHeight;
            cur.style.transition = '';
            next.style.transition = '';

            box.classList.remove('anim');
            box.style.height = '';
            box.style.pointerEvents = '';
        };

        next.addEventListener('transitionend', onEnd);
    }

    // --- meta z listy ---
    function readCollectionsMeta(root) {
        return qsa('#dfc-ids li', root).map(function (li) {
            return {
                cat: parseInt(li.getAttribute('data-cat'), 10),
                img: li.getAttribute('data-img') || '',
                imgMobile: li.getAttribute('data-img-mobile') || '',
                imgXS: li.getAttribute('data-img-xs') || '',
                compareImg: li.getAttribute('data-img-compare') || '',
                compareStart: parseInt(li.getAttribute('data-compare-start'), 10) || 50,
                compareLabel: li.getAttribute('data-compare-label') || '',
                title: li.getAttribute('data-title') || '',
                link: li.getAttribute('data-link') || ''
            };
        }).filter(function (x) { return !!x.cat; });
    }

    // Wybór właściwego obrazka (desktop / mobile / xs)
    function pickImg(meta) {
        var isXS = window.matchMedia('(max-width: 499.98px)').matches;
        var isSmall = window.matchMedia('(max-width: 767.98px)').matches;
        if (isXS && meta.imgXS) return meta.imgXS;
        return (isSmall && meta.imgMobile) ? meta.imgMobile : meta.img;
    }

    function hasCompare(meta) {
        return !!(meta && meta.compareImg && String(meta.compareImg).trim() !== '');
    }

    function initCompare(root) {
        var compare = qs('.dfc-compare', root);
        if (!compare) return;

        var after = qs('.dfc-compare-after', compare);
        var handle = qs('.dfc-compare-handle', compare);
        var range = qs('.dfc-compare-range', compare);

        if (!after || !handle) return;

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function setCompare(percent) {
            percent = parseFloat(percent);
            if (isNaN(percent)) percent = 50;
            percent = clamp(percent, 0, 100);

            after.style.clipPath = 'inset(0 0 0 ' + percent + '%)';
            handle.style.left = percent + '%';
            compare.setAttribute('data-compare-current', percent);

            if (range) {
                range.value = percent;
            }
        }

        function setCompareFromPointer(clientX) {
            var rect = compare.getBoundingClientRect();
            if (!rect.width) return;

            var x = clamp(clientX - rect.left, 0, rect.width);
            var percent = (x / rect.width) * 100;

            setCompare(percent);
        }

        var dragging = false;

        function onMove(e) {
            if (!dragging) return;

            if (e.cancelable) {
                e.preventDefault();
            }

            var point = e.touches ? e.touches[0] : e;
            if (!point) return;

            setCompareFromPointer(point.clientX);
        }

        function onUp() {
            dragging = false;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onUp);
        }

        function onDown(e) {
            var point = e.touches ? e.touches[0] : e;
            if (!point) return;

            dragging = true;
            setCompareFromPointer(point.clientX);

            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp, { passive: true });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onUp, { passive: true });
        }

        handle.addEventListener('mousedown', onDown, { passive: false });
        handle.addEventListener('touchstart', onDown, { passive: false });

        compare.addEventListener('click', function (e) {
            setCompareFromPointer(e.clientX);
        });

        if (range) {
            range.setAttribute('min', '0');
            range.setAttribute('max', '100');
            range.setAttribute('step', '0.01');

            range.addEventListener('input', function () {
                setCompare(this.value);
            });

            range.addEventListener('change', function () {
                setCompare(this.value);
            });
        }

        setCompare(compare.getAttribute('data-start') || 50);
    }

    function updateCompareInCurrent(root, meta) {
        var compare = qs('.dfc-compare', root);
        if (!compare) return;

        var afterImg = qs('.dfc-compare-after img', compare);
        var afterSourceXs = qs('.dfc-compare-after source[media*="499.98px"]', compare);
        var afterSourceMd = qs('.dfc-compare-after source[media*="767.98px"]', compare);
        var label = qs('.dfc-compare-label', compare);
        var range = qs('.dfc-compare-range', compare);

        if (afterImg) {
            afterImg.src = meta.compareImg || '';
        }

        if (afterSourceXs) {
            afterSourceXs.srcset = meta.compareImg || '';
        }

        if (afterSourceMd) {
            afterSourceMd.srcset = meta.compareImg || '';
        }

        if (label) {
            label.textContent = meta.compareLabel || '';
            label.style.display = meta.compareLabel ? '' : 'none';
        }

        if (range) {
            range.value = (typeof meta.compareStart === 'number' ? meta.compareStart : 50);
        }

        compare.setAttribute('data-compare-start', (typeof meta.compareStart === 'number' ? meta.compareStart : 50));

        var afterWrap = qs('.dfc-compare-after', compare);
        var handle = qs('.dfc-compare-handle', compare);

        if (afterWrap && handle) {
            var start = (typeof meta.compareStart === 'number' ? meta.compareStart : 50);
            if (start < 0) start = 0;
            if (start > 100) start = 100;

            afterWrap.style.clipPath = 'inset(0 0 0 ' + start + '%)';
            handle.style.left = start + '%';
        }
    }

    // ustaw <source> tylko w warstwie .next (XS i Mobile)
    function setNextSources(root, urlXS, urlMobile) {
        var nextImg = qs('.dfc-left .dfc-img.next', root);
        if (!nextImg) return;
        var pic = nextImg.closest('picture');
        if (!pic) return;

        var srcXs = pic.querySelector('source[media*="499.98px"]');
        if (srcXs) srcXs.srcset = urlXS || '';

        var srcMd = pic.querySelector('source[media*="767.98px"]');
        if (srcMd) srcMd.srcset = urlMobile || '';

        var anySrc = srcXs || srcMd;
        if (anySrc && anySrc.parentNode && anySrc.parentNode.load) {
            try { anySrc.parentNode.load(); } catch (e) { }
        }
    }

    function setNextMobileSource(root, url) {
        var nextImg = qs('.dfc-left .dfc-img.next', root);
        if (!nextImg) return;
        var pic = nextImg.closest('picture');
        if (!pic) return;

        var srcEl = pic.querySelector('source[media*="767.98px"]');
        if (srcEl) {
            srcEl.srcset = url || '';
            var img = pic.querySelector('img');
            if (img) { img.src = img.src; }
        }
    }

    function syncNextFromCurrent(root) {
        var curImg = qs('.dfc-left .dfc-img.current', root);
        var nextImg = qs('.dfc-left .dfc-img.next', root);
        if (!curImg || !nextImg) return;

        var curPic = curImg.closest('picture');
        var nextPic = nextImg.closest('picture');
        if (!curPic || !nextPic) return;

        curPic.classList.add('dfc-pic-current');
        curPic.classList.remove('dfc-pic-next');
        nextPic.classList.add('dfc-pic-next');
        nextPic.classList.remove('dfc-pic-current');

        var curXs = curPic.querySelector('source[media*="499.98px"]');
        var curMd = curPic.querySelector('source[media*="767.98px"]');
        var nextXs = nextPic.querySelector('source[media*="499.98px"]');
        var nextMd = nextPic.querySelector('source[media*="767.98px"]');

        if (nextXs && curXs) nextXs.srcset = curXs.srcset || '';
        if (nextMd && curMd) nextMd.srcset = curMd.srcset || '';

        nextImg.src = curImg.currentSrc || curImg.src || '';
    }

    function mountNextPicture(root, meta) {
        var box = qs('.dfc-left', root);
        if (!box) return;

        var linkEl = qs('.dfc-left-link', root) || box;

        var oldNextImg = box.querySelector('.dfc-img.next');
        var oldNextPic = oldNextImg ? oldNextImg.closest('picture') : null;
        if (oldNextPic && oldNextPic.parentNode) {
            oldNextPic.parentNode.removeChild(oldNextPic);
        }

        function esc(u) { return (u || '').replace(/"/g, '&quot;'); }
        var html =
            '<picture class="dfc-pic-next">' +
            (meta.imgXS ? ('<source media="(max-width: 499.98px)" srcset="' + esc(meta.imgXS) + '">') : '') +
            (meta.imgMobile ? ('<source media="(max-width: 767.98px)" srcset="' + esc(meta.imgMobile) + '">') : '') +
            '<img class="dfc-img next" src="' + esc(meta.img || meta.imgMobile || meta.imgXS || '') + '" alt="" loading="lazy" style="transform:translateX(100%);">' +
            '</picture>';

        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        var pic = wrap.firstChild;

        linkEl.appendChild(pic);
    }

    function setActiveTab(root, catId) {
        var tabs = qsa('.dfc-tab', root);
        tabs.forEach(function (btn) {
            var on = parseInt(btn.getAttribute('data-cat'), 10) === catId;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    }

    function updateCollectionCounter(root, currentIndex, total) {
        if (!root) return;

        var currentEl = qs('.dfc-collection-counter-current', root);
        var totalEl = qs('.dfc-collection-counter-total', root);
        var counter = qs('.dfc-collection-counter', root);

        if (!currentEl || !totalEl) return;

        var current = parseInt(currentIndex, 10);
        var all = parseInt(total, 10);

        if (!all || all < 1) {
            if (counter) {
                counter.style.display = 'none';
            }
            return;
        }

        if (counter) {
            counter.style.display = '';
        }

        currentEl.textContent = String(current + 1);
        totalEl.textContent = String(all);
    }

    function updateCollectionCounterInNode(node, currentIndex, total) {
        if (!node) return;

        var currentEl = qs('.dfc-collection-counter-current', node);
        var totalEl = qs('.dfc-collection-counter-total', node);
        var counter = qs('.dfc-collection-counter', node);

        if (!currentEl || !totalEl) return;

        var current = parseInt(currentIndex, 10);
        var all = parseInt(total, 10);

        if (!all || all < 1) {
            if (counter) {
                counter.style.display = 'none';
            }
            return;
        }

        if (counter) {
            counter.style.display = '';
        }

        currentEl.textContent = String(current + 1);
        totalEl.textContent = String(all);
    }

    function getStickyBar(root) {
        return qs('[data-dfc-sticky-bar]', root);
    }

    function getStickyTabs(root) {
        return qs('[data-dfc-sticky-tabs]', root);
    }

    function setActiveStickyTab(root, catId) {
        var tabs = qsa('.dfc-sticky-tab', root);

        tabs.forEach(function (btn) {
            var on = parseInt(btn.getAttribute('data-cat'), 10) === catId;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.setAttribute('tabindex', on ? '0' : '-1');
        });
    }

    function updateStickyCounter(root, currentIndex, total) {
        var bar = getStickyBar(root);
        if (!bar) return;

        var currentEl = qs('.dfc-sticky-bar__current', bar);
        var totalEl = qs('.dfc-sticky-bar__total', bar);

        if (!currentEl || !totalEl) return;

        currentEl.textContent = String((parseInt(currentIndex, 10) || 0) + 1);
        totalEl.textContent = String(parseInt(total, 10) || 0);
    }

    function syncStickyTabsScroll(root) {
        var mainTabs = qs('.dfc-tabs', root);
        var stickyTabs = getStickyTabs(root);

        if (!mainTabs || !stickyTabs) return;

        stickyTabs.scrollLeft = mainTabs.scrollLeft;
        updateStickyTabsScrollbar(root);
    }

    function scrollStickyTabIntoView(root, catId) {
        var stickyTabs = getStickyTabs(root);
        if (!stickyTabs) return;

        var activeTab = stickyTabs.querySelector('.dfc-sticky-tab[data-cat="' + catId + '"]');
        if (!activeTab) return;

        var wrapRect = stickyTabs.getBoundingClientRect();
        var tabRect = activeTab.getBoundingClientRect();

        if (tabRect.left < wrapRect.left) {
            stickyTabs.scrollLeft -= (wrapRect.left - tabRect.left + 16);
        } else if (tabRect.right > wrapRect.right) {
            stickyTabs.scrollLeft += (tabRect.right - wrapRect.right + 16);
        }
    }

    function syncStickyBarState(root, currentIndex, total, catId) {
        setActiveStickyTab(root, catId);
        updateStickyCounter(root, currentIndex, total);
        scrollStickyTabIntoView(root, catId);
    }

    function updateStickyBarVisibility(root) {
        var bar = getStickyBar(root);
        if (!bar || !root) return;

        var rect = root.getBoundingClientRect();
        var viewportH = window.innerHeight || document.documentElement.clientHeight || 0;

        var rootTopPassed = rect.top < -120;

        // 🔥 breakpoint mobile/tablet
        var isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        var bottomOffset = isMobile ? 600 : 800;
        var rootBottomStillVisible = rect.bottom > bottomOffset;

        var rootVisibleInViewport = rect.top < viewportH && rect.bottom > 0;

        var shouldShow = rootVisibleInViewport && rootTopPassed && rootBottomStillVisible;

        bar.classList.toggle('is-visible', shouldShow);
        bar.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');

        document.body.classList.toggle('dfc-sticky-bar-visible', shouldShow);
    }

    function bindStickyBar(root) {
        if (!root || root.__dfcStickyBound) return;
        root.__dfcStickyBound = true;

        var mainTabs = qs('.dfc-tabs', root);
        var stickyTabs = getStickyTabs(root);

        enableStickyTabsDrag(root);
        bindStickyTabsScrollbar(root);

        if (mainTabs && stickyTabs) {
            mainTabs.addEventListener('scroll', function () {
                syncStickyTabsScroll(root);
            }, { passive: true });

            stickyTabs.addEventListener('scroll', function () {
                updateStickyTabsScrollbar(root);
            }, { passive: true });

            stickyTabs.addEventListener('wheel', function (e) {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    stickyTabs.scrollLeft += e.deltaY;
                    updateStickyTabsScrollbar(root);
                    e.preventDefault();
                }
            }, { passive: false });
        }

        function refreshStickyBar() {
            updateStickyBarVisibility(root);
            updateStickyTabsScrollbar(root);
        }

        window.addEventListener('scroll', refreshStickyBar, { passive: true });
        window.addEventListener('resize', refreshStickyBar);

        setTimeout(function () {
            syncStickyTabsScroll(root);
            refreshStickyBar();
        }, 0);
    }

    function syncShortDescription(root, htmlMain) {
        var leftWrap = qs('.dfc-left-wrap', root);
        if (!leftWrap) return;

        var currentBlock = qs('.dfc-short-description-block', leftWrap);
        var anchor = qs('.dfc-all-link-left', leftWrap);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingBlock = qs('.dfc-short-description-block', tmp);

        if (currentBlock) {
            currentBlock.remove();
        }

        if (incomingBlock && anchor) {
            anchor.insertAdjacentElement('beforebegin', incomingBlock);
        }
    }

    function syncBadges(root, htmlMain) {
        var leftWrap = qs('.dfc-left-wrap', root);
        if (!leftWrap) return;

        var currentBadges = qs('.dfc-badges', leftWrap);
        var shortDescription = qs('.dfc-short-description-block', leftWrap);
        var allLink = qs('.dfc-all-link-left', leftWrap);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingBadges = qs('.dfc-badges', tmp);

        if (currentBadges) {
            currentBadges.remove();
        }

        if (!incomingBadges) {
            return;
        }

        if (shortDescription) {
            shortDescription.insertAdjacentElement('beforebegin', incomingBadges);
            return;
        }

        if (allLink) {
            allLink.insertAdjacentElement('beforebegin', incomingBadges);
        }
    }

    function syncLowestPrice(root, htmlMain) {
        var rightCol = qs('.dfc-right', root);
        if (!rightCol) return;

        var currentBlock = qs('.dfc-lowest-price', rightCol);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingBlock = qs('.dfc-lowest-price', tmp);
      
        if (currentBlock) {
            currentBlock.remove();
        }

        if (!incomingBlock) {
            return;
        }

        var featured = qs('.dfc-featured', rightCol);

        if (featured) {
            featured.insertAdjacentElement('afterend', incomingBlock);
        }
    }

	function syncFeaturedCountdown(root, htmlMain) {
        if (!root) return;

        var leftWrap = qs('.dfc-left-wrap', root);
        if (!leftWrap) return;

        var currentCountdown = qs('.dfc-featured-countdown-wrap', leftWrap);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingCountdown = qs('.dfc-featured-countdown-wrap', tmp);
        var allLink = qs('.dfc-all-link-left', leftWrap);

        if (currentCountdown) {
            currentCountdown.remove();
        }

        if (!incomingCountdown) {
            return;
        }

		incomingCountdown.classList.remove('is-ready');
        incomingCountdown.classList.add('is-loading'); 

        qsa('.psproductcountdown', incomingCountdown).forEach(function (el) {
            el.classList.remove('psproductcountdown');
            el.classList.add('pspc-inactive');
        });

        if (allLink) {
            allLink.insertAdjacentElement('afterend', incomingCountdown);
        } else {
            leftWrap.appendChild(incomingCountdown);
        }

        reinitFeaturedCountdown(root);
    }

	function reinitFeaturedCountdown(root) {
        if (!root) return;

        var countdownWrap = qs('.dfc-featured-countdown-wrap', root);
        if (!countdownWrap) return;

        var inactiveNodes = qsa('.pspc-inactive', countdownWrap);
        if (!inactiveNodes.length) {
            countdownWrap.classList.remove('is-loading');
            countdownWrap.classList.add('is-ready');
            return;
        }

        countdownWrap.classList.remove('is-ready');
        countdownWrap.classList.add('is-loading');

        setTimeout(function () {
            try {
                if (typeof pspc_initCountdown === 'function') {
                    pspc_initCountdown('.dfc-featured-countdown-wrap .pspc-inactive');
                }
            } catch (e) {
                console.error('DFC countdown reinit error:', e);
            }

            setTimeout(function () {
                countdownWrap.classList.remove('is-loading');
                countdownWrap.classList.add('is-ready');
            }, 80);
        }, 30);
    }

    function syncCollectionScope(root, htmlMain) {
        var leftWrap = qs('.dfc-left-wrap', root);
        if (!leftWrap) return;

        var current = qs('.dfc-collection-scope', leftWrap);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incoming = qs('.dfc-collection-scope', tmp);

        if (current) {
            current.remove();
        }

        if (!incoming) return;

        var badges = qs('.dfc-badges', leftWrap);
        var shortDescription = qs('.dfc-short-description-block', leftWrap);
        var allLink = qs('.dfc-all-link-left', leftWrap);

        // kolejność: badges -> scope -> description
        if (badges) {
            badges.insertAdjacentElement('afterend', incoming);
            return;
        }

        if (shortDescription) {
            shortDescription.insertAdjacentElement('beforebegin', incoming);
            return;
        }

        if (allLink) {
            allLink.insertAdjacentElement('beforebegin', incoming);
        }
    }

    function syncBundle(root, htmlMain) {
        if (!root) return;

        var currentSection = qs('.dfc-bundle-section', root);

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingSection = qs('.dfc-bundle-section', tmp);
        var mainGrid = qs('.dfc-main-grid', root);

        if (currentSection) {
            currentSection.remove();
        }

        if (!incomingSection || !mainGrid) {
            renderBundleSummary(root);
            return;
        }

        mainGrid.insertAdjacentElement('afterend', incomingSection);

        renderBundleSummary(root);
    }

    function syncLeftBlock(root, htmlMain) {
        if (!htmlMain) return;

        var currentLeft = qs('.dfc-left', root);
        if (!currentLeft) return;

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain || '';

        var incomingLeft = qs('.dfc-left', tmp);
        if (!incomingLeft) return;

        currentLeft.outerHTML = incomingLeft.outerHTML;

        ensureLeftLayers(root);
        initCompare(root);

        var newLeftLink = qs('.dfc-left-link', root);
        var newTitle = qs('.dfc-title', root);

        if (newLeftLink && newLeftLink.__dfcHrefPending) {
            newLeftLink.setAttribute('href', newLeftLink.__dfcHrefPending);
            delete newLeftLink.__dfcHrefPending;
        }

        if (newLeftLink && newLeftLink.__dfcAriaPending) {
            newLeftLink.setAttribute('aria-label', newLeftLink.__dfcAriaPending);
            delete newLeftLink.__dfcAriaPending;
        }

        if (newTitle && newTitle.__dfcTitlePending) {
            newTitle.textContent = newTitle.__dfcTitlePending;
            delete newTitle.__dfcTitlePending;
        }
    }

    function prepareLeftScene(scene) {
        if (!scene) return;

        var compare = scene.querySelector('.dfc-compare');
        if (compare) {
            var after = qs('.dfc-compare-after', compare);
            var handle = qs('.dfc-compare-handle', compare);
            var range = qs('.dfc-compare-range', compare);

            if (after && handle) {
                var start = parseFloat(compare.getAttribute('data-start') || 50);
                if (isNaN(start)) start = 50;
                if (start < 0) start = 0;
                if (start > 100) start = 100;

                after.style.clipPath = 'inset(0 0 0 ' + start + '%)';
                handle.style.left = start + '%';

                if (range) {
                    range.value = start;
                }
            }
        }
    }

    function animateLeftCompare(root, dir, htmlMain, currentIndex, totalCount) {
        if (!htmlMain) return false;

        var left = qs('.dfc-left', root);
        if (!left) return false;

        var tmp = document.createElement('div');
        tmp.innerHTML = htmlMain;

        var incomingLeft = qs('.dfc-left', tmp);
        if (!incomingLeft) return false;

        var currentScene = document.createElement('div');
        currentScene.className = 'dfc-left-scene current';
        currentScene.innerHTML = left.innerHTML;

        var nextScene = document.createElement('div');
        nextScene.className = 'dfc-left-scene next';
        nextScene.innerHTML = incomingLeft.innerHTML;
      
        updateCollectionCounterInNode(nextScene, currentIndex, totalCount);
        prepareLeftScene(currentScene);
        prepareLeftScene(nextScene);

        left.innerHTML = '';
        left.appendChild(currentScene);
        left.appendChild(nextScene);

        var startX = (dir === 'prev') ? '-100%' : '100%';
        var outX = (dir === 'prev') ? '100%' : '-100%';

        currentScene.style.transform = 'translateX(0)';
        nextScene.style.transform = 'translateX(' + startX + ')';

        left.offsetHeight;

        left.classList.add('dfc-left-animating');

        requestAnimationFrame(function () {
            currentScene.style.transform = 'translateX(' + outX + ')';
            nextScene.style.transform = 'translateX(0)';
        });

        var done = false;

        function finish(ev) {
            if (done) return;
            if (ev && ev.target !== nextScene) return;
            done = true;

            nextScene.removeEventListener('transitionend', finish);

            left.classList.remove('dfc-left-animating');
            left.innerHTML = incomingLeft.innerHTML;

            initCompare(root);

            updateCollectionCounter(root, currentIndex, totalCount);
        }

        nextScene.addEventListener('transitionend', finish);

        setTimeout(function () {
            finish();
        }, 1100);

        return true;
    }

    function updateSliderProductsCount(root, count) {
        var countEl = qs('.dfc-slider-count', root);
        if (!countEl) return;

        count = parseInt(count, 10) || 0;
        countEl.textContent = count + ' produktów';
    }

    function seedInitialCache(root, catId, fallbackTitle) {
        if (!root || !catId || DFC_CACHE.has(catId)) return;

        function getText(el) {
            return (el && el.textContent ? el.textContent.trim() : '');
        }

        var titleEl = root.querySelector('.dfc-title');
        var title = getText(titleEl) || fallbackTitle || '';

        var main = root.querySelector('#dfc-main');
        var htmlMain = main ? main.innerHTML : '';

        var slider = root.querySelector('#dfc-slider');
        var htmlSlider = slider ? slider.innerHTML : '';

        var countEl = root.querySelector('.dfc-slider-count');
        var productsCount = 0;

        if (countEl) {
            var match = (countEl.textContent || '').match(/\d+/);
            if (match) {
                productsCount = parseInt(match[0], 10) || 0;
            }
        }

        DFC_CACHE.set(catId, {
            title: title,
            htmlMain: htmlMain,
            htmlSlider: htmlSlider,
            productsCount: productsCount
        });
    }

    function getBundleItemsFromDom(root) {
        if (!root) return [];

        var items = [];
        var rows = qsa('.dfc-bundle-item[data-id-product]', root);

        rows.forEach(function (row) {
            var checkbox = qs('[data-dfc-bundle-checkbox]', row);
            var isChecked = checkbox ? !!checkbox.checked : true;

            if (!isChecked) {
                return;
            }

            var idProduct = parseInt(row.getAttribute('data-id-product'), 10) || 0;
            if (!idProduct) return;

            items.push({
                id_product: idProduct,
                qty: 1,
                id_product_attribute: 0
            });
        });

        return items;
    }

    function formatBundlePriceFromReference(referenceNode, amount) {
        if (!referenceNode) {
            return '';
        }

        var refText = (
            referenceNode.getAttribute('data-default-value') ||
            referenceNode.textContent ||
            ''
        ).trim();

        var normalizedAmount = (Math.round((amount || 0) * 100) / 100).toFixed(2);
        var formattedAmount = normalizedAmount.replace('.', ',');

        if (!refText) {
            return formattedAmount + ' zł';
        }

        var match = refText.match(/(\d{1,3}(?:[\s\u00A0]\d{3})*(?:[.,]\d{2}))/);

        if (match) {
            return refText.replace(match[1], formattedAmount);
        }

        return formattedAmount + ' zł';
    }

    function getBundleSummaryNodes(root) {
        return {
            box: qs('[data-dfc-bundle-box]', root),
            count: qs('[data-dfc-bundle-count]', root),
            total: qs('[data-dfc-bundle-total]', root),
            regular: qs('[data-dfc-bundle-regular-total]', root),
            regularRow: qs('[data-dfc-bundle-regular-row]', root),
            savings: qs('[data-dfc-bundle-savings]', root),
            savingsRow: qs('[data-dfc-bundle-savings-row]', root),
            addBtn: qs('[data-dfc-bundle-add]', root)
        };
    }

    function calculateBundleSummary(root) {
        var rows = qsa('.dfc-bundle-item[data-id-product]', root);

        var selectedCount = 0;
        var total = 0;
        var regularTotal = 0;

        rows.forEach(function (row) {
            var checkbox = qs('[data-dfc-bundle-checkbox]', row);
            var isChecked = checkbox ? !!checkbox.checked : true;

            row.classList.toggle('is-unchecked', !isChecked);
 
            if (!isChecked) {
                return;
            }

            selectedCount++;

            var priceAmount = parseFloat(row.getAttribute('data-price-amount')) || 0;
            var regularAmount = parseFloat(row.getAttribute('data-regular-price-amount')) || 0;

            total += priceAmount;

            if (regularAmount > priceAmount && priceAmount > 0) {
                regularTotal += regularAmount;
            } else {
                regularTotal += priceAmount;
            }
        });

        var savings = regularTotal - total;
        if (savings < 0) {
            savings = 0;
        }

        return {
            selectedCount: selectedCount,
            total: total,
            regularTotal: regularTotal,
            savings: savings
        };
    }

    function renderBundleSummary(root) {
        if (!root) return;

        var nodes = getBundleSummaryNodes(root);
        if (!nodes.box) return;

        var summary = calculateBundleSummary(root);

        if (nodes.count) {
            nodes.count.textContent = String(summary.selectedCount);
        }

        if (nodes.total) {
            var totalDefault = nodes.total.getAttribute('data-default-value') || '';
            var totalHtml = formatBundlePriceFromReference(nodes.total, summary.total);

            if (!totalHtml && totalDefault) {
                totalHtml = totalDefault;
            }

            nodes.total.innerHTML = totalHtml;
        }

        if (nodes.regular && nodes.regularRow) {
            if (summary.regularTotal > summary.total && summary.selectedCount > 0) {
                nodes.regularRow.style.display = '';
                nodes.regular.innerHTML = formatBundlePriceFromReference(nodes.regular, summary.regularTotal);
            } else {
                nodes.regularRow.style.display = 'none';
            }
        }

        if (nodes.savings && nodes.savingsRow) {
            if (summary.savings > 0 && summary.selectedCount > 0) {
                nodes.savingsRow.style.display = '';
                nodes.savings.innerHTML = formatBundlePriceFromReference(nodes.savings, summary.savings);
            } else {
                nodes.savingsRow.style.display = 'none';
            }
        }

        if (nodes.addBtn) {
            if (summary.selectedCount > 0) {
                nodes.addBtn.disabled = false;
                nodes.addBtn.removeAttribute('disabled');
            } else {
                nodes.addBtn.disabled = true;
                nodes.addBtn.setAttribute('disabled', 'disabled');
            }
        }
    }

    function bindBundleSelection(root) {
        if (!root) return;
        if (root.__dfcBundleSelectionBound) return;
        root.__dfcBundleSelectionBound = true;

        root.addEventListener('change', function (e) {
            var checkbox = e.target && e.target.closest('[data-dfc-bundle-checkbox]');
            if (!checkbox) return;

            renderBundleSummary(root);
        });

        renderBundleSummary(root);
    }

    function addBundleToCart(root) {
        if (!root || !window.__dfc || !window.__dfc.bundlecart) {
            return;
        }

        var btn = qs('[data-dfc-bundle-add]', root) || qs('.dfc-bundle-add', root);
        if (!btn) return;

        var bundleItems = getBundleItemsFromDom(root);

        if (!bundleItems.length) {
            alert('Wybierz przynajmniej jeden produkt z zestawu.');
            return;
        }

        btn.classList.add('is-loading');
        btn.disabled = true;
        btn.setAttribute('disabled', 'disabled');

        fetch(window.__dfc.bundlecart, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json'
            },
            body:
                'ajax=1&bundle_items=' +
                encodeURIComponent(JSON.stringify(bundleItems))
        })
        .then(function (r) {
            return r.json();
        })
        .then(function (resp) {
            if (!resp || !resp.ok) {
                throw new Error((resp && (resp.error || resp.message)) || 'Nie udało się dodać zestawu do koszyka.');
            }

            if (window.prestashop && typeof window.prestashop.emit === 'function') {
                window.prestashop.emit('updateCart', {
                    reason: {
                        linkAction: 'add-to-cart',
                        idProduct: bundleItems[0] ? bundleItems[0].id_product : 0,
                        idProductAttribute: 0
                    },
                    resp: resp
                });
            }
        })
        .catch(function (err) {
            console.error('DFC bundle add error:', err);
            alert(err && err.message ? err.message : 'Nie udało się dodać zestawu do koszyka.');
        })
        .finally(function () {
            btn.classList.remove('is-loading');
            renderBundleSummary(root);
        });
    }

    function getArrangementOverlay(root) {
        return qs('[data-dfc-arrangement-overlay]', root);
    }

    function closeArrangementOverlay(root) {
        if (!root) return;

        var overlay = getArrangementOverlay(root);
        if (!overlay) return;

        overlay.classList.remove('is-active');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('dfc-arrangement-open');
    }

    function openArrangementOverlay(root) {
        if (!root) return;

        var overlay = getArrangementOverlay(root);
        if (!overlay) return;

        overlay.classList.add('is-active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('dfc-arrangement-open');
    }

    function bindArrangementOverlay(root) {
        if (!root) return;
        if (root.__dfcArrangementBound) return;
        root.__dfcArrangementBound = true;

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;

            var overlay = getArrangementOverlay(root);
            if (!overlay || !overlay.classList.contains('is-active')) return;

            closeArrangementOverlay(root);
        });
    }

    window.addEventListener('load', function () {
        var root = qs('#dfcollection');
        if (!root || !window.__dfc) return;

        // GUARD: nie inicjuj drugi raz jeśli JS został dołączony podwójnie
        if (root.__dfcInit) return;
        root.__dfcInit = true;

        var AJAX_URL = window.__dfc.ajax;

        enableTabsDrag(root);
        bindTabsScrollbar(root);
        ensureLeftLayers(root);
        ensureFeaturedLayers(root);
        initCompare(root);
        bindBundleSelection(root);
        bindArrangementOverlay(root);
        renderBundleSummary(root);
		var initialCountdownWrap = qs('.dfc-featured-countdown-wrap', root);
        if (initialCountdownWrap) {
            if (qsa('.pspc-inactive', initialCountdownWrap).length) {
                initialCountdownWrap.classList.remove('is-ready');
                initialCountdownWrap.classList.add('is-loading');
                reinitFeaturedCountdown(root);
            } else {
                initialCountdownWrap.classList.remove('is-loading');
                initialCountdownWrap.classList.add('is-ready');
            }
        }

        var items = readCollectionsMeta(root);
        if (!items.length) return;

        var renderedCat = (function () {
            var mg = qs('.dfc-main-grid', root);
            var v = mg ? parseInt(mg.getAttribute('data-cat'), 10) : 0;
            return v || (items[0] && items[0].cat) || 0;
        })();

        var savedCat = getLastCollection();
        var renderedIdx = Math.max(0, items.findIndex(function (x) { return x.cat === renderedCat; }));
        if (renderedIdx < 0) renderedIdx = 0;

        var savedIdx = -1;
        if (savedCat) {
            savedIdx = items.findIndex(function (x) { return x.cat === savedCat; });
        }

        var curIdx = renderedIdx;
        var busy = false;

        bindStickyBar(root);
        updateStickyTabsScrollbar(root);

        seedInitialCache(root, items[renderedIdx].cat, items[renderedIdx].title);
        setActiveTab(root, items[renderedIdx].cat);
        updateCollectionCounter(root, renderedIdx, items.length);
        syncStickyBarState(root, renderedIdx, items.length, items[renderedIdx].cat);

        var initialSlider = qs('#dfc-slider', root);
        initSlider(initialSlider);
        refreshMiniAtc(initialSlider);

        var initialFeatured = qs('.dfc-featured', root);
        refreshMiniAtc(initialFeatured);
        if (isDesktopDFC()) {
            updateFeaturedMinHeight(root, measureCurrentFeaturedHeight(root));
            syncMainColumnsHeight(root);
        } else {
            resetDFCHeights(root);
        }

        setTimeout(function () {
            if (isDesktopDFC()) {
                syncMainColumnsHeight(root);
            } else {
               resetDFCHeights(root);
            }
        }, 120);

        setTimeout(function () {
            if (isDesktopDFC()) {
                syncMainColumnsHeight(root);
            } else {
                resetDFCHeights(root);
            }
        }, 400);

        var grid = qs('.dfc-main-grid', root);
        if (grid) grid.setAttribute('data-cat', String(items[renderedIdx].cat));

        var nextIdx = (renderedIdx + 1) % items.length;
        var prevIdx = (renderedIdx - 1 + items.length) % items.length;
        [
            items[nextIdx],
            items[prevIdx],
            items[(renderedIdx + 2) % items.length],
            items[(renderedIdx - 2 + items.length) % items.length],
            items[(renderedIdx + 3) % items.length],
            items[(renderedIdx - 3 + items.length) % items.length]
        ].forEach(function (it) {
            if (it) prefetch(it.cat, AJAX_URL);
        });
      
        warmupFeaturedHeights(root, items, AJAX_URL);

        setTimeout(function () {
            if (isDesktopDFC()) {
                syncMainColumnsHeight(root);
            } else {
                resetDFCHeights(root);
            }
        }, 700);

        qsa('.dfc-tab', root).forEach(function (btn) {
            var cid = parseInt(btn.getAttribute('data-cat'), 10);
            ['mouseenter', 'focus'].forEach(function (evt) {
                btn.addEventListener(evt, function () { prefetch(cid, AJAX_URL); }, { passive: true });
            });
        });

        if (savedIdx !== -1 && savedCat !== renderedCat) {
            setTimeout(function () {
                switchTo(savedIdx, (savedIdx > renderedIdx ? 'next' : 'prev'));
            }, 0);
        } else {
            curIdx = renderedIdx;
            saveLastCollection(items[renderedIdx].cat);
        }

        var mq500 = window.matchMedia('(max-width: 499.98px)');
        if (mq500 && mq500.addEventListener) {
            mq500.addEventListener('change', function () {
                var meta = items[curIdx];
                if (!meta) return;

                var curImg = qs('.dfc-left .dfc-img.current', root);
                var url = pickImg(meta);
                if (curImg && url) curImg.src = url;

                var curPic = qs('.dfc-left .dfc-img.current', root)?.closest('picture');
                var nextPic = qs('.dfc-left .dfc-img.next', root)?.closest('picture');

                if (curPic) {
                    var curXs = curPic.querySelector('source[media*="499.98px"]');
                    var curMd = curPic.querySelector('source[media*="767.98px"]');
                    if (curXs) curXs.srcset = meta.imgXS || '';
                    if (curMd) curMd.srcset = meta.imgMobile || '';
                }
                if (nextPic) {
                    var nextXs = nextPic.querySelector('source[media*="499.98px"]');
                    var nextMd = nextPic.querySelector('source[media*="767.98px"]');
                    if (nextXs) nextXs.srcset = meta.imgXS || '';
                    if (nextMd) nextMd.srcset = meta.imgMobile || '';
                }
            });
        }

        var mq = window.matchMedia('(max-width: 767.98px)');
        if (mq && mq.addEventListener) {
            mq.addEventListener('change', function () {
                var meta = items[curIdx];
                if (!meta) return;

                var curImg = qs('.dfc-left .dfc-img.current', root);
                var url = pickImg(meta);
                if (curImg && url) curImg.src = url;

                var curPic = qs('.dfc-left .dfc-img.current', root)?.closest('picture');
                var nextPic = qs('.dfc-left .dfc-img.next', root)?.closest('picture');
                if (curPic) {
                    var curXs = curPic.querySelector('source[media*="499.98px"]');
                    var curMd = curPic.querySelector('source[media*="767.98px"]');
                    if (curXs) curXs.srcset = meta.imgXS || '';
                    if (curMd) curMd.srcset = meta.imgMobile || '';
                }
                if (nextPic) {
                    var nextXs = nextPic.querySelector('source[media*="499.98px"]');
                    var nextMd = nextPic.querySelector('source[media*="767.98px"]');
                    if (nextXs) nextXs.srcset = meta.imgXS || '';
                    if (nextMd) nextMd.srcset = meta.imgMobile || '';
                }
            });
        }

        window.addEventListener('resize', function () {
            setTimeout(function () {
               if (isDesktopDFC()) {
                   syncMainColumnsHeight(root);
               } else {
                   resetDFCHeights(root);
               }

               syncStickyTabsScroll(root);
               updateStickyBarVisibility(root);
               updateTabsScrollbar(root);
               updateStickyTabsScrollbar(root);
            }, 60);
        });

        function switchTo(index, dir) {
            if (busy) return;
            if (index < 0) index = items.length - 1;
            if (index >= items.length) index = 0;
            if (index === curIdx) return;

            curIdx = index;
            busy = true;

            closeArrangementOverlay(root);

            var token = ++DFC_SWITCH_TOKEN;
            var metaNext = items[curIdx];

            var grid = qs('.dfc-main-grid', root);
            if (grid) grid.setAttribute('data-cat', String(metaNext.cat));

            saveLastCollection(metaNext.cat);

            setActiveTab(root, metaNext.cat);
            updateCollectionCounter(root, curIdx, items.length);
            syncStickyBarState(root, curIdx, items.length, metaNext.cat);

            setTimeout(function () {
                updateTabsScrollbar(root);
                updateStickyTabsScrollbar(root);
            }, 0);

            // linki i nazwy z meta
            qsa('.dfc-all-link, .dfc-see-all', root).forEach(function (btn) {
                if (metaNext.link) {
                    btn.setAttribute('href', metaNext.link);
                }

                if (metaNext.title) {
                    if (btn.classList.contains('dfc-all-link-slider')) {
                        btn.setAttribute('aria-label', 'Zobacz wszystkie produkty ' + metaNext.title);
                    } else {
                        btn.setAttribute('aria-label', 'Poznaj kolekcję ' + metaNext.title);
                    }
                }
            });

            var leftA = qs('.dfc-left-link', root);
            if (leftA && metaNext.link) leftA.setAttribute('href', metaNext.link);
            if (leftA && metaNext.title) leftA.setAttribute('aria-label', 'Poznaj kolekcję ' + metaNext.title);

            var sliderTitle = qs('.dfc-slider-title', root);
            if (sliderTitle && metaNext.title) sliderTitle.textContent = metaNext.title;

            qsa('.dfc-cat-name', root).forEach(function (el) {
                if (metaNext.title) el.textContent = metaNext.title;
            });

            var isCompareMode = hasCompare(metaNext);

            preloadCompareImages(metaNext).finally(function () {
              // prawa kolumna + slider z CACHE / FETCH
              getData(metaNext.cat, AJAX_URL)
                .then(function (resp) {
                    if (token !== DFC_SWITCH_TOKEN) return;

                    if (resp.htmlMain) {
                        if (isCompareMode) {
                            animateLeftCompare(root, dir || 'next', resp.htmlMain, curIdx, items.length);
                        } else {
                            mountNextPicture(root, metaNext);
                            setNextSources(root, metaNext.imgXS || '', metaNext.imgMobile || '');
                            setNextMobileSource(root, metaNext.imgMobile || '');

                            var nextImg = pickImg(metaNext);
                            if (nextImg) {
                                animateLeft(root, dir || 'next', nextImg, resp.title || metaNext.title || '');
                            }
                        }

                        syncLowestPrice(root, resp.htmlMain);
                        syncBadges(root, resp.htmlMain);
                        syncCollectionScope(root, resp.htmlMain);
                        syncShortDescription(root, resp.htmlMain);
						syncFeaturedCountdown(root, resp.htmlMain);
                        syncBundle(root, resp.htmlMain);

                        if (isDesktopDFC()) {
                            syncMainColumnsHeight(root);
                        } else {
                            resetDFCHeights(root);
                        }
                    }

                    var titleEl = qs('.dfc-title', root);
                    if (titleEl && resp.title) titleEl.textContent = resp.title;

                    qsa('.dfc-cat-name', root).forEach(function (el) {
                        if (resp.title) el.textContent = resp.title;
                    });

                    qsa('.dfc-all-link, .dfc-see-all', root).forEach(function (btn) {
                        if (resp.title) {
                            if (btn.classList.contains('dfc-all-link-slider')) {
                                btn.setAttribute('aria-label', 'Zobacz wszystkie produkty ' + resp.title);
                            } else {
                                btn.setAttribute('aria-label', 'Poznaj kolekcję ' + resp.title);
                            }
                        }
                    });

                    var leftA2 = qs('.dfc-left-link', root);
                    if (leftA2 && resp.title) leftA2.setAttribute('aria-label', 'Poznaj kolekcję ' + resp.title);

                    if (resp.title) {
                        var curImg = qs('.dfc-left .dfc-img.current', root);
                        var nxtImg = qs('.dfc-left .dfc-img.next', root);
                        if (curImg) curImg.alt = resp.title;
                        if (nxtImg) nxtImg.alt = '';
                    }

                    // FEATURED (prawa kolumna): wyciągnij miniaturę i animuj — tylko RAZ
                    var featuredBox = qs('.dfc-featured', root);
                    if (featuredBox && resp.htmlMain) {
                        var tmpF = document.createElement('div');
                        tmpF.innerHTML = resp.htmlMain;

                        var newSubheading = tmpF.querySelector('.dfc-subheading');
                        var currentSubheading = qs('.dfc-subheading', root);

                        if (newSubheading && currentSubheading) {
                            currentSubheading.outerHTML = newSubheading.outerHTML;
                        }

                        var newFeat =
                            tmpF.querySelector('.dfc-right .product-miniature') ||
                            tmpF.querySelector('.dfc-right .dfc-placeholder') ||
                            tmpF.querySelector('.dfc-right');
                        if (newFeat) {
                            updateFeaturedMinHeight(root, measureFeaturedHeightFromHtml(newFeat.outerHTML, root));
                            animateFeatured(root, dir || 'next', newFeat.outerHTML);

                            // od razu po wstawieniu nowego HTML-a do warstwy .next
                            refreshMiniAtc(featuredBox);

                            // jeszcze raz w trakcie animacji
                            setTimeout(function () {
                                refreshMiniAtc(featuredBox);
                                if (isDesktopDFC()) {
                                    syncMainColumnsHeight(root);
                                } else {
                                    resetDFCHeights(root);
                                }
                            }, 150);

                           // i jeszcze raz po zakończeniu animacji (duration = 1000)
                           setTimeout(function () {
                               refreshMiniAtc(featuredBox);

                               if (typeof window.initMiniatureVariants === 'function') {
                                   window.initMiniatureVariants();
                               }

                               if (isDesktopDFC()) {
                                   syncMainColumnsHeight(root);
                               } else {
                                   resetDFCHeights(root);
                               }
                               notifyUpdatedProducts();
                           }, 1100);
                        }
                    }
                    notifyUpdatedProducts();

                    var slider = qs('#dfc-slider', root);
                    if (slider && resp.htmlSlider) {
                        // zdejmij slick (jeśli był)
                        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.slick) {
                            var $ = jQuery;
                            var $old = $(slider).find('.dfc-products.slick-initialized');
                            if ($old.length) { $old.slick('unslick'); }
                        }

                        // wyczyść i wstaw świeży HTML
                        slider.innerHTML = resp.htmlSlider;

                        // odpal slick tylko na tym kontenerze
                        initSlider(slider);

                        // ping do wishlisty – po podmianie slidera
                        notifyUpdatedProducts();

                        // KLUCZ: odśwież mini add-to-cart po AJAX-owej podmianie slidera
                        refreshMiniAtc(slider);

                        // REINIT miniaturek wariantów po podmianie slidera
                        if (typeof window.initMiniatureVariants === 'function') {
                            window.initMiniatureVariants();
                        }

                        if (isDesktopDFC()) {
                            syncMainColumnsHeight(root);
                        } else {
                            resetDFCHeights(root);
                        }
                    }

                    var st = qs('.dfc-slider-title', root);
                    if (st && resp.title) st.textContent = resp.title;

                    if (typeof resp.productsCount !== 'undefined') {
                        updateSliderProductsCount(root, resp.productsCount);
                    }
                  
                    updateTabsScrollbar(root);
                })
                .catch(function (err) {
                    console.error('DFCollection AJAX error:', err);
                })
                .finally(function () {
                    if (token === DFC_SWITCH_TOKEN) {
                        var n1 = (curIdx + 1) % items.length;
                        var n2 = (curIdx + 2) % items.length;
                        prefetch(items[n1].cat, AJAX_URL);
                        prefetch(items[n2].cat, AJAX_URL);
                        busy = false;
                    }
                });
            });
        }

        // Delegacja klików (guard przeciwko podwójnemu bindowaniu)
        if (!root.__dfcClicksBound) {
            root.__dfcClicksBound = true;
            root.addEventListener('click', function (e) {            
                var prev = e.target && e.target.closest('.dfc-prev, .dfc-sticky-prev');
                var next = e.target && e.target.closest('.dfc-next, .dfc-sticky-next');
                var bundleAdd = e.target && e.target.closest('.dfc-bundle-add');
                var arrangementOpen = e.target && e.target.closest('[data-dfc-arrangement-open]');
                var arrangementClose = e.target && e.target.closest('[data-dfc-arrangement-close]');

                if (prev) {
                    e.preventDefault();
                    switchTo(curIdx - 1, 'prev');
                    return;
                }

                if (next) {
                    e.preventDefault();
                    switchTo(curIdx + 1, 'next');
                    return;
                }

                if (bundleAdd) {
                    e.preventDefault();
                    addBundleToCart(root);
                    return;
                }

                if (arrangementOpen) {
                    e.preventDefault();
                    openArrangementOverlay(root);
                    return;
                }

                if (arrangementClose) {
                    e.preventDefault();
                    closeArrangementOverlay(root);
                    return;
                }

                var tab = e.target && e.target.closest('.dfc-tab, .dfc-sticky-tab');
                if (tab) {
                    e.preventDefault();
                    var catId = parseInt(tab.getAttribute('data-cat'), 10);
                    var idx = items.findIndex(function (x) { return x.cat === catId; });
                    if (idx !== -1) {
                        var dir = (idx > curIdx || (curIdx === 0 && idx === items.length - 1)) ? 'next' : 'prev';
                        switchTo(idx, dir);
                    }
                }
            });
            root.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;

                var prev = e.target && e.target.closest('.dfc-prev, .dfc-sticky-prev');
                var next = e.target && e.target.closest('.dfc-next, .dfc-sticky-next');
                var tab = e.target && e.target.closest('.dfc-tab, .dfc-sticky-tab');
                var bundleAdd = e.target && e.target.closest('.dfc-bundle-add');
                var arrangementOpen = e.target && e.target.closest('[data-dfc-arrangement-open]');
                var arrangementClose = e.target && e.target.closest('[data-dfc-arrangement-close]');

                if (prev || next || tab || bundleAdd || arrangementOpen || arrangementClose) {
                    e.preventDefault();
                }

                if (prev) {
                    switchTo(curIdx - 1, 'prev');
                    return;
                }

                if (next) {
                    switchTo(curIdx + 1, 'next');
                    return;
                }

                if (bundleAdd) {
                    addBundleToCart(root);
                    return;
                }

                if (arrangementOpen) {
                    openArrangementOverlay(root);
                    return;
                }

                if (arrangementClose) {
                    closeArrangementOverlay(root);
                    return;
                }

                if (tab) {
                    var catId = parseInt(tab.getAttribute('data-cat'), 10);
                    var idx = items.findIndex(function (x) { return x.cat === catId; });
                    if (idx !== -1) {
                        var dir = (idx > curIdx || (curIdx === 0 && idx === items.length - 1)) ? 'next' : 'prev';
                        switchTo(idx, dir);
                    }
                }
            });
        }
    });
})();