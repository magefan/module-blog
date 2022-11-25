<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\Widget\Featured\Grid;

class Chooser extends \Magento\Widget\Block\Adminhtml\Widget\Chooser
{
    /**
     * @param  string $chooserId
     * @return string
     */
    public function onClickJs(string $chooserId) : string
    {
        $buttonHtml = "<button id='addBtn' class='action-primary' ><span>Use Selected Posts</span></button>";
        $js = '
                var waitForElm = function(selector) {
                    return new Promise(resolve => {
                        if (document.querySelector(selector)) {
                            return resolve(document.querySelector(selector));
                        }
                        
                        const observer = new MutationObserver(mutations => {
                            if (document.querySelector(selector)) {
                                resolve(document.querySelector(selector));
                                observer.disconnect();
                            }
                        });
                        
                        observer.observe(document.body, {
                            childList: true,
                            subtree: true
                        });
                    });
                };
                 
                require([
                 "jquery"
                  ],function($) { 
                     ' . $chooserId . '.choose();
                     window.needToReload = true;
                     
                     waitForElm("#'. $chooserId . '_table").then((elm) => {
                         $(".modal-header:last .modal-title").replaceWith("'.$buttonHtml . '");
                         $(".modal-header:last").css({"background": "#f8f8f8", "border-bottom": "1px solid #e3e3e3","border-top": "1px solid #e3e3e3",
                         "margin-bottom": "20px"});
                         var currentState = $("#' . $chooserId . 'label").html().replace("","");
                         var currentStateArray = []; 
    
                         if (currentState !== "") {
                            currentStateArray = $("#' . $chooserId . 'label").html().split(","); 
                         }
                        
                         window.postState = currentStateArray;
                         window.realState = currentStateArray;
                         $("#addBtn").click(function() {
                            var postStateStr = "";
                          
                            if (window.postState.length) {
                                postStateStr = window.postState.join(",");
                            }
                            document.getElementById("'. $chooserId . '_input").value = postStateStr;
                           ' .
            $chooserId .
            '.setElementValue(postStateStr);
                           ' .
            $chooserId .
            '.setElementLabel(postStateStr);
                           ' .
            $chooserId . '.close();
                         });
                         
                        $("#' . $chooserId . '_table > tbody  > tr").each(function () {
                            var postId = $(this).children("td:nth-child(2)").text().replace(/\s/g,"");
                            var isChoosed = currentState.includes(postId);
                            if (isChoosed === true) {
                                $(this).children("td:first").children("label:first").children("input:first").prop("checked", true);
                            }
                        });
                     });
                    
                  }
                 );
        ';
        return $js;
    }

    /**
     * @return \Magento\Framework\DataObject|mixed|null
     */
    public function getConfig()
    {
        if ($this->_getData('config') instanceof \Magento\Framework\DataObject) {
            return $this->_getData('config');
        }

        $configArray = $this->_getData('config');
        $config = new \Magento\Framework\DataObject();
        $this->setConfig($config);
        if (!is_array($configArray)) {
            return $this->_getData('config');
        }

        // define chooser label
        if (isset($configArray['label'])) {
            $config->setData('label', __($configArray['label']));
        }

        // chooser control buttons
        $buttons = ['open' => __('Select Post IDs ...')];
        $config->setButtons($buttons);

        return $this->_getData('config');
    }

    /**
     * Return chooser HTML and init scripts
     *
     * @return string
     */
    protected function _toHtml() : string
    {
        $element = $this->getElement();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $element->getForm()->getElement($this->getFieldsetId());
        $chooserId = $this->getUniqId();
        $config = $this->getConfig();
        // add chooser element to fieldset
        $chooser = $fieldset->addField(
            'chooser' . $element->getId(),
            'note',
            ['label' => $config->getLabel() ? $config->getLabel() : '', 'value_class' => 'value2']
        );
        $hiddenHtml = '';
        if ($this->getHiddenEnabled()) {
            $hidden = $this->_elementFactory->create('hidden', ['data' => $element->getData()]);
            $hidden->setId("{$chooserId}value")->setForm($element->getForm());
            if ($element->getRequired()) {
                $hidden->addClass('required-entry');
            }
            $hiddenHtml = $hidden->getElementHtml();
            $element->setValue('');
        }

        $buttons = $config->getButtons();
        $chooseButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setType(
            'button'
        )->setId(
            $chooserId . 'control'
        )->setClass(
            'btn-chooser'
        )->setLabel(
            $buttons['open']
        )->setOnclick(
            $this->onClickJs($chooserId)
       /* )->setDisabled(
            $element->getReadonly()*/
        );
        $chooser->setData('after_element_html', $hiddenHtml . $chooseButton->toHtml());

        // render label and chooser scripts
        $configJson = $this->_jsonEncoder->encode($config->getData());

        return '
            <input id="'. $chooserId . '_input" class="widget-option input-text admin__control-text" 
            onkeyup="keyupFunctionMf()"
            value="' . ($this->getLabel() ? $this->escapeHtml($this->getLabel()) : '') .'" />
            <label class="widget-option-label" style="display: none" id="' .
            $chooserId .
            'label">' .
            ($this->getLabel() ? $this->escapeHtml($this->getLabel()) : '') .
            '</label>
            <div id="' .
            $chooserId .
            'advice-container" class="hidden"></div>' .
            '<script>' .
                '
                function keyupFunctionMf() {
                    var inputV = document.getElementById("' . $chooserId . '_input").value;
                    ' . $chooserId . '.setElementValue(inputV);
                    ' . $chooserId . '.setElementLabel(inputV);    
                }
                require(["prototype", "mage/adminhtml/wysiwyg/widget"], function(){
            //<![CDATA[
                (function() {
                    var instantiateChooser = function() {
                      
                       window.' .
                $chooserId .
                ' = new WysiwygWidget.chooser(
                            "' .
                $chooserId .
                '",
                            "' .
                $this->getSourceUrl() .
                '",
                            ' .
                $configJson .
                '
                        );
                        if ($("' .
                $chooserId .
                'value")) {
                            $("' .
                $chooserId .
                'value").advaiceContainer = "' .
                $chooserId .
                'advice-container";
                        }
                    }
                    
                    jQuery(instantiateChooser);   
                })();
            //]]>
            });
            ' . '</script>';
    }
}