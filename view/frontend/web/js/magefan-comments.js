/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

define([
    'jquery',
    'uiComponent',
    'mage/validation'],
    function ($, Component, validation) {
    'use strict';


    return Component.extend({
        initialize: function($options) {

            var msgLifetime = 6000;
            var $hd = $('#magefan-comment-block');

            var getMessageHtml = function(msg, type) {
                var h = '<div class="message-' + type + ' ' + type + ' message">'
                    + '<div>' + msg + '</div>'
                +'</div>';
                return $(h);
            }

            var processError = function($form, msg) {
                $form.find('[type=submit]').removeAttr('disabled');
                var $h = getMessageHtml(msg, 'error');
                $h.insertBefore($form);
                setTimeout(function(){
                    $h.remove();
                }, msgLifetime);
            }

            var processSuccess = function($form, msg) {
                $form.find('[type=submit]').removeAttr('disabled');
                var $h = getMessageHtml(msg, 'success');
                $h.insertBefore($form);
                $form.hide();
                setTimeout(function(){
                    $h.remove();
                }, msgLifetime);
            }

            $hd.find('form').submit(function(){
                var $form = $(this);
                if ($form.validation() && $form.validation('isValid')) {
                    $form.find('[type=submit]').attr('disabled', 'disabled');
                    $.ajax({
                        'method': 'post',
                        'url': $form.attr('action'),
                        'dataType': 'json',
                        'data': $form.serialize(),
                        'success': function(res) {
                            if (res.success) {
                                processSuccess($form, res.message);
                            } else {
                                processError($form, res.message);
                            }
                        },
                        'error': function() {
                            processError($form, 'Unexpected error. Please try again later or contact us.')
                        }
                    })
                }
                return false;
            });

            return this;
        },

    });
});