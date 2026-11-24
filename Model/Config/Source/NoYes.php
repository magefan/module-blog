<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class NoYes implements ArrayInterface
{
    public const ENABLED = 0;
    public const DISABLED = 1;

    /**
     * Get options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::DISABLED, 'label' => __('No')],
            ['value' => self::ENABLED, 'label' => __('Yes')],
        ];
    }

    /**
     * Get options in key-value format
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
