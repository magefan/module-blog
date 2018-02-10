/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

/*jshint jquery:true*/
define([
    'jquery',
    'underscore',
    'mage/template',
    'uiRegistry',
    'productGallery',
    'jquery/ui',
    'baseImage'
], function ($, _, mageTemplate, registry, productGallery) {
    'use strict';

    $.widget('mage.productGallery', $.mage.productGallery, {
        _showDialog: function (imageData) {}
    });

    return $.mage.productGallery;
});
