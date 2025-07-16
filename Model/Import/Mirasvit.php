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
 * Mirasvit import model
 */
class Mirasvit extends AbstractImport
{
    protected $pref;
    protected $entityTypeId = [];
    protected $entityTypeAttributes = [];

    public function execute()
    {
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        $select = $connection->select()
            ->from($_pref . 'mst_blog_post_entity')->limit(1);

        try {
            $connection->fetchAll($select);
        } catch (\Exception $e) {
            throw new \Exception(__('Mirasvit Blog Extension not detected.'), 1);
        }

        $categories = [];
        $oldCategories = [];

        /* Import categories */

        $select = $connection->select()
            ->from(['t' => $_pref . 'mst_blog_category_entity'], [
                'old_id' => 'entity_id',
                'position' => 'position',
                'parent_id' => 'parent_id'
            ]);
        $result = $connection->fetchAll($select);

        foreach ($result as $data) {
            /* Prepare category data */

            /* Get Stores */
            $data['store_ids'] = [0];
            $data['path'] = 0;

            $map = [
                //mirasvit_blog ->  magefan_blog
                'name' => 'title',
                'meta_title' => 'meta_title',
                'meta_keywords' => 'meta_keywords',
                'meta_description' => 'meta_description',
                'url_key' => 'identifier',
                'content' => 'content',
                'status' => 'is_active',
            ];

            foreach ($map as $msField => $mfField) {
                $data[$mfField] = $this->getAttributValue('blog_category', $data['old_id'], $msField);
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

        $select = $connection->select()
            ->from(['t' => $_pref . 'mst_blog_tag'], [
                'old_id' => 'tag_id',
                'title' => 'name',
                'identifier' => 'url_key',
                'is_active' => new \Zend_Db_Expr('1')
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
            ->from(['t' => $_pref . 'mst_blog_post_entity'], [
            'old_id' => 'entity_id',
                'author_id' => 'author_id',
                'creation_time' => 'created_at',
                'publish_time' => 'created_at',
                'update_time' => 'updated_at',
            ])
            ->where('type = ?', 'post');

        $result = $connection->fetchAll($select);


        foreach ($result as $data) {

            $map = [
                // mirasvit ->  blog magefan_blog
                //'featured_show_on_home' => '',
                //'featured_show_on_home' => '',
                'meta_description' => 'meta_description',
                'meta_keywords' => 'meta_keywords',
                'meta_title' => 'meta_title',
                'featured_image' => 'featured_img',
                'short_content' => 'short_content',
                'content' => 'content',
                'status' => 'is_active',
                'url_key' => 'identifier',
                'name' => 'title',
                //'is_pinned' => '',
            ];

            foreach ($map as $msField => $mfField) {
                $data[$mfField] = $this->getAttributValue('blog_post', $data['old_id'], $msField);
            }

            if ($data['is_active'] == 2) {
                $data['is_active'] = 1;
            } else {
                $data['is_active'] = 0;
            }

            if ($data['featured_img']) {
                $data['featured_img'] = 'magefan_blog/' . $data['featured_img'];
            }

            /* Find post categories*/
            $postCategories = [];

            $c_select = $connection->select()
                ->from(['c' => $_pref . 'mst_blog_category_post'], ['category_id'])
                ->where('post_id = ?', $data['old_id']);

            $c_result = $connection->fetchAll($c_select);

            foreach ($c_result as $c_data) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }
            $data['categories'] = $postCategories;

            /* Find post tags*/
            $postTags = [];

            $t_select = $connection->select()
                ->from(['tp' => $_pref . 'mst_blog_tag_post'], ['tag_id'])
                ->where('post_id = ?', $data['old_id']);

            $t_result = $connection->fetchAll($t_select);

            foreach ($t_result as $t_data) {
                $oldId = $t_data['tag_id'];
                if (isset($oldTags[$oldId])) {
                    $id = $oldTags[$oldId]->getId();
                    $postTags[$id] = $id;
                }
            }
            $data['tags'] = $postTags;

            /* Find post products*/
            $data['links'] = [];
            $postProducts = [];

            $t_select = $connection->select()
                ->from($_pref . 'mst_blog_post_product', ['product_id'])
                ->where('post_id = ?', $data['old_id']);

            $t_result = $connection->fetchAll($t_select);

            foreach ($t_result as $t_data) {
                $id = $t_data['product_id'];
                $postProducts[$id] = $id;
            }
            
            if (count($postProducts)) {
                $data['links']['product'] = $postProducts;
            }

            /* Find store ids */
            $storeIds = [];

            $select2 = $connection->select()
                ->from($_pref . 'mst_blog_store_post', ['store_id'])
                ->where('post_id = ?', $data['old_id']);

            $result2 = $connection->fetchAll($select2);
            foreach ($result2 as $data2) {
                $storeIds[] = $data2['store_id'];
            }
            $data['store_ids'] = $storeIds;

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
    }

    protected function getAttributValue($entitytTypeCode, $entitytId, $attributeCode)
    {
        $connection = $this->getDbConnection();
        $_pref = $this->getPrefix();

        if (!isset($this->entityTypeId[$entitytTypeCode])) {

            $select = $connection->select()
                ->from($_pref . 'eav_entity_type', ['entity_type_id'])
                ->where('entity_type_code = ?', $entitytTypeCode);

            $result = $connection->fetchAll($select);

            if (count($result)) {
                foreach ($result as $data) {
                    $this->entityTypeId[$entitytTypeCode] = $data['entity_type_id'];
                    break;
                }
            } else {
                $this->entityTypeId[$entitytTypeCode] = false;
            }
        }

        $entityTypeId = $this->entityTypeId[$entitytTypeCode];

        if (!$entityTypeId) {
            return null;
        }

        if (!isset($this->entityTypeAttributes[$entitytTypeCode])) {
            $this->entityTypeAttributes[$entitytTypeCode] = [];

            $select = $connection->select()
                ->from($_pref . 'eav_attribute')
                ->where('entity_type_id = ?', $entityTypeId);

            $result = $connection->fetchAll($select);

            foreach ($result as $data) {
                $this->entityTypeAttributes[$entitytTypeCode][$data['attribute_code']] = $data;
            }
        }

        if (empty($this->entityTypeAttributes[$entitytTypeCode][$attributeCode])) {
            return null;
        }

        $attribute = $this->entityTypeAttributes[$entitytTypeCode][$attributeCode];

        $select = $connection->select()
            ->from($_pref . 'mst_' . $entitytTypeCode . '_entity_' . $attribute['backend_type'], ['value'])
            ->where('store_id = ?', 0)
            ->where('attribute_id = ?', $attribute['attribute_id'])
            ->where('entity_id = ?', $entitytId);

        $result = $connection->fetchAll($select);

        if (count($result)) {
            foreach ($result as $data) {
                return $data['value'];
            }
        }
        return null;
    }
}
