<?php

namespace Magefan\Blog\Block\Social;

use Magento\Store\Model\ScopeInterface;

class AddThis extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve AddThis status
     *
     * @return boolean
     */
    public function getAddThisEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/social/add_this_enabled', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve AddThis publisher id
     *
     * @return boolean
     */
    public function getAddThisPubId()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/social/add_this_pubid', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve AddThis language code
     *
     * @return boolean
     */
    public function getAddThisLanguage()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/social/add_this_language', ScopeInterface::SCOPE_STORE
        );
    }

    public function toHtml()
    {
        if (!$this->getAddThisEnabled() || !$this->getAddThisPubId()) {
            return '';
        }

        return parent::toHtml();
    }
}
