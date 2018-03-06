<?php
/**
 * Copyright © 2015-17 Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Class PostsSortBy
 * @package Magefan\Blog\Model\Config\Source
 */
class PostsSortBy implements \Magento\Framework\Option\ArrayInterface
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
            ['value' => self::POSTS, 'label' => __('Publish Date (default)')],
            ['value' => self::POST_LINKS, 'label' => __('Position')],
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
