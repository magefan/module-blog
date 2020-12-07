<?php
namespace Magefan\Blog\Model\Config\Source;

class PostListTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'default', 'label' => __('Default')],
            ['value' => 'list', 'label' => __('List')],
            ['value' => 'grid', 'label' => __('Grid')],
        ];
    }
}