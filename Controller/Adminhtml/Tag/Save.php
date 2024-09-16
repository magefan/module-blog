<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Tag;

/**
 * Blog tag save controller
 */
class Save extends \Magefan\Blog\Controller\Adminhtml\Tag
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::tag_save';

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
     * @param $model
     * @param $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        /* Prepare images */
        $this->prepareImagesBeforeSave($model, ['tag_img']);
    }
}
