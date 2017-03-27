<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
     * @return bool
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
                case 'archive' :
                    $term = explode('-', $term);
                    if (count($term) < 2) {
                        return false;
                    }
                    list($year, $month) = $term;
                    $year = (int) $year;
                    $month = (int) $month;

                    if ($year < 1970) {
                        return false;
                    }
                    if ($month < 1 || $month > 12) {
                        return false;
                    }

                    $collection->addArchiveFilter($year, $month);
                    break;
                case 'author' :
                    $collection->addAuthorFilter($term);
                    break;
                case 'category' :
                    $collection->addCategoryFilter($term);
                    break;
                case 'search' :
                    $collection->addSearchFilter($term);
                    break;
                case 'tag' :
                    $collection->addTagFilter($term);
                    break;
            }

            $posts = [];
            foreach ($collection as $item) {
                $item->initDinamicData();
                $posts[] = $item->getData();
            }

            $result = [
                'posts' => $posts,
                'total_number' => $collection->getSize(),
                'current_page' => $collection->getCurPage(),
                'last_page' => $collection->getLastPageNumber(),
            ];

            return json_encode($result);
        } catch (\Exception $e) {
            return false;
        }
    }

}
