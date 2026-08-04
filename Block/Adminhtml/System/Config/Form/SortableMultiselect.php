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
}