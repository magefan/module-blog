/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

/**
 * Posts autload
 */
 define([
    'domReady!',
    'jquery'
], function (domReady, $) {
    'use strict';

    var Lazyload = function (options) {

        var that = this;

        /**
         * Lazyload default options.
         * @type {Object}
         */
        that.defaults = {
            expires: null,
            path: '/',
            domain: null,
            secure: false,
            lifetime: null
        };

        /**
         * Init options
         * @type {Object}
         */
        that.opt = $.extend(that.default ? that.default : {}, options);

        /**
         * Load new content
         */
        function startLoading()
        {
            if (that.opt.current_page < that.opt.last_page && !that.loading) {
                that.loading = true;
                $('.mfblog-show-onload').show();
                $('.mfblog-hide-onload').hide();

                $.ajax({
                    "url": that.opt.page_url[that.opt.current_page + 1],
                    "cache": true,
                    "success": function (data) {
                        var $html = $(data);
                        var ws = that.opt.list_wrapper;
                        var $nw = $html.find(ws);
                        if ($nw.length) {
                            $(ws).append($nw.html());
                            that.opt.current_page++;
                        }

                        if ($html.find('[data-original]').length) {
                            require(['jquery', 'Magefan_Blog/js/lib/mfblogunveil', 'domReady!'], function ($) {
                                $('.mfblogunveil').mfblogunveil();
                            });
                        }

                        endLoading();

                    },
                    "fail": function (xhr, ajaxOptions, thrownError) {
                        console.log(thrownError);
                        endLoading();
                    }
                });
            }
        }

        /**
         * On loading end
         */
        function endLoading()
        {
            that.loading = false;
            $('.mfblog-show-onload').hide();
            if (that.opt.current_page < that.opt.last_page) {
                $('.mfblog-hide-onload').show();
            }
        }

        /* Is not loading now */
        endLoading();

        /* If auto trigger enabled */
        if (that.opt.auto_trigger) {
            var $w = $(window);
            $w.scroll(function () {
                if ($w.scrollTop() + $w.height() >= $(that.opt.trigger_element).offset().top - that.opt.padding) {
                    startLoading();
                }
            });
        }

        /* On trigger element click */
        if (that.opt.trigger_element) {
            $(that.opt.trigger_element).click(function () {
                startLoading();
            });
        }
    };

    return function (options) {
        new Lazyload(options)
    };

});
