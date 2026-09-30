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
 * Authors list
 */
class Authors implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magefan\Blog\Api\AuthorCollectionInterfaceFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param \Magefan\Blog\Api\AuthorCollectionInterfaceFactory $authorCollectionFactory
     */
    public function __construct(
        \Magefan\Blog\Api\AuthorCollectionInterfaceFactory $authorCollectionFactory
    ) {
        $this->authorCollectionFactory = $authorCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            $collection = $this->authorCollectionFactory->create();

            foreach ($collection as $item) {
                $this->options[] = [
                    'label' => $item->getName(),
                    'value' => $item->getId(),
                ];
            }
        }

        return $this->options;
    }

    /**
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