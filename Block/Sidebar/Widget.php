<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar widget trait
 */
trait Widget
{
    /**
     * Retrieve block sort order
     * @return int
     */
    public function getSortOrder()
    {
        if (!$this->hasData('sort_order')) {
            $this->setData('sort_order', $this->_scopeConfig->getValue(
                'mfblog/sidebar/'.$this->_widgetKey.'/sort_order',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ));
        }
        return (int) $this->getData('sort_order');
    }

    /**
     * Retrieve block html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_scopeConfig->getValue(
            'mfblog/sidebar/'.$this->_widgetKey.'/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Retrieve widget key
     *
     * @return string
     */
    public function getWidgetKey()
    {
        return $this->_widgetKey;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $result = parent::getCacheKeyInfo();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $result['customer_group_id'] = $objectManager->get(\Magento\Customer\Model\Session::class)->getCustomerGroupId();
        return $result;
    }
}
