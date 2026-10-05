<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Plugin\Magento\AdminGws\Model;

use Magento\Store\Model\StoreManagerInterface;

class ModelsPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Intercepts the CMS page save process to adjust store-related data for blog models.
     *
     * @param mixed $subject
     * @param callable $proceed
     * @param mixed $model
     * @return callable
     */
    public function aroundCmsPageSaveBefore($subject, callable $proceed, $model)
    {
        $isBlogModel = ($model instanceof \Magefan\Blog\Model\Post
            || $model instanceof \Magefan\Blog\Model\Category
            || $model instanceof \Magefan\Blog\Model\Tag
            || $model instanceof \Magefan\Blog\Api\AuthorInterface
        );
        if ($isBlogModel) {
            /* Restricted role: stores are already filtered by AdminGws */
            $allowedStoreIds = array_keys($this->storeManager->getStores());
            $storeIds = (array)$model->getStoreIds();

            /* No allowed store view selected (e.g. field is hidden or "All Store Views") - use all allowed ones */
            if ($allowedStoreIds && !array_intersect($storeIds, $allowedStoreIds)) {
                $model->setStoreIds($allowedStoreIds);
            }

            if ($model->getStoreIds()) {
                $model->setStoreId($model->getStoreIds());
            }
        }

        return $proceed($model);
    }
}
