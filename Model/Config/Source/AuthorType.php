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
 * Authors types
 *
 */
class AuthorType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @const string
     */
    public const GUEST = 0;

    /**
     * @const string
     */
    public const CUSTOMER = 1;

    /**
     * @const string
     */
    public const ADMIN = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::GUEST, 'label' => __('Guest')],
            ['value' => self::CUSTOMER, 'label' => __('Customer')],
            ['value' => self::ADMIN, 'label' => __('Admin')],
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
