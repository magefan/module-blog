<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model;

/**
 * Tag management model
 */
class TagManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\TagFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\TagFactory $tagFactory
     * @param \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magefan\Blog\Model\ResourceModel\Tag\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_itemFactory = $tagFactory;
    }

     /**
      * Retrieve list of tag by page type, term, store, etc
      *
      * @param  string $type
      * @param  string $term
      * @param  int $storeId
      * @param  int $page
      * @param  int $limit
      * @return string
      */
    public function getList($type, $term, $storeId, $page, $limit)
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection
                ->addActiveFilter()
                ->addStoreFilter($storeId)
                ->setCurPage($page)
                ->setPageSize($limit);

            $type = strtolower($type);

            switch ($type) {
                case 'search':
                    $collection->addSearchFilter($term);
                    break;
            }

            $tags = [];
            foreach ($collection as $item) {
                $tags[] = $this->getDynamicData($item);
            }

            $result = [
                'tags' => $tags,
                'total_number' => $collection->getSize(),
                'current_page' => $collection->getCurPage(),
                'last_page' => $collection->getLastPageNumber(),
            ];

            return json_encode($result);
        } catch (\Exception $e) {
            return $this->getError($e->getMessage());
        }
    }

    /**
     * @param $item
     * @return array
     */
    protected function getDynamicData($item)
    {
        $data = $item->getData();

        $keys = [
            'meta_description',
            'meta_title',
            'tag_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace('_', '', ucwords($key, '_'));
            $data[$key] = $item->$method();
        }

        return $data;
    }
}
