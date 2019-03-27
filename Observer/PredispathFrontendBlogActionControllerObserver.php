<?php

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magefan\Blog\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magefan\Blog\Model\NoSlashUrlRedirect;

/**
 * Class PredispathFrontendBlogActionControllerObserver
 * @package Magefan\Blog\Observer
 */
class PredispathFrontendBlogActionControllerObserver implements ObserverInterface
{
    /**
     * @var NoSlashUrlRedirect
     */
    protected $noSlashUrlRedirect;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * PredispathFrontendBlogActionControllerObserver constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param NoSlashUrlRedirect $noSlashUrlRedirect
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NoSlashUrlRedirect $noSlashUrlRedirect
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->noSlashUrlRedirect = $noSlashUrlRedirect;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $redirectToNoSlash = $this->scopeConfig->getValue(Config::XML_PATH_REDIRECT_TO_NO_SLASH, ScopeInterface::SCOPE_STORE);

        $advancedPermalinkEnabled =  $this->scopeConfig->getValue(Config::XML_PATH_ADVANCED_PERMALINK_ENABLED, ScopeInterface::SCOPE_STORE);

        if ($redirectToNoSlash && !$advancedPermalinkEnabled) {
            $this->noSlashUrlRedirect->execute($observer);
        }
    }
}
