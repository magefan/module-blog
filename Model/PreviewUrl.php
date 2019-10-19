<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

/**
 * Blog url model
 */
class PreviewUrl extends Url
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Url $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($registry, $url, $storeManager, $scopeConfig);
    }

    /**
     * Retrieve blog page preview url
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  string $controllerName
     * @return string
     */
    public function getUrl($object, $controllerName)
    {
        $storeIds = $object->getStoreIds();
        if (count($storeIds)) {
            $storeId = array_shift($storeIds);
        } else {
            $storeId = 0;
        }

        if (0 == $storeId) {
            $storeId = $this->_storeManager->getDefaultStoreView()->getId();
        }

        $this->storeId = $storeId;

        $scope = $this->_storeManager->getStore($this->storeId);
        $url = $this->_url->setScope($scope)
            ->getUrl(
                '',
                [
                    '_direct'   => $this->getUrlPath($object->getIdentifier(), $controllerName),
                    'key'       => null,
                    '_nosid'    => true,
                ]
            );

        $url .= (false === strpos($url, '?')) ? '?' : '&';
        $url .= 'secret=' . $object->getSecret();
        return $url;
    }
}
