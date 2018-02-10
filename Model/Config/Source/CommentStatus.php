<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Comment statuses
 */
class CommentStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const string
     */
    const PENDING = 0;

    /**
     * @const int
     */
    const APPROVED = 1;

    /**
     * @const int
     */
    const NOT_APPROVED = 2;

    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::APPROVED, 'label' => __('Approved')],
            ['value' => self::NOT_APPROVED, 'label' => __('Not Approved')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
