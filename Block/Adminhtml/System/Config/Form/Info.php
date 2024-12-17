<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

/**
 * Admin blog configurations information block
 */
class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl()
    {
        return 'https://mage' . 'fan.com/magento2-blog-extension';
    }

    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle()
    {
        return 'Blog Extension';
    }

    /**
     * Return extension image
     * @return string
     */
    protected function getModuleImage() {
        return 'http://mage' . 'fan.com/media/catalog/product/i/c/icon-blog-ext_1.jpg';
    }

    /**
     * Return extension Key
     * @return string
     */
    protected function getModuleKey() {
        return 'Blog';
    }

    /**
     * Return extension Max Plan
     * @return string
     */
    protected function getModuleMaxPlan() {
        return 'Extra';
    }
}
