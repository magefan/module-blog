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
 * Aw import model
 */
class Aw extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
        $adapter = $this->getDbAdapter();
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        $select = $connection->select()
            ->from($_pref.'aw_blog_cat')->limit(1);

        try {
            $connection->fetchAll($select);
        } catch (\Exception $e) {
            throw new \Exception(__('AheadWorks Blog Extension not detected.'), 1);
        }

        $storeIds = array_keys($this->_storeManager->getStores(true));

        $categories = [];
        $oldCategories = [];

        /* Import categories */

        $select = $connection->select()
            ->from(['t' => $_pref . 'aw_blog_cat'],[
                'old_id' => 'cat_id',
                'title' => 'title',
                'identifier' => 'identifier',
                'position' => 'sort_order',
                'meta_keywords' => 'meta_keywords',
                'meta_description' => 'meta_description'
            ]);
        $result = $connection->fetchAll($select);
        foreach ($result as $data) {
            /* Prepare category data */

            /* Find store ids */
            $data['store_ids'] = [];

            $s_select = $connection->select()
                ->from($_pref . 'aw_blog_cat_store', ['store_id'])
                ->where('cat_id = ?', (int)$data['old_id']);
            $s_result = $connection->fetchAll($s_select);

            foreach ($s_result as $s_data) {
                $data['store_ids'][] = $s_data['store_id'];
            }

            foreach ($data['store_ids'] as $key => $id) {
                if (!in_array($id, $storeIds)) {
                    unset($data['store_ids'][$key]);
                }
            }

            if (empty($data['store_ids']) || in_array(0, $data['store_ids'])) {
                $data['store_ids'] = 0;
            }

            $data['is_active'] = 1;
            $data['path'] = 0;
            $data['identifier'] = trim(strtolower($data['identifier']));
            if (strlen($data['identifier']) == 1) {
                $data['identifier'] .= $data['identifier'];
            }

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

        /* Import tags */
        $tags = [];
        $oldTags = [];
        $existingTags = [];

        $select = $connection->select()
            ->from(['t' => $_pref . 'aw_blog_tags'], [
                'old_id' => 'id',
                'title' => 'tag'
            ]);
        $result = $connection->fetchAll($select);

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

        $select = $connection->select()
            ->from($_pref . 'aw_blog');
        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Find post categories*/
            $postCategories = [];

            $c_select = $connection->select()
                ->from($_pref . 'aw_blog_post_cat', ['category_id' => 'cat_id'])
                ->where('post_id = ?', (int)$data['post_id']);
            ;

            $c_result = $connection->fetchAll($c_select);

            foreach ($c_result as $c_data) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }

            /* Find store ids */
            $data['store_ids'] = [];

            $s_select = $connection->select()
                ->from($_pref . 'aw_blog_store', ['store_id'])
                ->where('post_id = ?', (int)$data['post_id']);

            $s_result = $connection->fetchAll($s_select);

            foreach ($s_result as $s_data) {
                $data['store_ids'][] = $s_data['store_id'];
            }

            foreach ($data['store_ids'] as $key => $id) {
                if (!in_array($id, $storeIds)) {
                    unset($data['store_ids'][$key]);
                }
            }

            if (empty($data['store_ids']) || in_array(0, $data['store_ids'])) {
                $data['store_ids'] = 0;
            }

            /* Prepare post data */
            $data = [
                'old_id' => $data['post_id'],
                'store_ids' => $data['store_ids'],
                'title' => $data['title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
                'identifier' => $data['identifier'],
                'content_heading' => '',
                'content' => str_replace('<!--more-->', '<!-- pagebreak -->', $data['post_content']),
                'short_content' => $data['short_content'],
                'creation_time' => strtotime((string)$data['created_time']),
                'update_time' => strtotime((string)$data['update_time']),
                'publish_time' => strtotime((string)$data['created_time']),
                'is_active' => (int)($data['status'] == 1),
                'categories' => $postCategories,
                'featured_img' => !empty($data['featured_image']) ? 'magefan_blog/' . $data['featured_image'] : '',
            ];
            $data['identifier'] = trim(strtolower($data['identifier']));

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();

                /* find post comment s*/
                $select = $connection->select();
                $select->from($_pref . 'aw_blog_comment')
                    ->where('post_id = ?', $post->getOldId());

                $resultComments = $connection->fetchAll($select);
                foreach ($resultComments as $comments) {
                    $commentParentId = 0;

                    $commentData = [
                        'parent_id' => $commentParentId,
                        'post_id' => $post->getPostId(),
                        'status' => ($comments['status'] == 2) ?
                            \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED :
                            \Magefan\Blog\Model\Config\Source\CommentStatus::NOT_APPROVED,
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['user'],
                        'author_email' => $comments['email'],
                        'text' => $comments['comment'],
                        'creation_time' => $comments['created_time'],
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
