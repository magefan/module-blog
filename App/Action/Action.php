<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\App\Action;

/**
 * Blog frontend action controller
 */
abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * Retrieve true if blog extension is enabled.
     *
     * @return bool
     */
    protected function moduleEnabled()
    {
        return (bool) $this->getConfigValue(
            \Magefan\Blog\Helper\Config::XML_PATH_EXTENSION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve store config value
     *
     * @param string $path
     * @return string | null | bool
     */
    protected function getConfigValue($path)
    {
        $config = $this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        return $config->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether the preview secret from request matches the object's secret.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidSecret($object): bool
    {
        $secret = (string)$this->getRequest()->getParam('secret');
        return $secret && $object->getData('secret') === $secret;
    }

    /**
     * Throw control to cms_index_noroute action.
     *
     * @return void
     */
    protected function _forwardNoroute()
    {
        $this->_forward('index', 'noroute', 'cms');
    }
}
