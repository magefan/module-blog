<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Comment;

use Magefan\Blog\Model\Comment;

/**
 * Blog comment save controller
 */
class Save extends \Magefan\Blog\Controller\Adminhtml\Comment
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::comment_save';

    /**
     * Filter request params
     * @param  array $data
     * @return array
     */
    protected function filterParams($data)
    {
        /* Prepare dates */
        $dateFilter = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class);

        $filterRules = [];
        foreach (['creation_time'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $dateFilter;
            }
        }

        if (class_exists('\Magento\Framework\Filter\FilterInput')) {
            $inputFilter = new \Magento\Framework\Filter\FilterInput(
                $filterRules,
                [],
                $data
            );
        } else {
            $inputFilter = new \Zend_Filter_Input(
                $filterRules,
                [],
                $data
            );
        }

        $data = $inputFilter->getUnescaped();

        return $data;
    }
}
