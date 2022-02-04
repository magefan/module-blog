/**
 * Copyright © 2018 Wyomind. All rights reserved.
 */

requireDep = ['jquery', 'underscore', 'mage/template', 'jquery-ui-modules/widget', 'jquery-ui-modules/core', 'mage/translate'];
if (!compareVersions(MAGE_VERSION, "2.3.4")) {
    requireDep = ['jquery', 'underscore', 'mage/template', 'jquery/ui', 'mage/translate'];
}

/*jshint browser:true jquery:true*/
define(requireDep, function ($, _, mageTemplate) {
    'use strict';


    $.widget('mage.wyoea', {
        options: {
            autocomplete: 'off',
            minSearchLength: 1,
            storeCode: '',
            responseFieldElements: 'ul li.qs-option',
            selectClass: 'selected',
            submitBtn: 'button[type="submit"]',
            searchLabel: '[data-role=minisearch-label]',
            searchUrl: '',
            customerGroupId: 0,
            urlUpdateSearchTerm: '',
            debug: false,
            formSelector: ''
        },

        /**
         * Elasticsearch requesters cache (contains the results of the requests)
         */
        cache: [],

        lastRequestData: "",

        observersInitialized: false,

        /**
         * Are template hints enabled?
         */
        templateHintsEnabled: false,


        templateSelectors: {
            suggests: '#ea-didyoumean-template',
            categories: '#ea-category-template',
            cms: '#ea-cms-template',
            products: '#ea-products-template',
            allResults: '#ea-all-results-template',
            mfpost:  '#ea-mfpost-template'
        },
        contentSelectors: {
            suggests: '#ea-suggests',
            categories: '#ea-categories',
            cms: '#ea-cms',
            products: '#ea-products',
            others: '#ea-others',
            allResults: '#ea-all-results',
            mfpost:  '#ea-mfpost'
        },
        /**
         * Containers phtml references (only for template hints)
         */
        contentTemplateFiles: {
            suggests: "view/frontend/templates/autocomplete/suggests.phtml",
            categories: "view/frontend/templates/autocomplete/categories.phtml",
            cms: "view/frontend/templates/autocomplete/cms.phtml",
            products: "view/frontend/templates/autocomplete/products.phtml",
            allResults: "view/frontend/templates/autocomplete/all-results.phtml",
            mfpost: "view/frontend/templates/post/post-autocomplete.phtml"
        },

        isIOS: function () {
            return navigator.userAgent.match(/iPhone|iPod/i);
        },

        _create: function () {
            this.initSearchFrom();
        },

        getCustomerGroupId: function () {
            let data = $.parseJSON(localStorage.getItem('mage-cache-storage'));
            if (!_.isEmpty(data) && typeof data.customer != "undefined" && typeof data.customer.cgi != "undefined") {
                return data.customer.cgi;
            } else {
                return 0;
            }
        },

        initSearchFrom: function () {
            this.responseList = {
                indexList: null,
                currentIndex: null
            };
            this.autoComplete = $(this.options.destinationSelector);
            this.overlay = $(".ea-overlay");
            this.searchForm = $(this.options.formSelector);
            this.submitBtn = this.searchForm.find(this.options.submitBtn)[0];
            this.searchLabel = $(this.options.searchLabel);

            _.bindAll(this, '_onKeyDown', '_onPropertyChange');

            this.submitBtn.disabled = true;

            this.element.attr('autocomplete', this.options.autocomplete);


            if (!this.observersInitialized) {

                this.element.on('blur', $.proxy(function () {
                    if (this.doNotBlur) {
                        return;
                    }
                    if (this.searchLabel.hasClass('active')) {
                        setTimeout($.proxy(function () {
                            this.searchLabel.removeClass('active');
                            this._updateAriaHasPopup(false);
                        }, this), 250);
                    }
                }, this));

                this.element.trigger('blur');

                this.element.on('focus', $.proxy(function () {
                    this.searchLabel.addClass('active');
                }, this));
                this.element.on('keydown', this._onKeyDown);
                this.element.on('input propertychange', this._onPropertyChange);

                this.searchForm.on('submit', $.proxy(function () {
                    // if (/*'true' === this.element.attr('aria-haspopup') && */null !== this.responseList.currentIndex) {
                    //     return false;
                    // }
                    this._updateAriaHasPopupForceHide();
                }, this));

                $(document).on('touchstart', function (evt) {
                    var target = $(evt.target);
                    if (!target.parents('#ea_search_autocomplete').length) {
                        this._updateAriaHasPopupForceHide();
                    }
                }.bind(this));

                this.initObservers();
                this.observersInitialized = true;
            }
            $(document).trigger('ela-autocomplete-loaded');
            this.element.addClass('loaded');

        },

        /**
         * Initialize the debug popup observers
         */
        initDebugObservers: function () {
            $(document).on('click', '#ea-debug-enable-template-hints', function () {
                this.toggleTemplateHints(true);
            }.bind(this));
            $(document).on('click', '#ea-debug-disable-template-hints', function () {
                this.toggleTemplateHints(false);
            }.bind(this));
            $(document).on('click', '#ea-debug-show-last-request-data', function () {
                requireDep = ['jquery', 'jquery-ui-modules/dialog', "elasticsearchcore_jsonview"];
                if (!compareVersions(MAGE_VERSION, "2.3.4")) {
                    requireDep = ['jquery', 'jquery/ui', "elasticsearchcore_jsonview"];
                }
                require(requireDep, function ($) {
                    $("#ea-debug-show-last-request-data-modal").modal({
                        "type": "slide",
                        "title": "Last Request Data",
                        "modalClass": "mage-new-category-dialog form-inline",
                        buttons: []
                    });
                    $("#ea-debug-show-last-request-data-modal").html("");
                    $("#ea-debug-show-last-request-data-modal").JSONView(JSON.stringify(this.lastRequestData));
                    $("#ea-debug-show-last-request-data-modal").modal("openModal");
                }.bind(this));
            }.bind(this));
        },

        doNotBlur: false,

        /**
         * Add observers when the window is resized
         */
        initWindowResizeObservers: function () {
            $(window).resize(function () {
                this.doNotBlur = true;
                if (this.element.attr('aria-haspopup') == 'true') {
                    this._onPropertyChange();
                }
            }.bind(this));

        },

        initAutocompleteDisabledObservers: function () {

            $(document).on('mouseover', '#ea_search_autocomplete', function () {
                if ($("#ea_search_autocomplete").css("opacity") == 0) {
                    $("#ea_search_autocomplete").css({height: "0px"});
                }


            }.bind(this));
        },

        /**
         * Init the overlay observers (click on the overlay = hide the autocomplete)
         */
        initOverlayObservers: function () {
            $(document).on('click', "#ea-overlay", function () {
                this._updateAriaHasPopup(false);
            }.bind(this));
        },

        initObservers: function () {
            this.initDebugObservers();
            this.initAutocompleteDisabledObservers();
            this.initWindowResizeObservers();
            this.initOverlayObservers();

            $(document).on('wyo-search-term-updated-finish', function () {
                this.element.removeClass('in-progress');
            }.bind(this));

        },


        /**
         * @private
         * @return {Element} The current element in the suggestion list.
         */
        _current: function () {
            if (this.responseList.currentIndex == -1) {
                return null;
            } else {
                return this.responseList.indexList
                    ? $(this.responseList.indexList[this.responseList.currentIndex])
                    : null;
            }
        },
        /**
         * @private
         */
        _updateCurrent: function () {
            var selectClass = this.options.selectClass;
            this.responseList.indexList.removeClass(selectClass);
            var current = this._current();
            if (current) {
                current.addClass(selectClass);


            }
        },
        /**
         * @private
         */
        _previous: function () {
            if (this.responseList.indexList) {
                var currentIndex = this.responseList.currentIndex;
                var listLength = this.responseList.indexList.length;
                if (--currentIndex < 0) {
                    currentIndex = listLength - 1;
                }
                this.responseList.currentIndex = currentIndex;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _next: function () {
            if (this.responseList.indexList) {
                var currentIndex = this.responseList.currentIndex;
                var listLength = this.responseList.indexList.length;
                if (null === currentIndex || ++currentIndex >= listLength) {
                    currentIndex = 0;
                }
                this.responseList.currentIndex = currentIndex;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _first: function () {
            if (this.responseList.indexList) {
                this.responseList.currentIndex = 0;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _last: function () {
            if (this.responseList.indexList) {
                var listLength = this.responseList.indexList.length;
                this.responseList.currentIndex = listLength - 1;
                this._updateCurrent();
            }
        },
        /**
         * @private
         * @param {Boolean} show Set attribute aria-haspopup to "true/false" for element.
         */
        _updateAriaHasPopup: function (show, noResult) {
            if (show) {
                this.element.attr('aria-haspopup', 'true');
                if (typeof noResult != "undefined" && noResult === true) {
                    $("body").removeClass("ea-autocomplete").addClass("ea-no-result");
                }
                else {
                    $("body").removeClass("ea-no-result").addClass("ea-autocomplete");
                }
            } else {
                if (this.isIOS()) {
                    this.searchForm.toggleClass('active', true);
                    this.searchLabel.toggleClass('active', true);
                    this.element.attr('aria-expanded', true);
                    return;
                }
                this.element.attr('aria-haspopup', 'false');
                $("body").removeClass("ea-autocomplete").removeClass("ea-no-result");


            }
        },
        _updateAriaHasPopupForceHide() {
            this.element.attr('aria-haspopup', 'false');
            $("body").removeClass("ea-autocomplete").removeClass("ea-no-result");
        },

        /**
         * Clears the item selected from the suggestion list and resets the suggestion list.
         * @private
         */
        _resetResponseList: function () {
            this.responseList.indexList = null;
            this.responseList.currentIndex = null;
        },
        /**
         * Executes when keys are pressed in the search input field. Performs specific actions
         * depending on which keys are pressed.
         * @private
         * @param {Event} e - The key down event
         * @return {Boolean} Default return type for any unhandled keys
         */
        _onKeyDown: function (e) {
            var keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case $.ui.keyCode.HOME:
                    this._first();
                    break;
                case $.ui.keyCode.END:
                    this._last();
                    break;
                case $.ui.keyCode.ESCAPE:
                    break;
                case $.ui.keyCode.ENTER:
                    if (this.options.config.product.redirect != 0) {
                        let items = this.searchForm.find('.ea-product')
                        if (items.length === 1) {
                            let link = items.find('a');
                            location.href = link[0].href;
                            return false;
                        }
                    }
                    var current = this._current();
                    if (current) {
                        var links = current.find('a');
                        if (links.length) {
                            location.href = links[0].href;
                            this.autoComplete.removeClass();
                        }
                    } else {
                        $("body").removeClass('ea-autocomplete');
                        this.searchForm.find('input').attr('aria-haspopup', 'false');
                        this.searchForm.trigger('submit');
                        return true;
                    }
                    break;
                case $.ui.keyCode.DOWN:
                    this._next();
                    break;
                case $.ui.keyCode.UP:
                    this._previous();
                    break;
                default:
                    return true;
            }
        },
        /**
         * Executes when the value of the search input field changes. Executes a GET request
         * to populate a suggestion list based on entered text. Handles click (select), hover,
         * and mouseout events on the populated suggestion list dropdown.
         * @private
         */
        _onPropertyChange: function () {

            this.element.addClass('in-progress');

            var fullStartTime = Date.now();

            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    // Removed to fix display issues
                    // left: searchField.offset().left,
                    // top: searchField.offset().top + searchField.outerHeight(),
                    width: searchField.outerWidth()
                },
                value = this.element.val();

            this.submitBtn.disabled = ((value.length === 0) || (value == null) || /^\s+$/.test(value));

            // if Elasticsearch Layered Navigation auto refresh in search page is enabled => no autocomplete
            if ($('body').hasClass('catalogsearch-result-index') && $('body').hasClass('eln-auto-refresh')) {
                $(document).trigger('wyo-search-term-updated', value);
                return;
            }

            if (value.length >= parseInt(this.options.minSearchLength, 10)) {


                if (this.xhr != undefined) {
                    this.xhr.abort();
                }
                var data = {
                    searchTerm: value,
                    store: this.options.storeCode,
                    eaConfig: this.options.config,
                    ea: true,
                    customerGroupId: this.getCustomerGroupId(),
                    debug: this.options.debug
                };

                var hash = this.hash(JSON.stringify(data));
                var requestStartTime = Date.now();

                if (typeof this.cache[hash] !== "undefined") {
                    this.updateContent(value, this.cache[hash], fullStartTime, requestStartTime, hash);
                    this.element.removeClass('in-progress');
                } else {
                    this.xhr = $.ajax({
                        url: this.options.url,
                        method: "POST",
                        global: false,
                        data: data,
                        dataType: "json"
                    }).done(function (data) {
                        var self = this;

                        $.ajax({
                            url: "blog/search/autocomplete",
                            method: "POST",
                            global: false,
                            data: data,
                            dataType: "json"
                        }).done(function (postData) {
                            data['mfpost'] = postData;
                            if (value === self.element.val()) {
                                self.updateContent(value, data, fullStartTime, requestStartTime);
                                self.cache[hash] = data;
                                self.element.removeClass('in-progress');
                            }
                        });
                    }.bind(this));

                }
            } else {

                this._updateAriaHasPopup(false);
                this._updateAriaHasPopupForceHide();
                this.element.removeClass('in-progress');
                // this._resetResponseList();
            }
        },

        updateContent: function (value, data, fullStartTime, requestStartTime, hash) {

            var requestTime = Date.now() - requestStartTime;
            var renderingStartTime = Date.now();
            if (value == this.element.val()) {
                if (typeof data.suggest == "undefined") {
                    data.suggest = {"count": 0, "docs": {}, "time": 0};
                }
                if (typeof data.product == "undefined") {
                    data.product = {"count": 0, "docs": {}, "time": 0};
                }
                if (typeof data.category == "undefined") {
                    data.category = {"count": 0, "docs": {}, "time": 0};
                }
                if (typeof data.cms == "undefined") {
                    data.cms = {"count": 0, "docs": {}, "time": 0};
                }
                if (typeof data.mfpost == "undefined") {
                    data.mfpost = {"count": 0, "docs": {}, "time": 0};
                }
                if (typeof data.mfpost == "undefined") {
                    data.mfpost = {"count": 0, "docs": {}, "time": 0};
                }
                var globalCount = data.product.count + data.suggest.count + data.category.count + data.cms.count + data.mfpost.count;
                var phpTime = data.product.time + data.suggest.time + data.category.time + data.cms.time;
                if (typeof hash != "undefined") {
                    phpTime = 0;
                }

                if (this.options.config.general.update_search_term == 1) {
                    $.ajax({
                        url: this.options.urlUpdateSearchTerm,
                        method: "POST",
                        global: false,
                        data: {"term": value, "num_results": data.product.count},
                        dataType: "json"
                    }).done(function (data) {
                    }.bind(this));
                }


                // no result
                if (globalCount == 0) {
                    var padding = parseInt($(this.options.formSelector + ' ' + "#ea-search-autocomplete-no-result").css("padding-left").replace("px", "")) + parseInt($(this.options.formSelector + ' ' + "#ea-search-autocomplete-no-result").css("padding-right").replace("px", ""));
                    $(this.options.formSelector + ' ' + "#ea-search-autocomplete-no-result").css({
                        width: ($(this.element).innerWidth() - padding) + 'px'
                    });

                    this._updateAriaHasPopup(true, true);
                } else {

                    this._updateAriaHasPopup(true, false);

                    var htmlSuggests = "";
                    if ($(this.templateSelectors.suggests).length > 0) {
                        htmlSuggests = mageTemplate(this.templateSelectors.suggests, {
                            suggests: data.suggest.docs,
                            count: data.suggest.count,
                            enabled: this.options.config.didyoumean.enable_autocomplete != "0",
                            title: this.options.config.general.labels.didyoumean
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.suggests).html(htmlSuggests);
                    }

                    var htmlCategories = "";
                    if ($(this.templateSelectors.categories).length > 0) {
                        htmlCategories = mageTemplate(this.templateSelectors.categories, {
                            categories: data.category.docs,
                            count: data.category.count,
                            enabled: this.options.config.category.enable_autocomplete != "0",
                            title: this.options.config.general.labels.categories,
                            displayEmpty: this.options.config.category.display_empty_autocomplete !== "0"
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.categories).html(htmlCategories);
                    }

                    var htmlCms = "";
                    if ($(this.templateSelectors.cms).length > 0) {
                        htmlCms = mageTemplate(this.templateSelectors.cms, {
                            cms: data.cms.docs,
                            count: data.cms.count,
                            enabled: this.options.config.cms.enable_autocomplete != "0",
                            title: this.options.config.general.labels.cms,
                            displayEmpty: this.options.config.cms.display_empty_autocomplete !== "0"
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.cms).html(htmlCms);
                    }
                    _.each(this.options.config, function (info, key) {
                        if (key != "cms" && key != "product" && key != "category" && key != "didyoumean" && key != "mfpost"
                            && typeof this.options.config[key].enable_autocomplete !== "undefined") {
                            if ($('#ea-' + key + '-template').length) {
                                let html = mageTemplate('#ea-' + key + '-template', {
                                    docs: data[key].docs,
                                    count: data[key].count,
                                    enabled: this.options.config[key].enable_autocomplete != "0",
                                    title: this.options.config.general.labels[key],
                                    displayEmpty: this.options.config[key].display_empty_autocomplete !== "0"
                                });
                                $(this.options.formSelector + ' ' + this.contentSelectors.others).html(html);
                            }
                        }
                    }.bind(this));

                    var htmlProducts = "";
                    if ($(this.templateSelectors.products).length > 0) {
                        htmlProducts = mageTemplate(this.templateSelectors.products, {
                            products: data.product.docs,
                            count: data.product.count,
                            enabled: this.options.config.product.enable_autocomplete != "0",
                            title: this.options.config.general.labels.products,
                            customerGroupId: this.getCustomerGroupId()
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.products).html(htmlProducts);
                    }

                    var htmlAllResults = "";
                    if ($(this.templateSelectors.allResults).length > 0) {
                        htmlAllResults = mageTemplate(this.templateSelectors.allResults, {
                            enabled: this.options.config.general.enable_all_results != "0",
                            title: this.options.config.general.labels.all_results,
                            href: this.options.searchUrl + "?q=" + value,
                            term: value
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.allResults).html(htmlAllResults);
                    }

                    var htmlMfPosts = "";
                    if ($(this.templateSelectors.mfpost).length > 0) {
                        htmlMfPosts = mageTemplate(this.templateSelectors.mfpost, {
                            mfpost: data.mfpost.docs,
                            count: data.mfpost.count,
                            enabled: "1",
                            title: "Posts",
                            href: this.options.searchUrl + "?q=" + value,
                            term: value
                        });
                        $(this.options.formSelector + ' ' + this.contentSelectors.mfpost).html(htmlMfPosts);
                    }


                    if (data.product.count != 0) {
                        $(this.options.formSelector + ' ' + '.ea-search-autocomplete').removeClass('no-product');
                    }

                    if (htmlSuggests == "" && htmlCategories == "" && htmlCms == "") {
                        $(this.options.formSelector + ' ' + '.ea-search-autocomplete .left').addClass('hidden');
                    } else {
                        $(this.options.formSelector + ' ' + '.ea-search-autocomplete .left').removeClass('hidden');
                        if (data.product.count == 0) {
                            $(this.options.formSelector + ' ' + '.ea-search-autocomplete').addClass('no-product');
                        }
                    }


                }

                this.responseList.indexList = $('.ea-product');
                this.responseList.currentIndex = -1;

                if ($("#ea-debug-template").length >= 1) {
                    var fullEndTime = Date.now();
                    var debug = mageTemplate("#ea-debug-template", {
                        "phpTime": phpTime,
                        "requestTime": requestTime,
                        "renderingTime": (fullEndTime - renderingStartTime),
                        "totalTime": (fullEndTime - fullStartTime),
                        "fromCache": typeof hash !== "undefined",
                        "cacheHash": hash,
                        "templateHintsEnabled": this.templateHintsEnabled
                    });
                    this.lastRequestData = data;
                    $('#ea-debug').html(debug);
                    this.toggleTemplateHints(this.templateHintsEnabled);
                }


            }

        },

        //######################################################################
        // GENERAL TOOLS
        //######################################################################
        /**
         * Get a unique hash of a string
         * @param string
         * @returns {number}
         */
        hash: function (string) {
            var hash = 0;
            if (string.length === 0) {
                return hash;
            }
            for (var i = 0; i < string.length; i++) {
                var char = string.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        },

        //######################################################################
        // DEBUG TOOLS
        //######################################################################

        /**
         * Enable the template hints
         * @param on true => enable, false => disable
         */
        toggleTemplateHints: function (on) {
            // add a hint for all Underscore.js template
            _.each(this.templateSelectors, function (elt, id) {
                // remove all hints
                $(elt + "-th").remove();
                if (on) { // enabling
                    var div = $("<div>").addClass("ea-template-hint").addClass("template-hint").attr('id', elt.replace("#", "") + "-th");
                    div.html("<a name='" + id.toUpperCase() + "'>" + id.toUpperCase() + "</a><br/>elt: " + elt + "<br/>file: " + this.contentTemplateFiles[id]);
                    // the template dom elt is a script node => adding the hint before the node
                    div.insertBefore($(elt));
                }
            }.bind(this));

            // add a hint for all blocks updated using uUnderscore.js template
            _.each(this.contentSelectors, function (elt, id) {
                // remove all hints
                elt = this.options.formSelector + ' ' + elt;
                $(elt + " > .ea-template-hint").remove();
                if (on) { // enabling template hints
                    this.templateHintsEnabled = true;
                    $('#ea-debug-disable-template-hints').show();
                    $('#ea-debug-enable-template-hints').hide();
                    var div = $("<div>").addClass("ea-template-hint");
                    if (typeof this.templateSelectors[id] != "undefined") {
                        // an underscore.js template exists => add an anchor link to the hint of the underscore.js template
                        div.html(id.toUpperCase() + "<br/>elt: " + elt + "<br/>_tpl: <a onClick='jQuery(\".template-hint.selected\").removeClass(\"selected\");jQuery(\"" + this.templateSelectors[id] + "-th\").addClass(\"selected\")' href='#" + id.toUpperCase() + "'>" + this.templateSelectors[id] + "</a>");
                    } else {
                        div.html(id.toUpperCase() + "<br/>elt: " + elt + "<br/>phtml: " + this.contentTemplateFiles[id]);
                    }
                    $(elt).prepend(div);
                    $(elt).addClass("ea-template-hint-container");
                } else { // disabling templateh hints
                    this.templateHintsEnabled = false;
                    // remove hint class to the container
                    $(elt).removeClass("ea-template-hint-container");
                    $('#ea-debug-disable-template-hints').hide();
                    $('#ea-debug-enable-template-hints').show();
                }
            }.bind(this));
        }

    });

    return $.mage.wyoea;
});
