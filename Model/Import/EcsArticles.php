<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

/**
 * Articles import model
 */
class EcsArticles extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
        $adapter = $this->getDbAdapter();

        $_pref = $this->getPrefix();

        $sql = 'SELECT * FROM '.$_pref.'ecs_articles_category LIMIT 1';

        try {
            $adapter->query($sql)->execute();
        } catch (\Exception $e) {
            throw new \Exception(__('Articles Blog Extension not detected.'), 1);
        }

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.entity_id as old_id,
                    t.name as title,
                    t.topmenu as include_in_menu,
                    t.topmenu_position as position,
                    t.url_key as identyfier,
                    t.status as is_active, 
                    t.meta_title as meta_title,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description
                FROM '.$_pref.'ecs_articles_category t';

        $result = $adapter->query($sql)->execute();

        foreach ($result as $data) {
            /* Prepare category data */

            if (array_key_exists('store_ids', $data)) {
                $data['store_ids'] = 0;
            }

            $data['is_active'] = 1;
            $data['path'] = 0;
            if ($data['is_active'] !== 0 && $data['is_active'] !== NULL){
                $data['is_active'] = 1;
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

        $sql = 'SELECT
                    t.entity_id as old_id,
                    t.tag as title,
                    t.status as is_active
                FROM '.$_pref.'ecs_articles_tag t';

        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {

            if (!$data['title']) {
                continue;
            }

            $data['title'] = trim($data['title']);

            if (is_numeric($data['title'])){
                $data['title'] = 't' . $data['title'];
            }

            try {
                /* Initial saving */
                if (!isset($existingTags[$data['title']])) {
                    $tag = $this->_tagFactory->create();
                    $tag->setData($data);

                    $currentTag = $tag->getCollection()
                        ->addFieldToFilter('title', $tag->getTitle())
                        ->setPageSize(1)
                        ->getFirstItem();
                    if ($currentTag->getId()) {
                        $tag = $currentTag;
                    } else {
                        $tag->save();
                    }

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

        /* Import authors */
        $authors = [];
        $oldAuthors = [];

        $sql = 'SELECT
                    t.entity_id as old_id,
                    t.first_name as firstname,
                    t.last_name as lastname,
                    t.description as content,
                    t.picture as featured_img,
                    t.status as is_active,
                    t.meta_title as meta_title,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description
                FROM '.$_pref.'ecs_articles_author t';

        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {

            if (!$data['firstname'] || !$data['lastname'] || !$data['old_id']) {
                continue;
            }

            $data['firstname'] = trim($data['firstname']);
            $data['lastname'] = trim($data['lastname']);


            try {
                $author = $this->_authorFactory->create();
                $author->setData($data);
                $author->save();
                $this->_importedAuthorsCount++;
                $authors[$author->getId()] = $author;
                $oldAuthors[$author->getOldId()] = $author;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_skippedAuthors[] = $data['firstname'] . ' ' . $data['lastname'];
                $this->_logger->debug('Blog Author Import [' . $data['firstname'] . ' ' . $data['lastname'] . ']: '. $e->getMessage());
            }
        }

        /* Import posts */
        $sql = 'SELECT * FROM '.$_pref.'ecs_articles_article';
        $result = $adapter->query($sql)->execute();

        foreach ($result as $data) {

            /* Find post categories*/
            $postCategories = [];
            $c_sql = 'SELECT category_id  FROM '.
                $_pref.'ecs_articles_article_category WHERE article_id = "'.$data['entity_id'].'"';

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
            $c_sql = 'SELECT tag_id  FROM '.
                $_pref.'ecs_articles_article_tag WHERE article_id = "'.$data['entity_id'].'"';

            $c_result = $adapter->query($c_sql)->execute();
            foreach ($c_result as $c_data) {
                $oldId = $c_data['tag_id'];
                if (isset($oldTags[$oldId])) {
                    $id = $oldTags[$oldId]->getId();
                    $postTags[$id] = $id;
                }
            }

            /* Find post author */
            $oldAuthorId = $data['author_id'];
            if (isset($oldAuthors[$oldAuthorId])) {
                $data['author_id'] = $oldAuthors[$oldAuthorId]->getId();
            } else {
                $data['author_id'] = null;
            }


            if ($data['status'] > 0) {
                $data['status'] = 1;
            }

            if (!array_key_exists('store_ids', $data)) {
                $data['store_ids'] = 0;
            }

            /* Prepare post data */
            $postDate = $this->date->gmtDate();
            $data = [
                'old_id' => $data['entity_id'],
                'store_ids' => $data['store_ids'],
                'title' => $data['title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
                'content_heading' => '',
                'content' => str_replace('<!--more-->', '<!-- pagebreak -->', $data['content']),
                'short_content' => $data['summary'],
                'creation_time' => $postDate,/*strtotime($data['created_at'])*/
                'update_time' => $postDate,/*strtotime($data['updated_at'])*/
                'publish_time' => $postDate,/*strtotime($data['published_at'])*/
                'is_active' => (int)($data['status'] == 1),
                'categories' => $postCategories,
                'author_id' => $data['author_id'],
                'tags' => $postTags,
                'featured_img' => !empty($data['photo_credits']) ? 'magefan_blog/' . $data['photo_credits'] : '',
            ];

            $post = $this->_postFactory->create();
            try {
                /* Post saving */
                $post->setData($data)->save();

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
