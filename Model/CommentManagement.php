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
 * Comment management model
 */
class CommentManagement extends AbstractManagement
{
    /**
     * @var \Magefan\Blog\Model\CommentFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magefan\Blog\Model\CommentFactory $commentFactory
     */
    public function __construct(
        \Magefan\Blog\Model\CommentFactory $commentFactory
    ) {
        $this->_itemFactory = $commentFactory;
    }

    /**
     * Retrieve list of tag by page type, term, store, etc
     *
     * @param string $type
     * @param mixed $term
     * @param int $storeId
     * @param int $page
     * @param int $limit
     * @return string
     */
    public function getList($type, $term, $storeId, $page, $limit)
    {
        return $this->fetchFilteredComments($type, $term, $storeId, $page, $limit);
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
        return $this->fetchFilteredComments($type, $term, $storeId, $page, $limit, false);
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
    public function fetchFilteredComments($type, $term, $storeId, $page, $limit, bool $active = true)
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
                case 'list':
                    $collection->addPostFilter($term);
                    break;
            }

            $comments = [];
            foreach ($collection as $item) {
                $comments[] = $this->getDynamicData($item);
            }

            $result = [
                'comments' => $comments,
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
     * Retrieves dynamic data from the given item, including nested replies.
     *
     * @param object $item
     * @return array
     */
    public function getDynamicData($item)
    {
        $data = $item->getData();

        $replies = [];
        foreach ($item->getRepliesCollection() as $reply) {
            $replier = $this->getDynamicData($reply);
            $replies[] = $replier;
        }
        $data['replies'] = $replies;
        $data = $this->unsetSensitiveData($data);

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function unsetSensitiveData($data)
    {
        $data = parent::unsetSensitiveData($data);
        unset(
            $data['author_email'],
            $data['customer_id'],
            $data['admin_id']
        );

        return $data;
    }
}
