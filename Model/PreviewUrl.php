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
 *
 */
class PreviewUrl
{
    /**
     * @var Url
     */
    private $blogUrl;

    /**
     * @var \Magento\Framework\Url
     */
    private $frameworkUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @param Url $url
     * @param \Magento\Framework\Url $frameworkUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\Random $random
     */
    public function __construct(
        Url $blogUrl,
        \Magento\Framework\Url $frameworkUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $random
    ) {
        $this->blogUrl = $blogUrl;
        $this->frameworkUrl = $frameworkUrl;
        $this->storeManager = $storeManager;
        $this->random = $random;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return string
     */
    private function getSecret($object): string
    {
        if ($object->getId() && !$object->getData('secret')) {
            $object->setData('secret', $this->random->getRandomString(32));
            $object->save();
        }

        return (string) $object->getData('secret');
    }

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
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $this->blogUrl->startStoreEmulation($this->storeManager->getStore($storeId));
        $url = $this->blogUrl->getUrl($object, $controllerName);
        $this->blogUrl->stopStoreEmulation();


        $url .= (false === strpos($url, '?')) ? '?' : '&';
        $url .= 'secret=' . $this->getSecret($object);
        return $url;
    }
}
