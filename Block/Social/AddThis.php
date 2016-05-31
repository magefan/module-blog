<?php

namespace Magefan\Blog\Block\Social;

use Magento\Store\Model\ScopeInterface;

class AddThis extends \Magento\Framework\View\Element\Template
{
    public function getAddThisPubId()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/social/add_this_pubid', ScopeInterface::SCOPE_STORE
        );
    }

    public function getAddThisLanguage()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/social/add_this_language', ScopeInterface::SCOPE_STORE
        );
    }

    public function toHtml()
    {
        if (!$this->getAddThisPubId()) {
            return '';
        }

        return parent::toHtml();
    }
}