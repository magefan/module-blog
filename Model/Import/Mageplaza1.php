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
        $_pref = $this->getPrefix();

        $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_betterblog_post LIMIT 1';
        try {
            $adapter->query($sql)->execute();
        } catch (\Exception $e) {
            throw new \Exception(__('Mageplaza Blog Extension not detected.'), 1);
        }

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.entity_id as old_id,
                    t.name as title,
                    t.url_key as identifier,
                    t.position as position,
                    t.meta_title as meta_title,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description,
                    t.description as content,
                    t.parent_id as parent_id,
                    t.status as is_active
                FROM ' . $_pref . 'mageplaza_betterblog_category t';
        $result = $adapter->query($sql)->execute();
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
            $s_sql = 'SELECT store_id FROM '.$_pref.'mageplaza_betterblog_category_store WHERE category_id = "'.$data['old_id'].'"';
            $s_result =  $adapter->query($s_sql)->execute();
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
                $this->_logger->addDebug('Blog Category Import [' . $data['title'] . ']: '. $e->getMessage());
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

        $sql = 'SELECT
                    t.entity_id as old_id,
                    t.name as title,
                    t.url_key as identifier, 
                    t.description as content,
                    t.meta_title as meta_title,
                    t.meta_description as meta_description,  
                    t.meta_keywords as meta_keywords,    
                    t.status as is_active
                FROM ' . $_pref . 'mageplaza_betterblog_tag t';

        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {

            if (!$data['title']) {
                continue;
            }
            $data['title'] = trim($data['title']);

            /* Find store ids */
            $data['store_ids'] = [];
            $s_sql = 'SELECT store_id FROM '.$_pref.'mageplaza_betterblog_tag_store WHERE tag_id = "'.$data['old_id'].'"';
            $s_result =  $adapter->query($s_sql)->execute();
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
                $this->_logger->addDebug('Blog Tag Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Import posts */
        $sql = 'SELECT entity_id as post_id, created_at, updated_at  FROM ' . $_pref . 'mageplaza_betterblog_post';
        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {
            /* Find post categories*/
            $postCategories = [];
            $c_sql = 'SELECT category_id FROM ' . $_pref .
                     'mageplaza_betterblog_post_category WHERE post_id = "'.$data['post_id'].'"';
            $c_result = $adapter->query($c_sql)->execute();
            foreach ($c_result as $c_data) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }

            /* Find post tags*/
            $postTags = [];
            $t_sql = 'SELECT tag_id FROM ' . $_pref . 'mageplaza_betterblog_post_tag WHERE post_id = "'.$data['post_id'].'"';

            $t_result = $adapter->query($t_sql)->execute();

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
                'creation_time'     => strtotime($data['created_at']),
                'update_time'       => strtotime($data['updated_at']),
                'publish_time'      => strtotime($data['created_at']),
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
                $sql = 'SELECT * FROM ' . $_pref .
                       'mageplaza_betterblog_post_comment WHERE `post_id` = ' . $post->getOldId();
                $resultComments = $adapter->query($sql)->execute();

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
                $this->_logger->addDebug('Blog Post Import [' . $data['title'] . ']: '. $e->getMessage());
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
        $_pref = $this->getPrefix();

        $attribute = $this->getBlogAttributeId($attributeCode);

        $sql = 'SELECT value FROM ' . $_pref . 'mageplaza_betterblog_post_' . $attribute['backend_type'] . ' WHERE entity_id = "' . ((int)$postId) . '"
            AND attribute_id = "' . ((int)$attribute['attribute_id']) . '" LIMIT 1';
        $result = $adapter->query($sql)->execute();
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
            $_pref = $this->getPrefix();

            $sql = 'SELECT entity_type_id FROM ' . $_pref . 'eav_entity_type WHERE entity_type_code = "mageplaza_betterblog_post" LIMIT 1';
            $result = $adapter->query($sql)->execute();
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
            $_pref = $this->getPrefix();

            $sql = 'SELECT * FROM ' . $_pref . 'eav_attribute WHERE entity_type_id = "' . $this->getBlogEntityId() . '" 
                 AND attribute_code = "' . $attributeCode . '" LIMIT 1';
            $result = $adapter->query($sql)->execute();
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
