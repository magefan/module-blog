<?php
/**
 * Copyright © 2015-17 Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Category Display Mode Model
 */
class CategoryDisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const int
     */
    const POSTS = 0;

    /**
     * @const int
     */
    const POST_LINKS = 1;

    /**
     * @const int
     */
    const SUBCATEGORIES_LINKS = 2;

    /**
     * @const int
     */
    const POSTS_AND_SUBCATEGORIES_LINKS = 3;

    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::POSTS, 'label' => __('Posts (default)')],
            ['value' => self::POST_LINKS, 'label' => __('Posts Links')],
            ['value' => self::SUBCATEGORIES_LINKS, 'label' => __('Subcategories Links')],
            ['value' => self::POSTS_AND_SUBCATEGORIES_LINKS, 'label' => __('Posts & Subcategories Links')],
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
