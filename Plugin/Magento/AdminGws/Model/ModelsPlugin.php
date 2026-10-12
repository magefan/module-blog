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

            $originStoreId  = $model->getStoreId();
            $model->setStoreId($model->getStoreIds());
            $result = $proceed($model);
            $model->setStoreId($originStoreId);
            return $result;

        }

        return $proceed($model);
    }
}
