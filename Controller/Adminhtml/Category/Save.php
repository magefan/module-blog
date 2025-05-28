<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Category;

/**
 * Blog category save controller
 */
class Save extends \Magefan\Blog\Controller\Adminhtml\Category
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::category_save';

    /**
     * After model save
     * @param  \Magefan\Blog\Model\Category $model
     * @param  \Magento\Framework\App\Request\Http $request
     * @return void
     */
    protected function _afterSave($model, $request)
    {
        $model->addData(
            [
                'parent_id' => $model->getParentId(),
                'level' => $model->getLevel(),
            ]
        );
    }

    protected function _beforeSave($model, $request)
    {
        $categoryPostData = $request->getPostValue();

        $isNewCategory = empty($categoryPostData['category_id']);
        $parentId = $categoryPostData['parent'] ?? null;

        if ($parentId) {
            $model->setParentId($parentId);
        }

        if ($isNewCategory) {
            $storeId = 0;
            $parentCategory = $this->getParentCategory($parentId, $storeId);

            $path = $parentCategory->getPath();

            if ($parentId) {
                if ($path) {
                    $path .= '/' . $parentId;
                } else {
                    $path = $parentId;
                }
            }

            $model->setPath($path);
            $model->setParentId($parentCategory->getId());
            $model->setLevel(null);
        }

        /* Prepare images */
        $this->prepareImagesBeforeSave($model, ['category_img']);
    }

    /**
     * Filter request params
     * @param  array $data
     * @return array
     */
    protected function filterParams($data)
    {
        /* Prepare dates */
        $dateFilter = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\Filter\Date::class);

        $filterRules = [];
        foreach (['custom_theme_from', 'custom_theme_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $dateFilter;
            }
        }

        $inputFilter = $this->getFilterInput(
            $filterRules,
            [],
            $data
        );

        $data = $inputFilter->getUnescaped();

        return $data;
    }


    /**
     * Get parent category
     *
     * @param int $parentId
     * @param int $storeId
     *
     * @return \Magefan\Blog\Model\Category
     */
    protected function getParentCategory($parentId, $storeId)
    {
        if (!$parentId) {
            if ($storeId) {
                //$parentId = $this->storeManager->getStore($storeId)->getRootCategoryId();
            } else {
                $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                return $this->_objectManager->create(\Magefan\Blog\Model\Category::class)
                    ->setId($parentId)
                    ->setPath('');
            }
        }

        return $this->_objectManager->create(\Magefan\Blog\Model\Category::class)->load($parentId);
    }
}
