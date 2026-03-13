<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model;

/**
 * Blog url model
 */
class PreviewUrl extends Url
{

    /**
     * Retrieve blog page preview url
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @param  string $controllerName
     * @return string
     */
    public function getUrl($object, $controllerName): string
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
