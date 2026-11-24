<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SortableMultiselect extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magefan_Blog::system/config/sortable_multiselect.phtml';

    /**
     * Render element HTML.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(['element' => $element]);
        return $this->_toHtml();
    }

    /**
     * Return available options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        $values = $this->getElement()->getValues();
        if (!is_array($values)) {
            return [];
        }
        return $values;
    }

    /**
     * Return selected option values in stored order.
     *
     * @return array
     */
    public function getSelectedValues(): array
    {
        $value = $this->getElement()->getValue();
        return $value ? explode(',', $value) : [];
    }

    /**
     * Return options ordered by stored selection, with unselected options appended.
     *
     * @return array
     */
    public function getSortedOptions(): array
    {
        $optionsByValue = [];
        foreach ($this->getOptions() as $option) {
            $optionsByValue[$option['value']] = $option;
        }

        $sorted = [];
        foreach ($this->getSelectedValues() as $value) {
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
