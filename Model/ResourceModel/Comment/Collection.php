<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'comment_id';

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\Blog\Model\Comment::class, \Magefan\Blog\Model\ResourceModel\Comment::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add is_active filter to collection
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this
            ->addFieldToFilter('status', \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED);
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            if (count($field) > 1) {
                return parent::addFieldToFilter($field, $condition);
            } elseif (count($field) === 1) {
                $field = $field[0];
                $condition = $condition[0] ?? $condition;
            }
        }

        if ($field === 'store_id' || $field === 'store_ids') {
            return $this->addStoreFilter($condition);
        }

        if ($field === 'post_id') {
            return $this->addPostFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add store filter to collection
     * @param array|int|\Magento\Store\Model\Store  $store
     * @param boolean $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store === null) {
            return $this;
        }

        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof \Magento\Store\Model\Store) {
                $this->_storeId = $store->getId();
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $this->_storeId = $store;
                $store = [$store];
            }

            if (in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $store)) {
                return $this;
            }

            if ($withAdmin) {
                $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store', ['in' => $store], 'public');
            $this->setFlag('store_filter_added', 1);
        }
        return $this;
    }

    /**
     * Add post filter to collection
     * @param array|int|string|\Magefan\Blog\Model\Post  $post
     * @return $this
     */
    public function addPostFilter($post)
    {
        if (!$this->getFlag('post_filter_added')) {
            if ($post instanceof \Magefan\Blog\Model\Post) {
                $post = [$post->getId()];
            }

            if (!is_array($post)) {
                $post = [$post];
            }

            $connection = $this->getConnection();
            $tableName = $this->getTable('magefan_blog_post');

            if (is_numeric(key($post))) {
                foreach ($post as $k => $id) {
                    if (!is_numeric($id)) {
                        $select = $connection->select()
                            ->from(['t' => $tableName], 'post_id')
                            ->where('t.identifier = ?', $id);

                        $id = $connection->fetchOne($select);
                        if (!$id) {
                            $id = 0;
                        }

                        $post[$k] = $id;
                    }
                }
            } else {
                $select = $connection->select()
                    ->from(['t' => $tableName], 'post_id')
                    ->where(
                        $connection->prepareSqlCondition('t.identifier', $post)
                        . ' OR ' .
                        $connection->prepareSqlCondition('t.post_id', $post)
                    );

                $post = [];
                foreach ($connection->fetchAll($select) as $item) {
                    $post[] = $item['post_id'];
                }
            }

            $this->addFilter('main_table.post_id', ['in' => $post], 'public');
            $this->setFlag('post_filter_added', 1);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store') && !$this->getFlag('store_filtered')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('magefan_blog_post_store')],
                'main_table.post_id = store_table.post_id',
                []
            )->group(
                'main_table.comment_id'
            );
            $this->setFlag('store_filtered', true);
        }
        parent::_renderFiltersBefore();
    }
}
