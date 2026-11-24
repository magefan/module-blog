<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar widget trait
 */
trait Widget
{
    /**
     * Retrieve block sort order
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        if (!$this->hasData('sort_order')) {
            $this->setData('sort_order', $this->getConfigValue('sort_order'));
        }
        return (int)$this->getData('sort_order');
    }

    /**
     * Retrieve block html, injecting mfblog-sticky class into the wrapper div when sticky is enabled.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getConfigValue('enabled')) {
            return '';
        }
        $html = parent::_toHtml();

        if ($html && $this->getConfigValue('sticky')) {
            $html = preg_replace('/(<div\b[^>]*\bclass=")/', '$1mfblog-sticky ', $html, 1);
        }

        return $html;
    }

    /**
     * Retrieve config value
     *
     * @param string $param
     * @return mixed
     */
    protected function getConfigValue(string $param)
    {
        return $this->_scopeConfig->getValue(
            'mfblog/sidebar/' . $this->getWidgetKey() . '/' . $param,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve widget key
     *
     * @return string
     */
    public function getWidgetKey(): string
    {
        return $this->_widgetKey ?: '';
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $result = [
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            //$this->getTemplateFile(), don't use template, valor overide templates
            'base_url' => $this->getBaseUrl(),
            //'template' => $this->getTemplate() , don't use template, valor overide templates
            $this->getNameInLayout() //add name in layout
        ];

        /* Add Customer Group */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $result['customer_group_id'] = $objectManager->get(\Magento\Customer\Model\Session::class)
            ->getCustomerGroupId();
        return $result;
    }
}
