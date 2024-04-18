<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

abstract class InfoPlan extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * @return string
     */
    abstract protected function getMinPlan(): string;

    /**
     * @return string
     */
    abstract protected function getSectionId(): string;

    /**
     * @return string
     */
    abstract protected function getText(): string;


    /**
     * Return info block html
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleVersion->execute($this->getModuleName() . $this->getMinPlan())) {
            return '';
        }

        $html = '';

        $html .= '<script>
                    require(["jquery", "Magento_Ui/js/modal/alert", "domReady!"], function($, alert){
                        setInterval(function(){
                            var json = ' . $this->getSectionId() . ';
                            json.forEach(function(element) {
                                var $section = $("#" + element + "-state").parent(".section-config");
                                var $formatDateField = $("#" + element);

                                function disableField($field) {
                                    $field.attr("readonly", "readonly");
                                    $field.removeAttr("disabled");
                                    if ($field.data("mbdisabled")) return;
                                    $field.data("mbdisabled", 1);
                                    $field.click(function(){
                                        alert({
                                            title: "You can not change this option.",
                                            content: "' . ($this->getMinPlan() == "Extra" ? 'This option is available in <strong>Extra</strong> plan only.' : 'This option is available in <strong>Plus or Extra</strong> plans only.') . '",
                                            buttons: [{
                                                text: "Upgrade Plan Now",
                                                class: "action primary accept",
                                                click: function () {
                                                    window.open("https://magefan.com/magento2-blog-extension/pricing?utm_source=gtm_config&utm_medium=link&utm_campaign=regular");
                                                }
                                            }]
                                        });
                                    });
                                }

                                if ($section.length) {
                                    $section.find(".use-default").css("visibility", "hidden");
                                    $section.find("input,select").each(function(){
                                        disableField($(this));
                                    });
                                    if ($section.data("mbdisabled")) return;
                                    $section.data("mbdisabled", 1);
                                    var customHtml = \'<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">' . $this->getText() . ' <a style="color: #ef672f; text-decoration: underline;" href="https://magefan.com/magento2-blog-extension/pricing?utm_source=gtm_config&utm_medium=link&utm_campaign=regular" target="_blank">Read more</a>.</div>\';
                                    console.log($section.find([id$="state"]).parent(".section-config"));
                                    $($section.find("fieldset")).prepend(customHtml);

                                } else {
                                    $("#row_" + element).find(".use-default").css("visibility", "hidden");
                                    disableField($formatDateField);
                                }
                            });

                        }, 1000);
                    });
                </script>';

        return $html;
    }
}
