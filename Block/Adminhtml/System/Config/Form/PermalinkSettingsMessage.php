<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

class PermalinkSettingsMessage extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return info block html
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $html = '<div id="advencedNotification" style="display:none;padding:10px;background-color:#fffbbb;border:1px solid #ddd;margin-bottom:7px;">
            ' . __('Blog Plus <b>Advanced Permalink Settings</b> are enabled.') . '
        </div>';

        $script = "
            require(['jquery', 'domReady!'], function($) {
                    var e = $('#mfblog_advanced_permalink_enabled');
                    e.change(function() {
                        var advencedSettings = $(this).val();

                        if (1 == advencedSettings) {
                            advencedNotification
                            $('#mfblog_permalink').find('input,select').prop('disabled', true);
                            $('#advencedNotification').show();
                        } else {
                            $('#mfblog_permalink').find('.use-default input').each(function() {
                                $(this).prop('disabled', false);
                                if (!$(this).is(':checked')) {
                                    $(this).parents('tr').find('.value').children('input,select').prop('disabled', false);
                                }
                            })
                            $('#advencedNotification').hide();
                        }
                    });
                    setTimeout(function(){
                           e.change();
                    }, 2000);
                 });
        ";

        $script = $this->mfSecureRenderer->renderTag('script', [], $script, false);

        $html .= $script;

        return $html;
    }
}
