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
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_NONE, 'label' => __('Please select')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_ALL, 'label' => __('All Blog Pages')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_INDEX, 'label' => __('Blog Index Page')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_POST, 'label' => __('Blog Post Page')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_CATEGORY, 'label' => __('Blog Category Page')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_AUTHOR, 'label' => __('Blog Author Page')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_ARCHIVE, 'label' => __('Blog Archive Page')],
            ['value' => \Magefan\Blog\Model\Config::CANONICAL_PAGE_TYPE_TAG, 'label' => __('Blog Tag Page')],
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
