/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (AbstractElement) {
    'use strict';

    return AbstractElement.extend({
        defaults: {
            elementTmpl: 'Magefan_Blog/form/element/link'
        },

        initialize: function () {
            this._super();

            var value = this.value();
            this.url = value.url;
            this.title = value.title;
            this.text = value.text;
        },

    });
});