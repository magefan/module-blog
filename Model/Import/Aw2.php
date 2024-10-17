<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Import;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Laminas\Db\Adapter\Adapter;

/**
 * Aw2 import model
 */
class Aw2 extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
        $adapter = $this->getDbAdapter();
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        $select = $connection->select()
            ->from($_pref . 'aw_blog_category')->limit(1);

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
            ->from(['t' => $_pref . 'aw_blog_category'],[
                'old_id' => 'id',
                'title' => 'name',
                'identifier' => 'url_key',
                'position' => 'sort_order',
                'meta_description' => 'meta_description'
            ])
            ->columns();

        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Prepare category data */

            /* Find store ids */
            $data['store_ids'] = [];

            $s_select = $connection->select()
                ->from($_pref . 'aw_blog_category_store', ['store_id'])
                ->where('category_id = ?', (int)$data['old_id']);

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
            ->from(['t' => $_pref . 'aw_blog_tag'], [
                'old_id' => 'id',
                'title' => 'name'
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

        $select = $connection->select();
        $select->from($_pref . 'aw_blog_post', ['*']);

        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Find post categories*/
            $postCategories = [];

            $c_select = $connection->select()
                ->from($_pref . 'aw_blog_post_category', ['category_id'])
                ->where('post_id = ?', (int)$data['id']);

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
                ->from($_pref . 'aw_blog_post_store', ['store_id'])
                ->where('post_id = ?', (int)$data['id']);

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
            if (!empty($data['featured_image_file'])) {
                $imgPath = explode('/', $data['featured_image_file']);
                $img = end($imgPath);
            }

            /* Prepare post data */
            $data = [
                'old_id' => $data['id'],
                'store_ids' => $data['store_ids'],
                'title' => $data['title'],
                'meta_description' => $data['meta_description'],
                'identifier' => $data['url_key'],
                'content_heading' => '',
                'content' => str_replace('<!--more-->', '<!-- pagebreak -->', $data['content']),
                'short_content' => $data['short_content'],
                'creation_time' => strtotime((string)$data['created_at']),
                'update_time' => strtotime((string)$data['updated_at']),
                'publish_time' => strtotime((string)$data['publish_date']),
                'is_active' => (int)($data['status'] == 'publication'),
                'categories' => $postCategories,
                'featured_img' => !empty($img) ? 'magefan_blog/' . $img : '',
            ];
            $data['identifier'] = trim(strtolower($data['identifier']));

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
