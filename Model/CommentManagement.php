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
      * @param  string $type
      * @param  string $postId
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

    public function getDynamicData($item)
    {
        $data = $item->getData();
        $fields = ['replies' => 1];
        if (is_array($fields) && array_key_exists('replies', $fields)) {
            $replies = [];
            foreach ($item->getRepliesCollection() as $reply) {
                $replier = $reply->getDynamicData(
                    isset($fields['replies']) ? $fields['replies'] : null
                );
                unset($replier['author_email']);
                $replies[] = $replier;
            }
            $data['replies'] = $replies;
        }
        unset($data['author_email']);

        return $data;
    }
}
