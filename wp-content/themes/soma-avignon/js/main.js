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

    // ── Scroll-to-top button ──
    var scrollTopHTML = '<button class="soma-scroll-top" aria-label="Retour en haut">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<polyline points="18 15 12 9 6 15"></polyline>' +
        '</svg></button>';
    $('body').append(scrollTopHTML);

    var $scrollTop = $('.soma-scroll-top');

    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 600) {
            $scrollTop.addClass('visible');
        } else {
            $scrollTop.removeClass('visible');
        }
    });

    $scrollTop.on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 700, 'swing');
    });

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
});
