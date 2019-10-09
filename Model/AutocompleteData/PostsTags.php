<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Model\AutocompleteData;

use Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Class BlogPost
 * Provides Data for Autocomplete Ajax Call
 */
class PostsTags
{
    /**
     * @var BlogFactory
     */
    protected $collectionFactory;

    /**
     * Post constructor.
     * @param BlogFactory $blogFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $search
     * @return array
     */
    public function getItems($search)
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
            ->setPageSize(10)
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
