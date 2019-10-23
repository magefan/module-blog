<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Color Picker Block
 */
class ColorPicker extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script>
            require(["jquery", "jquery/colorpicker/js/colorpicker", "domReady!"], function ($) {
                var el = $("#'.$element->getHtmlId().'");
                
                el.css("background-color", "#'.$value.'");
                el.ColorPicker({
                    layout: "hex",
                    submit: 0,
                    colorScheme: "dark",
                    color: "#'.$value.'",
                    onChange: function (hsb, hex, rgb) {
                        el.css("background-color", "#"+hex);
                    }
                }).keyup(function() {
                    $(this).ColorPickerSetColor(this.value);
                });
            });
            </script>';

        return $html;
    }
}
