<?php
namespace Magefan\Blog\Model\Config\Source;

class SetTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Default')],
            ['value' => 'custom', 'label' => __('Custom')]
        ];
    }
}