/**
 * jQuery Unveil
 * A very lightweight jQuery plugin to lazy load images
 * http://luis-almeida.github.com/unveil
 *
 * Licensed under the MIT license.
 * Copyright 2013 Luís Almeida
 * https://github.com/luis-almeida
 */

define(['jquery'], function($) {
    /* Origin https://github.com/luis-almeida/unveil/blob/master/jquery.unveil.js */
    $.fn.mfblogunveil = function(threshold, callback) {

        var attrib = 'data-original';
        var images = this;

        function revealImage(el) {
            var source = el.getAttribute(attrib);
            if (!source) return;

            if (window.MagefanWebP && window.MagefanWebP.canUseWebP() && !source.includes('mf_webp')) {
                source = window.MagefanWebP.getWebUrl(source);
            }

            var style = el.getAttribute('style') ? (el.getAttribute('style') + '; ') : '';
            source = source.replace('"', '\\"');
            style = style + 'background-image: url("' + source + '");';
            el.setAttribute('style', style);

            if (typeof callback === 'function') callback.call(el);
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        revealImage(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: (threshold || 0) + 'px'
            });

            images.each(function () {
                if (!$(this).is(':hidden')) {
                    observer.observe(this);
                }
            });
        } else {
            /* Fallback for browsers without IntersectionObserver */
            var $w = $(window);
            var th = threshold || 0;

            function mfblogunveil() {
                var inview = images.filter(function () {
                    var $e = $(this);
                    if ($e.is(":hidden")) return;

                    var wt = $w.scrollTop(),
                        wb = wt + $w.height(),
                        et = $e.offset().top,
                        eb = et + $e.height();

                    return eb >= wt - th && et <= wb + th;
                });

                inview.each(function () {
                    revealImage(this);
                });
                images = images.not(inview);
            }

            $w.on("scroll.mfblogunveil resize.mfblogunveil lookup.mfblogunveil", mfblogunveil);

            mfblogunveil();
        }

        return this;
    };
});
