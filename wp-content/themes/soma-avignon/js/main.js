/**
 * SOMA Avignon — Main JS
 * Animations, scroll effects, counters, floating CTA, scroll-to-top
 */
jQuery(document).ready(function($) {

    // ── Header scroll effect ──
    var $header = $('.site-header, .ast-primary-header');
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 60) {
            $header.addClass('soma-scrolled');
        } else {
            $header.removeClass('soma-scrolled');
        }
    });

    // ── Scroll-triggered animations ──
    var animatedSelectors = '.soma-fade-in, .soma-fade-in-left, .soma-fade-in-right, .soma-scale-in, .soma-stagger';
    var $animatedElements = $(animatedSelectors);

    if ($animatedElements.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        $animatedElements.each(function() {
            observer.observe(this);
        });
    } else {
        $animatedElements.addClass('visible');
    }

    // ── Animated counters ──
    var countersAnimated = false;
    var $counters = $('[data-count]');

    if ($counters.length && 'IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && !countersAnimated) {
                    countersAnimated = true;
                    animateCounters();
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        // Observe the parent container
        var $statsSection = $counters.closest('.soma-stats');
        if ($statsSection.length) {
            counterObserver.observe($statsSection[0]);
        }
    }

    function animateCounters() {
        $counters.each(function() {
            var $this = $(this);
            var target = parseInt($this.data('count'), 10);
            var suffix = $this.data('suffix') || '';
            var prefix = $this.data('prefix') || '';
            var duration = 2000;
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                // Ease out cubic
                var eased = 1 - Math.pow(1 - progress, 3);
                var current = Math.floor(eased * target);
                $this.text(prefix + current + suffix);
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    $this.text(prefix + target + suffix);
                }
            }

            requestAnimationFrame(step);
        });
    }

    // ── Floating CTA button ──
    var $floatingCta = $('.soma-floating-cta');
    if ($floatingCta.length) {
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 500) {
                $floatingCta.addClass('visible');
            } else {
                $floatingCta.removeClass('visible');
            }
        });
    }

    // ── Smooth scroll for anchor links ──
    $(document).on('click', 'a[href^="#"]', function(e) {
        var hash = this.getAttribute('href');
        if (hash.length <= 1) return;

        // Ne pas intercepter les liens RDV (gérés par calcom-popup.js)
        if (hash === '#rdv' || hash === '#RDV') return;

        var $target = $(hash);
        if ($target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $target.offset().top - 80
            }, 700, 'swing');

            if (history.pushState) {
                history.pushState(null, null, hash);
            }
        }
    });

    // ── Parallax subtil sur l'image du hero split ──
    var $heroImg = $('.soma-hero-split__frame');
    if ($heroImg.length && window.matchMedia('(min-width: 769px)').matches) {
        $(window).on('scroll', function() {
            var scrolled = $(this).scrollTop();
            var heroHeight = $('.soma-hero-split').outerHeight();
            if (scrolled < heroHeight) {
                var parallaxVal = scrolled * 0.15;
                $heroImg.css('transform', 'translateY(' + parallaxVal + 'px)');
            }
        });
    }

    // ── Marquee duplication for infinite scroll ──
    var $marquee = $('.soma-marquee-inner');
    if ($marquee.length) {
        var $clone = $marquee.children().clone();
        $marquee.append($clone);
    }

    // ── Carrousel témoignages ──
    $('[data-soma-carousel]').each(function() {
        var $carousel = $(this);
        var $track    = $carousel.find('.soma-carousel-track');
        var $cards    = $track.find('.soma-testimonial');
        var $prev     = $carousel.find('.soma-carousel-prev');
        var $next     = $carousel.find('.soma-carousel-next');
        var $dots     = $carousel.find('.soma-carousel-dots');
        if (!$track.length || !$cards.length) return;

        function visibleCount() {
            var w = $track[0].clientWidth;
            var card = $cards.first()[0].getBoundingClientRect().width;
            return Math.max(1, Math.round(w / card));
        }

        function pageCount() {
            return Math.max(1, $cards.length - visibleCount() + 1);
        }

        function buildDots() {
            $dots.empty();
            var n = pageCount();
            if (n <= 1) {
                $dots.hide();
                $prev.hide(); $next.hide();
                return;
            }
            $dots.show(); $prev.show(); $next.show();
            for (var i = 0; i < n; i++) {
                $('<button type="button" role="tab" aria-label="Aller au témoignage ' + (i + 1) + '"></button>')
                    .attr('data-index', i)
                    .appendTo($dots);
            }
        }

        function activeIndex() {
            var card = $cards.first()[0].getBoundingClientRect().width;
            var gap  = parseFloat(getComputedStyle($track[0]).columnGap || getComputedStyle($track[0]).gap || 0) || 0;
            return Math.round($track[0].scrollLeft / (card + gap));
        }

        function syncDots() {
            var i = activeIndex();
            $dots.children().each(function(idx) {
                $(this).toggleClass('is-active', idx === i);
            });
            $prev.prop('disabled', i <= 0);
            $next.prop('disabled', i >= pageCount() - 1);
        }

        function scrollToIndex(i) {
            var card = $cards.first()[0].getBoundingClientRect().width;
            var gap  = parseFloat(getComputedStyle($track[0]).columnGap || getComputedStyle($track[0]).gap || 0) || 0;
            $track[0].scrollTo({ left: i * (card + gap), behavior: 'smooth' });
        }

        $prev.on('click', function() { scrollToIndex(Math.max(0, activeIndex() - 1)); });
        $next.on('click', function() { scrollToIndex(Math.min(pageCount() - 1, activeIndex() + 1)); });
        $dots.on('click', 'button', function() { scrollToIndex(parseInt($(this).attr('data-index'), 10)); });

        var scrollTimer;
        $track.on('scroll', function() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(syncDots, 60);
        });

        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() { buildDots(); syncDots(); }, 120);
        });

        buildDots();
        syncDots();
    });
});
