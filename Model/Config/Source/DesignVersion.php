<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in creating options for commetns config value selection
 */
class DesignVersion implements \Magento\Framework\Option\ArrayInterface
{
    const INITIAL = '2015-11';
    const MODERN = '2021-06';
    const VALOR = '2025-07';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INITIAL, 'label' => '2015-11 (Initial)'],
            ['value' => self::MODERN, 'label' => '2021-06 (Modern)'],
            ['value' => self::VALOR, 'label' => '2025-07 (Valor)']
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
