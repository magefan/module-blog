<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ColorPicker
 * @package Magefan\Blog\Block
 */
class ColorPicker extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script>
            require(["jquery"], function ($) {
                $(document).ready(function (e) {
                    $("#'.$element->getHtmlId().'").css("background-color","#'.$value.'");
                    $("#'.$element->getHtmlId().'").colpick({
                        layout:"hex",
                        submit:0,
                        colorScheme:"dark",
                        color: "#'.$value.'",
                        onChange:function(hsb,hex,rgb,el,bySetColor) {
                        $(el).css("background-color","#"+hex);
                        if(!bySetColor) $(el).val(hex);
                    }
                    }).keyup(function(){
                        $(this).colpickSetColor(this.value);
                    });
                });
            });
            </script>';

        return $html;
    }
}