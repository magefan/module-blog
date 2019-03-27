<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
        $config = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\DeploymentConfig');
        $pref = ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/';
        $this->setData(
            'dbhost',
            $config->get($pref . ConfigOptionsListConstants::KEY_HOST)
        )->setData(
            'uname',
            $config->get($pref . ConfigOptionsListConstants::KEY_USER)
        )->setData(
            'pwd',
            $config->get($pref . ConfigOptionsListConstants::KEY_PASSWORD)
        )->setData(
            'dbname',
            $config->get($pref . ConfigOptionsListConstants::KEY_NAME)
        );
        $host = $this->getData('dbhost') ?: $this->getData('host');
        if (false !== strpos($host, '.sock')) {
            $con = $this->_connect = mysqli_connect(
                'localhost',
                $this->getData('uname'),
                $this->getData('pwd'),
                $this->getData('dbname'),
                null,
                $host
            );
        } else {
            $con = $this->_connect = mysqli_connect(
                $host,
                $this->getData('uname'),
                $this->getData('pwd'),
                $this->getData('dbname')
            );
        }

        if (mysqli_connect_errno()) {
            throw new \Exception("Failed connect to magento database", 1);
        }

        $_pref = mysqli_real_escape_string(
            $con,
            $config->get('db/table_prefix')
        );

        $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_blog_category LIMIT 1';
        try {
            $this->_mysqliQuery($sql);
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
                    t.position as position,
                    t.meta_title as meta_title,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description,
                    t.description as content,
                    t.parent_id as parent_id,
                    t.position as position,
                    t.enabled as is_active,
                    t.store_ids as store_ids
                FROM ' . $_pref . 'mageplaza_blog_category t';
        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
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
                    t.tag_id as old_id,
                    t.name as title,
                    t.url_key as identifier, 
                    t.description as content,
                    t.meta_title as meta_title,
                    t.meta_description as meta_description,  
                    t.meta_keywords as meta_keywords,    
                    t.enabled as is_active
                FROM ' . $_pref . 'mageplaza_blog_tag t';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
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
                $this->_logger->addDebug('Blog Tag Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }


        /* Import posts */
        $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_blog_post';
        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Find post categories*/
            $postCategories = [];
            $c_sql = 'SELECT category_id FROM ' . $_pref . 'mageplaza_blog_post_category WHERE post_id = "'.$data['post_id'].'"';
            $c_result = $this->_mysqliQuery($c_sql);
            while ($c_data = mysqli_fetch_assoc($c_result)) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }

            /* Find post tags*/
            $postTags = [];
            $t_sql = 'SELECT tag_id FROM ' . $_pref . 'mageplaza_blog_post_tag WHERE post_id = "'.$data['post_id'].'"';

            $t_result = $this->_mysqliQuery($t_sql);

            while ($t_data = mysqli_fetch_assoc($t_result)) {
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
                'meta_title'        => $data['meta_title'],
                'meta_keywords'     => $data['meta_keywords'],
                'meta_description'  => $data['meta_description'],
                'identifier'        => $data['url_key'],
                'content_heading'   => '',
                'content'           => $data['post_content'],
                'short_content'     => $data['short_description'],
                'creation_time'     => strtotime($data['created_at']),
                'update_time'       => strtotime($data['updated_at']),
                'publish_time'      => strtotime($data['publish_date']),
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
                $sql = 'SELECT * FROM ' . $_pref . 'mageplaza_blog_comment WHERE `post_id` = ' . $post->getOldId();
                $resultComments = $this->_mysqliQuery($sql);

                while ($comments = mysqli_fetch_assoc($resultComments)) {
                    $commentData = [
                        'parent_id' => 0,
                        'post_id' => $post->getPostId(),
                        'status' => ($comments['status'] == 3) ? \Magefan\Blog\Model\Config\Source\CommentStatus::PENDING : $comments['status'],
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
        mysqli_close($con);
    }
}
