<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Mageplaza import model
 */
class Mageplaza1 extends AbstractImport
{
    /**
     * @var int
     */
    private $blogEntityId;

    /**
     * @var array
     */
    private $blogAttributes = [];

    /**
     * @throws \Zend_Db_Exception
     */
    public function execute()
    {
        $adapter = $this->getDbAdapter();
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        $select = $connection->select()->from($_pref . 'mageplaza_betterblog_post')->limit(1);

        try {
            $connection->fetchAll($select);
        } catch (\Exception $e) {
            throw new \Exception(__('Mageplaza Blog Extension not detected.'), 1);
        }

        $categories = [];
        $oldCategories = [];

        /* Import categories */

        $select = $connection->select()->from(['t' => $_pref . 'mageplaza_betterblog_category'], [
                'old_id' => 'entity_id',
                'title' => 'name',
                'identifier' => 'url_key',
                'position' => 'position',
                'meta_title' => 'meta_title',
                'meta_keywords' => 'meta_keywords',
                'meta_description' => 'meta_description',
                'content' => 'description',
                'parent_id' => 'parent_id',
                'is_active' => 'status',
            ]);
        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Prepare category data */

            if (1 == $data['old_id']) {
                continue; // skip root category
            }

            if (1 == $data['parent_id']) {
                $data['parent_id'] = 0; // skip root category
            }

            /* Find store ids */
            $data['store_ids'] = [];

            $s_select = $connection->select()->from($_pref . 'mageplaza_betterblog_category_store', ['store_id'])
                ->columns()
                ->where('category_id = ?', (int)$data['old_id']);

            $s_result = $connection->fetchAll($s_select);

            foreach ($s_result as $s_data) {
                $data['store_ids'][] = $s_data['store_id'];
            }

            $data['path'] = 0;

            $category = $this->_categoryFactory->create();
            try {
                /* Initial saving */
                $category->setData($data)->save();
                $this->_importedCategoriesCount++;
                $categories[$category->getId()] = $category;
                $oldCategories[$category->getOldId()] = $category;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                unset($category);
                $this->_skippedCategories[] = $data['title'];
                $this->_logger->debug('Blog Category Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Reindexing parent categories */
        foreach ($categories as $ct) {
            if ($oldParentId = $ct->getData('parent_id')) {
                if (isset($oldCategories[$oldParentId])) {
                    $ct->setPath(
                        $parentId = $oldCategories[$oldParentId]->getId()
                    );
                }
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $changed = false;
            foreach ($categories as $ct) {
                if ($ct->getPath()) {
                    $parentId = explode('/', $ct->getPath())[0];
                    $pt = $categories[$parentId];
                    if ($pt->getPath()) {
                        $ct->setPath($pt->getPath() . '/'. $ct->getPath());
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                break;
            }
        }
        /* end*/

        foreach ($categories as $ct) {
            /* Final saving */
            $ct->save();
        }

        /* Import tags */
        $tags = [];
        $oldTags = [];
        $existingTags = [];

        $select = $connection->select()->from(['t' => $_pref . 'mageplaza_betterblog_tag'], [
                'old_id' => 'entity_id',
                'title' => 'name',
                'identifier' => 'url_key',
                'content' => 'description',
                'meta_title' => 'meta_title',
                'meta_description' => 'meta_description',
                'meta_keywords' => 'meta_keywords',
                'is_active' => 'status',
            ]);

        $result = $connection->fetchAll($select);

        foreach ($result as $data) {

            if (!$data['title']) {
                continue;
            }
            $data['title'] = trim($data['title']);

            /* Find store ids */
            $data['store_ids'] = [];

            $select = $connection->select()->from($_pref . 'mageplaza_betterblog_tag_store', ['store_id'])
                ->where('tag_id = ?', (int)$data['old_id']);

            $s_result = $connection->fetchAll($select);

            foreach ($s_result as $s_data) {
                $data['store_ids'][] = $s_data['store_id'];
            }

            try {
                /* Initial saving */
                if (!isset($existingTags[$data['title']])) {
                    $tag = $this->_tagFactory->create();
                    $tag->setData($data)->save();
                    $this->_importedTagsCount++;
                    $tags[$tag->getId()] = $tag;
                    $oldTags[$tag->getOldId()] = $tag;
                    $existingTags[$tag->getTitle()] = $tag;
                } else {
                    $tag = $existingTags[$data['title']];
                    $oldTags[$data['old_id']] = $tag;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedTags[] = $data['title'];
                $this->_logger->debug('Blog Tag Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Import posts */

        $select = $connection->select()
            ->from($_pref . 'mageplaza_betterblog_post', [
                'post_id' => 'entity_id',
                'created_at',
                'updated_at'
            ]);

        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Find post categories*/
            $postCategories = [];

            $select = $connection->select()
                ->from($_pref . 'mageplaza_betterblog_post_category', ['category_id'])
                ->where('post_id = ?', (int)$data['post_id']);

            $c_result = $connection->fetchAll($select);

            foreach ($c_result as $c_data) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }

            /* Find post tags*/
            $postTags = [];

            $select = $connection->select()->from($_pref . 'mageplaza_betterblog_post_tag', ['tag_id'])
                ->where('post_id = ?', (int)$data['post_id']);

            $t_result = $connection->fetchAll($select);

            foreach ($t_result as $t_data) {
                $oldId = $t_data['tag_id'];
                if (isset($oldTags[$oldId])) {
                    $id = $oldTags[$oldId]->getId();
                    $postTags[$id] = $id;
                }
            }

            $data['image'] = $this->getPostAttrValue($data['post_id'], 'image');

            /* Prepare post data */
            $data = [
                'old_id'            => $data['post_id'],
                'store_ids'         => [0],
                'title'             => $this->getPostAttrValue($data['post_id'], 'post_title'),
                'meta_title'        => $this->getPostAttrValue($data['post_id'], 'meta_title'),
                'meta_keywords'     => $this->getPostAttrValue($data['post_id'], 'meta_keywords'),
                'meta_description'  => $this->getPostAttrValue($data['post_id'], 'meta_description'),
                'identifier'        => $this->getPostAttrValue($data['post_id'], 'url_key'),
                'content_heading'   => '',
                'content'           => $this->getPostAttrValue($data['post_id'], 'post_content'),
                'short_content'     => $this->getPostAttrValue($data['post_id'], 'post_excerpt'),
                'creation_time'     => strtotime((string)$data['created_at']),
                'update_time'       => strtotime((string)$data['updated_at']),
                'publish_time'      => strtotime((string)$data['created_at']),
                'is_active'         => $this->getPostAttrValue($data['post_id'], 'status'),
                'enable_comments'   => $this->getPostAttrValue($data['post_id'], 'allow_comment'),
                'categories'        => $postCategories,
                'tags'              => $postTags,
                'featured_img'      => !empty($data['image']) ? 'magefan_blog/' . $data['image'] : '',
                'author_id'         => '',
            ];

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();

                /* find post comment s*/

                $select = $connection->select()->from($_pref . 'mageplaza_betterblog_post_comment', ['*'])
                    ->where('post_id = ?', (int)$post->getOldId());

                $resultComments = $connection->fetchAll($select);

                foreach ($resultComments as $comments) {
                    $commentData = [
                        'parent_id' => 0,
                        'post_id' => $post->getPostId(),
                        'status' => $comments['status'],
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['name'],
                        'author_email' => $comments['email'],
                        'text' => $comments['title'] . "\r\n" . $comments['comment'],
                        'creation_time' => $comments['created_at'],
                    ];

                    if (!$commentData['text']) {
                        continue;
                    }

                    $comment = $this->_commentFactory->create($commentData);

                    try {
                        /* saving */
                        $comment->setData($commentData)->save();
                        $this->_importedCommentsCount++;
                    } catch (\Exception $e) {
                        if (!isset($commentData['title'])) {
                            $commentData['title'] = 'Undefined';
                        }
                        $this->_skippedComments[] = $commentData['title'];
                        unset($comment);
                    }
                }

                $this->_importedPostsCount++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedPosts[] = $data['title'];
                $this->_logger->debug('Blog Post Import [' . $data['title'] . ']: '. $e->getMessage());
            }
            unset($post);
        }
        /* end */
        $adapter->getDriver()->getConnection()->disconnect();
    }

    /**
     * @param $postId
     * @param $attributeCode
     * @return |null
     * @throws \Zend_Db_Exception
     */
    private function getPostAttrValue($postId, $attributeCode)
    {
        $adapter = $this->getDbAdapter();
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        $attribute = $this->getBlogAttributeId($attributeCode);

        $select = $connection->select()->from($_pref . 'mageplaza_betterblog_post_' . $attribute['backend_type'], ['value'])
            ->where('entity_id = ?', (int)$postId)
            ->where('attribute_id = ?', (int)$attribute['attribute_id'])
            ->limit(1);

        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            return $data['value'];
        }

        return null;
    }

    /**
     * @return int
     * @throws \Zend_Db_Exception
     */
    private function getBlogEntityId()
    {
        if (null === $this->blogEntityId) {
            $adapter = $this->getDbAdapter();
            $connection = $this->getDbConnection();
            $_pref = $this->getPrefix();

            $select = $connection->select()->from($_pref . 'eav_entity_type', ['entity_type_id'])
                ->where('entity_type_code = ?', 'mageplaza_betterblog_post')
                ->limit(1);

            $result = $connection->fetchAll($select);

            foreach ($result as $data) {
                $this->blogEntityId = (int)$data['entity_type_id'];
                break;
            }

            if (empty($this->blogEntityId)) {
                throw new \Exception(__('Unable to determine blog entity ID.'), 1);
            }
        }

        return $this->blogEntityId;
    }

    /**
     * @return int
     * @throws \Zend_Db_Exception
     */
    private function getBlogAttributeId($attributeCode)
    {
        if (!isset($this->blogAttributes[$attributeCode])) {
            $this->blogAttributes[$attributeCode] = [];

            $adapter = $this->getDbAdapter();
            $connection = $this->getDbConnection();
            $_pref = $this->getPrefix();

            $select = $connection->select()
                ->from($_pref . 'eav_attribute')
                ->where('entity_type_id = ?', $this->getBlogEntityId())
                ->where('attribute_code = ?', $attributeCode)
                ->limit(1);

            $result = $connection->fetchAll($select);

            foreach ($result as $data) {
                $this->blogAttributes[$attributeCode] = $data;
                break;
            }

            if (empty($this->blogAttributes[$attributeCode])) {
                throw new \Exception(__('Unable to load blog attribute %1.'. $attributeCode), 1);
            }
        }

        return $this->blogAttributes[$attributeCode];
    }
}
