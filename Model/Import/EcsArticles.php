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
                    t.meta_description as meta_description,
                    t.url_key as identifier
                FROM '.$_pref.'ecs_articles_author t';

        $result = $adapter->query($sql)->execute();
        foreach ($result as $data) {

            if (!$data['firstname'] || !$data['lastname'] || !$data['old_id']) {
                continue;
            }

            $data['firstname'] = trim($data['firstname']);
            $data['lastname'] = trim($data['lastname']);

            $data['featured_img'] = 'magefan_blogauthor' . $data['featured_img'];

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

            /* Find post video*/
            $postVideos = '';
            $c_sql = 'SELECT * FROM '.
                $_pref.'ecs_articles_video WHERE entity_id = "'.$data['video_id'].'"';

            $c_result = $adapter->query($c_sql)->execute();
            foreach ($c_result as $c_data) {
                $prepareUrl = '';
                if (isset($c_data['entity_id'])) {
                    if (strpos($c_data['url'], 'vimeo')) {
                        $videoId = preg_replace( '/[^0-9]/', '', $c_data['url']);
                        $prepareUrl = 'https://player.vimeo.com/video/' . $videoId;
                        $videoId = '';

                    }elseif (strpos($c_data['url'], 'youtube')){
                        parse_str( parse_url( $c_data['url'], PHP_URL_QUERY ), $videoId );
                        $prepareUrl = 'https://www.youtube.com/embed/' . $videoId['v'];
                        $videoId['v'] = '';
                    }

                    $postVideos .= '<iframe 
                        src="' . $prepareUrl . '" 
                        title="' . $c_data['title'] . '"
                        width="640" 
                        height="360" 
                        frameborder="0" 
                        ></iframe> <br>';
                }
            }

            /* Find post related product*/
            $c_sql = 'SELECT * FROM '. $_pref.'ecs_articles_article_product tr
                    LEFT JOIN '.$_pref.'catalog_product_entity tt ON tr.product_id = tt.entity_id
                    WHERE article_id = "'.$data['entity_id'].'"';

            $c_result = $adapter->query($c_sql)->execute();
            $postProducts = [];
            foreach ($c_result as $c_data) {
                if (isset($c_data['sku'])) {
                    try {
                        $product = $this->productRepository->get($c_data['sku']);
                        $postProducts[$product->getId()] = $product->getId();
                    } catch (\Exception $e) {

                    }
                }
            }

            if ($data['status'] > 0) {
                $data['status'] = 1;
            }

            if (!array_key_exists('store_ids', $data)) {
                $data['store_ids'] = 0;
            }

            if (!empty($data['image'])) {
                $imagePost = 'magefan_blog' . $data['image'];
            }elseif (!empty($data['highlight_image'])){
                $imagePost = 'magefan_blog' . $data['highlight_image'];
            }else {
                $imagePost = '';
            }
            /* Prepare post data */
            $data = [
                'old_id' => $data['entity_id'],
                'store_ids' => $data['store_ids'],
                'title' => $data['title'],
                'meta_title' => $data['meta_title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
                'identifier' => $data['url_key'],
                'content_heading' => '',
                'content' => $postVideos . $data['content'],
                'short_content' => $data['summary'],
                'creation_time' => $data['created_at'],
                'update_time' => $data['updated_at'],
                'publish_time' => $data['published_at'],
                'is_active' => (int)($data['status'] == 1),
                'categories' => $postCategories,
                'author_id' => $data['author_id'],
                'tags' => $postTags,
                'featured_img' => $imagePost
            ];

            if (count($postProducts)) {
                $data['links']['product'] = $postProducts;
            }

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
