<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Magento\AdminGws\Model;

class ModelsPlugin
{
    /**
     * @param $subject
     * @param callable $proceed
     * @param $model
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
