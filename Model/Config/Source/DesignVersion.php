<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

/**
 * Used in creating options for commetns config value selection
 */
class DesignVersion implements \Magento\Framework\Option\ArrayInterface
{
    public const INITIAL = 'design_initial';
    public const MODERN = 'design_modern';
    public const VALOR = 'design_valor';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INITIAL, 'label' => 'Initial (2015)'],
            ['value' => self::MODERN, 'label' => 'Modern (2021)'],
            ['value' => self::VALOR, 'label' => 'Valor (2025)']
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
