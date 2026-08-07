<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SortableMultiselect extends Field
{
    protected $_template = 'Magefan_Blog::system/config/sortable_multiselect.phtml';

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(['element' => $element]);
        return $this->_toHtml();
    }

    public function getOptions(): array
    {
        $values = $this->getElement()->getValues();
        if (!is_array($values)) {
            return [];
        }
        return $values;
    }

    public function getSelectedValues(): array
    {
        $value = $this->getElement()->getValue();
        return $value ? explode(',', $value) : [];
    }

    public function getSortedOptions(): array
    {
        $options = $this->getOptions();
        $selected = $this->getSelectedValues();

        $optionsByValue = [];
        foreach ($options as $option) {
            $optionsByValue[$option['value']] = $option;
        }

        $sorted = [];
        foreach ($selected as $value) {
            if (isset($optionsByValue[$value])) {
                $sorted[] = $optionsByValue[$value];
                unset($optionsByValue[$value]);
            }
        }
        foreach ($optionsByValue as $option) {
            $sorted[] = $option;
        }

        return $sorted;
    }
}
