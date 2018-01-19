<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Social;

use Magento\Store\Model\ScopeInterface;

/**
 * Class AddThis
 * @package Magefan\Blog\Block\Social
 */
class AddThis extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve AddThis status
     *
     * @return boolean
     */
    public function getAddThisEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/social/add_this_enabled',
            ScopeInterface::SCOPE_STORE
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
            'mfblog/social/add_this_pubid',
            ScopeInterface::SCOPE_STORE
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
            'mfblog/social/add_this_language',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve AddThis json config
     *
     * @return string
     */
    public function getAddthisConfig()
    {
        $config = [
            'ui_language' => $this->getAddThisLanguage(),
            'data_track_clickback' => false
        ];

        $encodedData = $this->jsonHelper->jsonEncode($config);

        return $encodedData;
    }

    public function toHtml()
    {
        if (!$this->getAddThisEnabled() || !$this->getAddThisPubId()) {
            return '';
        }

        return parent::toHtml();
    }
}
