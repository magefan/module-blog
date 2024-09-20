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
class Mageplaza extends AbstractImport
{

    public function execute()
    {
        $adapter = $this->getDbAdapter();
        $_pref = $this->getPrefix();

        $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_blog_category LIMIT 1';
        try {
            $adapter->query($sql)->execute();
        } catch (\Exception $e) {
            throw new \Exception(__('Mageplaza Blog Extension not detected.'), 1);
        }

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.category_id as old_id,
                    t.name as title,
                    t.url_key as identifier,
                    t.position as position, ' .
                    /*
                    t.meta_title as meta_title,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description,
                    */
                    't.description as content,
                    t.parent_id as parent_id,
                    t.enabled as is_active,
                    t.store_ids as store_ids
                FROM ' . $_pref . 'mageplaza_blog_category t';
        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {
            /* Prepare category data */

            $data['store_ids'] = explode(',', $data['store_ids']);
            $data['path'] = 0;
            /*
            $data['identifier'] = trim(strtolower($data['identifier']));
            if (strlen($data['identifier']) == 1) {
                $data['identifier'] .= $data['identifier'];
            }
            */
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

        $sql = 'SELECT
                    t.tag_id as old_id,
                    t.name as title,
                    t.url_key as identifier, 
                    t.description as content, ' .
                    /*
                    t.meta_title as meta_title,
                    t.meta_description as meta_description,
                    t.meta_keywords as meta_keywords,
                    */
                    't.enabled as is_active
                FROM ' . $_pref . 'mageplaza_blog_tag t';

        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {
            /* Prepare tag data */
            /*
            foreach (['title'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }
            */

            if (!$data['title']) {
                continue;
            }

            $data['title'] = trim($data['title']);

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
        $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_blog_post';
        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {
            /* Find post categories*/
            $postCategories = [];
            $c_sql = 'SELECT category_id FROM ' . $_pref .
                     'mageplaza_blog_post_category WHERE post_id = "'.$data['post_id'].'"';
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
            $t_sql = 'SELECT tag_id FROM ' . $_pref . 'mageplaza_blog_post_tag WHERE post_id = "'.$data['post_id'].'"';

            $t_result = $adapter->query($t_sql)->execute();

            foreach ($t_result as $t_data) {
                $oldId = $t_data['tag_id'];
                if (isset($oldTags[$oldId])) {
                    $id = $oldTags[$oldId]->getId();
                    $postTags[$id] = $id;
                }
            }

            /* Find store ids */
            $data['store_ids'] = explode(',', $data['store_ids']);

            /* Prepare post data */
            $data = [
                'old_id'            => $data['post_id'],
                'store_ids'         => $data['store_ids'],
                'title'             => $data['name'],
                //'meta_title'        => $data['meta_title'],
                //'meta_keywords'     => $data['meta_keywords'],
                //'meta_description'  => $data['meta_description'],
                'identifier'        => $data['url_key'],
                'content_heading'   => '',
                'content'           => $data['post_content'],
                'short_content'     => $data['short_description'],
                'creation_time'     => strtotime((string)$data['created_at']),
                'update_time'       => strtotime((string)$data['updated_at']),
                'publish_time'      => strtotime((string)$data['publish_date']),
                'is_active'         => $data['enabled'],
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
                       'mageplaza_blog_comment WHERE `post_id` = ' . $post->getOldId();
                $resultComments = $adapter->query($sql)->execute();

                foreach ($resultComments as $comments) {
                    $commentData = [
                        'parent_id' => 0,
                        'post_id' => $post->getPostId(),
                        'status' => ($comments['status'] == 3) ?
                            \Magefan\Blog\Model\Config\Source\CommentStatus::PENDING :
                            $comments['status'],
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['user_name'],
                        'author_email' => $comments['user_email'],
                        'text' => $comments['content'],
                        'creation_time' => $comments['created_at'],
                    ];
                    /*
                    foreach (['text'] as $key) {
                        $commentData[$key] = mb_convert_encoding($commentData[$key], 'HTML-ENTITIES', 'UTF-8');
                    }
                    */

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
}
