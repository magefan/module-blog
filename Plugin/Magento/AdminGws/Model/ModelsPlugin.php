<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Plugin\Magento\AdminGws\Model;

class ModelsPlugin
{
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
        );
        if ($isBlogModel) {
            $storeId = $model->getStoreId();
            if ($model->getStoreIds()) {
                $model->setStoreId($model->getStoreIds());
            }
        }

        return $proceed($model);
    }
}
