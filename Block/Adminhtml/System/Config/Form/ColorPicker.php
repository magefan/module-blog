<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magefan\Community\Api\SecureHtmlRendererInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Color Picker Block
 */
class ColorPicker extends Field
{
    /**
     * @var SecureHtmlRendererInterface
     */
    private $mfSecureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param SecureHtmlRendererInterface $mfSecureRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        SecureHtmlRendererInterface $mfSecureRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mfSecureRenderer = $mfSecureRenderer;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $this->escapeHtml($element->getData('value'));

        $script = '
            require(["jquery", "jquery/colorpicker/js/colorpicker", "domReady!"], function ($) {
                var el = $("#' . $element->getHtmlId() . '");
                
                el.css("background-color", "#' . $value . '");
                el.ColorPicker({
                    layout: "hex",
                    onChange: function (hsb, hex, rgb) {
                        el.css("background-color", "#"+hex);
                        el.val(hex);
                    }
                }).keyup(function() {
                    var value = el.val();
                    $(this).ColorPickerSetColor(value);
                    el.css("background-color", "#" + value);
                });
            });
            ';
        $html .= $this->mfSecureRenderer->renderTag('script', [], $script, false);

        return $html;
    }
}
