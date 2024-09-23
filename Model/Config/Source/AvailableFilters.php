<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

class AvailableFilters implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options int
     *
     * @return array
     */

    public function toOptionArray()
    {
        return [
            ['value' => 'category', 'label' => __('Category')],
            ['value' => 'author', 'label' => __('Author')],
            ['value' => 'tag', 'label' => __('Tag')],
            ['value' => 'publication_date', 'label' => __('Publication Date (From/To)')]
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