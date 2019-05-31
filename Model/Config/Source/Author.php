<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

/**
 * Authors list
 *
 */
class Author implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Api\AuthorCollectionInterfaceFactory $authorCollectionFactory
     * @param void
     */
    public function __construct(
        \Magefan\Blog\Api\AuthorCollectionInterfaceFactory  $authorCollectionFactory
    ) {
        $this->authorCollectionFactory = $authorCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [['label' => __('Please select'), 'value' => 0]];
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
