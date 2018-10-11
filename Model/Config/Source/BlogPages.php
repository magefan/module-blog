<?php

namespace Magefan\Blog\Model\Config\Source;


class BlogPages implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => 'all', 'label' => __('All Blog Pages')],
            ['value' => 'index', 'label' => __('Blog Index Page')],
            ['value' => 'post', 'label' => __('Blog Post Page')],
            ['value' => 'category', 'label' => __('Blog Category Page')],
            ['value' => 'author', 'label' => __('Blog Author Page')],
            ['value' => 'archive', 'label' => __('Blog Archive Page')],
            ['value' => 'tag', 'label' => __('Blog Tag Page')],
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