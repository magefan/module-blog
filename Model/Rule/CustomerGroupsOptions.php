<?php
namespace Magefan\Blog\Model\Rule;

class CustomerGroupsOptions implements \Magento\Framework\Data\OptionSourceInterface
{

    public function __construct(
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->objectConverter = $objectConverter;
    }

    public function getGroupOptions() {
        return  [
            ['value' => 0, 'label' => __('All Groups')],
            ['value' => 1, 'label' => __('General')],
            ['value' => 2, 'label' => __('Wholesale')],
            ['value' => 3, 'label' => __('Retailer')]
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $options = [];
        foreach ($this->getGroupOptions() as $item) {
            $options[] = [
                'value' => $item['value'],
                'label' => $item['label'],
            ];
        }
        return $options;
    }
}