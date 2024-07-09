<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Class Color Picker Block
 */
class ColorPicker extends Field
{

    /**
     * @var SecureHtmlRenderer|null
     */
    private $secureRenderer;

    /**
     * ColorPicker constructor.
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(Context $context, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $script = '';
        $html = $element->getElementHtml();
        $value = $this->escapeHtml($element->getData('value'));

        $script .= "
            require(['jquery', 'jquery/colorpicker/js/colorpicker', 'domReady!'], function ($) {
                var el = $('#" . $element->getHtmlId() . "');

                el.css('background-color', '#" . $value . "');
                el.ColorPicker({
                    layout: 'hex',
                    onChange: function (hsb, hex, rgb) {
                        el.css('background-color', '#'+hex);
                        el.val(hex);
                    }
                }).keyup(function() {
                    var value = el.val();
                    $(this).ColorPickerSetColor(value);
                    el.css('background-color', '#' + value);
                });
            });";

        $html .= $this->secureRenderer->renderTag('script', ['type' => 'text/x-magento-init'], $script, false);

        return $html;
    }
}
