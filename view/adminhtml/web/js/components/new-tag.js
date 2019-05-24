/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/ui-select'
], function (_, Select) {
    'use strict';

    function flatten(a, s, cr)
    {
        var i = 0, c;
        a = _.compact(a);
        cr = cr || [];
        for (i; i < a.length; i++) {
            cr.push(a[i]);
            if (a[i].hasOwnProperty(s)) {
                c = a[i][s];
                delete a[i][s];
                flatten.call(this, c, s, cr);
            }
        }
        return cr;
    }

    return Select.extend({

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data),
                copyOptionsTree
            if (data.error) {
                return this;
            }

            this.options([]);
            if (!option.parent) {
                this.cacheOptions.tree.push(option);
                copyOptionsTree = JSON.parse(JSON.stringify(this.cacheOptions.tree));
                this.cacheOptions.plain = flatten(copyOptionsTree, this.separator);
                this.options(this.cacheOptions.tree);
            } else {
                this.setOption(option);
            }
            this.set('newOption', option);
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                'is_active': "1",
                level: 0,
                value: data.model['tag_id'],
                label: data.model['title'],
                parent: 0
            };
        }
    });
});
