<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

/**
 * Post management model
 */
class PostManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     */
    public function __construct(
        \Magefan\Blog\Model\PostFactory $postFactory
    ) {
        $this->_itemFactory = $postFactory;
    }

    /**
     * Retrieve list of post by page type, term, store, etc
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
            $collection = $this->_itemFactory->create()->getCollection();
            $collection
                ->addActiveFilter()
                ->addStoreFilter($storeId)
                ->setOrder('publish_time', 'DESC')
                ->setCurPage($page)
                ->setPageSize($limit);

            $type = strtolower($type);

            switch ($type) {
                case 'archive':
                    $term = explode('-', $term);
                    if (count($term) < 2) {
                        $year = (int) $term;
                        $month = 0;
                    } else {
                        list($year, $month) = $term;
                        $year = (int) $year;
                        $month = (int) $month;
                    }

                    if ($year < 1970) {
                        return false;
                    }
                    if ($month < 0 || $month > 12) {
                        return false;
                    }

                    $collection->addArchiveFilter($year, $month);
                    break;
                case 'author':
                    $collection->addAuthorFilter($term);
                    break;
                case 'category':
                    $collection->addCategoryFilter($term);
                    break;
                case 'search':
                    $collection->addSearchFilter($term);
                    break;
                case 'tag':
                    $collection->addTagFilter($term);
                    break;
            }

            $posts = [];
            foreach ($collection as $item) {
                $posts[] = $this->getDynamicData($item);
            }

            $result = [
                'posts' => $posts,
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
            'og_image',
            'og_type',
            'og_description',
            'og_title',
            'meta_description',
            'meta_title',
            'short_filtered_content',
            'filtered_content',
            'first_image',
            'featured_image',
            'post_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace('_', '', ucwords($key, '_'));
            $data[$key] = $item->$method();
        }

        return $data;
    }
}
