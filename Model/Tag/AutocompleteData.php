<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model\Tag;

use Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Provides Data for Tag Autocomplete Ajax Call
 */
class AutocompleteData
{
    /**
     * @var BlogFactory
     */
    protected $collectionFactory;

    /**
     * Post constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get items by search term
     *
     * @param string $search
     * @return array
     */
    public function getItems(string $search): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ['tag_id', 'title'],
                [
                    ['eq' => $search],
                    ['like' => '%' . $search . '%'],
                ]
            )
            ->setPageSize(15)
        ;

        $result = [];
        foreach ($collection as $item) {
            $result[] = [
                'value' => $item->getTitle(),
                'label' => $item->getTitle()
            ];
        }

        return $result;
    }
}
