/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            imports: {
                identifier: '${ $.provider }:data.identifier'
            },
            listens: {
                identifier: 'handleChanges'
            },
        },

        /**
         * Disable checkbox field, when 'identifier' field without changes or 'use default' field is checked
         */
        handleChanges: function (newValue) {
            if (!this.initValue) {
                this.initValue = newValue;
                this.disabled(1);
            } else {
                var disabled = (newValue === this.initValue);
                this.disabled(disabled);
                if (disabled) {
                    this.checked(false);
                    this.value("0");
                } /* else {
                    this.checked(1);
                    this.value("1");
                } */
            }
        },
    });
});
