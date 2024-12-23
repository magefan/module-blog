<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

class SitemapInfo extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return info block html
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $m = $this->moduleList->getOne($this->getModuleName());
        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            Sitemap XML included in all editions of the blog extension. Sitemap items priority and frequency can be configured only in the <b>Blog Plus</b> and <b>Blog Extra</b> edition.
        </div>';

        return $html;
    }
}
