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
 * Category management model
 */
class CategoryManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magefan\Blog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_itemFactory = $categoryFactory;
    }

     /**
      * Retrieve list of category by page type, term, store, etc
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
        return $this->fetchFilteredCategories($type, $term, $storeId, $page, $limit);
    }

    /**
     * Retrieve all posts (including inactive) filtered by type, term, store, etc
     *
     * @param string $type
     * @param string $term
     * @param int $storeId
     * @param int $page
     * @param int $limit
     * @return string
     */
    public function getAll($type, $term, $storeId, $page, $limit)
    {
        return $this->fetchFilteredCategories($type, $term, $storeId, $page, $limit, false);
    }

    /**
     *  Build a JSON response with posts filtered by type, term, store, etc
     *
     * @param string $type
     * @param string $term
     * @param int $storeId
     * @param int $page
     * @param int $limit
     * @param bool $active
     * @return false|string
     */
    protected function fetchFilteredCategories($type, $term, $storeId, $page, $limit, bool $active = true)
    {
        try {
            $collection = $this->_itemFactory->create()->getCollection();
            if ($active) {
                $collection->addActiveFilter();
            }
            $collection
                ->addStoreFilter($storeId)
                ->setCurPage($page)
                ->setPageSize($limit);

            $type = strtolower($type);

            switch ($type) {
                case 'search':
                    $collection->addSearchFilter($term);
                    break;
            }

            $categories = [];
            foreach ($collection as $item) {
                $categories[] = $this->getDynamicData($item);
            }

            $result = [
                'categories' => $categories,
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
     * Retrieve dynamic data for a given item.
     *
     * @param object $item
     * @return array
     */
    protected function getDynamicData($item)
    {
        $data = $item->getData();

        $keys = [
            'meta_description',
            'meta_title',
            'category_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace('_', '', ucwords($key, '_'));
            $data[$key] = $item->$method();
        }

        return $data;
    }
}
