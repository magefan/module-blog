<?php

namespace Magefan\Blog\Plugin\Magento\AdminGws\Model;

use Magento\AdminGws\Model\Models;

class ModelsPlugin
{
    const POST_INTERCEPTOR_TYPE = 'Magefan\Blog\Model\Post\Interceptor';
    const CATEGORY_INTERCEPTOR_TYPE = 'Magefan\Blog\Model\Category\Interceptor';

    public function aroundCmsPageSaveBefore(Models $subject, callable $proceed, $model)
    {
        $modelType = get_class($model);
        if ($modelType != self::POST_INTERCEPTOR_TYPE && $modelType != self::CATEGORY_INTERCEPTOR_TYPE) {
            return $proceed;
        }
        $storeId = $model->getStoreId();

        if ($model->getStoreIds()) {
            $model->setStoreId($model->getStoreIds());
        }
    }
}
