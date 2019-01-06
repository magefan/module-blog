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
 * Aw import model
 */
class Aw extends AbstractImport
{
    protected $_requiredFields = ['dbname', 'uname', 'dbhost'];

    public function execute()
    {
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
                $this->getData('dbhost'),
                $this->getData('uname'),
                $this->getData('pwd'),
                $this->getData('dbname')
            );
        }

        if (mysqli_connect_errno()) {
            throw new \Exception("Failed connect to magento database", 1);
        }

        mysqli_set_charset($con, "utf8");

        $_pref = mysqli_real_escape_string($con, $this->getData('prefix'));

        $sql = 'SELECT * FROM '.$_pref.'aw_blog_cat LIMIT 1';
        try {
            $this->_mysqliQuery($sql);
        } catch (\Exception $e) {
            throw new \Exception(__('AheadWorks Blog Extension not detected.'), 1);
        }

        $storeIds = array_keys($this->_storeManager->getStores(true));

        $categories = [];
        $oldCategories = [];

        /* Import categories */
        $sql = 'SELECT
                    t.cat_id as old_id,
                    t.title as title,
                    t.identifier as identifier,
                    t.sort_order as position,
                    t.meta_keywords as meta_keywords,
                    t.meta_description as meta_description
                FROM '.$_pref.'aw_blog_cat t';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare category data */

            /* Find store ids */
            $data['store_ids'] = [];
            $s_sql = 'SELECT store_id FROM '.$_pref.'aw_blog_cat_store WHERE cat_id = "'.$data['old_id'].'"';
            $s_result = $this->_mysqliQuery($s_sql);
            while ($s_data = mysqli_fetch_assoc($s_result)) {
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
                $this->_logger->addDebug('Blog Category Import [' . $data['title'] . ']: '. $e->getMessage());
            }
        }

        /* Import tags */
        $tags = [];
        $oldTags = [];
        $existingTags = [];

        $sql = 'SELECT
                    t.id as old_id,
                    t.tag as title
                FROM '.$_pref.'aw_blog_tags t';

        $result = $this->_mysqliQuery($sql);
        while ($data = mysqli_fetch_assoc($result)) {
            /* Prepare tag data */
            foreach (['title'] as $key) {
                $data[$key] = mb_convert_encoding($data[$key], 'HTML-ENTITIES', 'UTF-8');
            }

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
        $sql = 'SELECT * FROM '.$_pref.'aw_blog';
        $result = $this->_mysqliQuery($sql);

        while ($data = mysqli_fetch_assoc($result)) {
            /* Find post categories*/
            $postCategories = [];
            $c_sql = 'SELECT cat_id as category_id FROM '.$_pref.'aw_blog_post_cat WHERE post_id = "'.$data['post_id'].'"';
            $c_result = $this->_mysqliQuery($c_sql);
            while ($c_data = mysqli_fetch_assoc($c_result)) {
                $oldId = $c_data['category_id'];
                if (isset($oldCategories[$oldId])) {
                    $id = $oldCategories[$oldId]->getId();
                    $postCategories[$id] = $id;
                }
            }

            /* Find store ids */
            $data['store_ids'] = [];
            $s_sql = 'SELECT store_id FROM '.$_pref.'aw_blog_store WHERE post_id = "'.$data['post_id'].'"';
            $s_result = $this->_mysqliQuery($s_sql);
            while ($s_data = mysqli_fetch_assoc($s_result)) {
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
                'creation_time' => strtotime($data['created_time']),
                'update_time' => strtotime($data['update_time']),
                'publish_time' => strtotime($data['created_time']),
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
                $sql = 'SELECT * FROM '.$_pref.'aw_blog_comment WHERE `post_id` = ' . $post->getOldId();
                $resultComments = $this->_mysqliQuery($sql);

                while ($comments = mysqli_fetch_assoc($resultComments)) {
                    $commentParentId = 0;

                    $commentData = [
                        'parent_id' => $commentParentId,
                        'post_id' => $post->getPostId(),
                        'status' => ($comments['status'] == 2) ? \Magefan\Blog\Model\Config\Source\CommentStatus::APPROVED : \Magefan\Blog\Model\Config\Source\CommentStatus::NOT_APPROVED,
                        'author_type' => \Magefan\Blog\Model\Config\Source\AuthorType::GUEST,
                        'author_nickname' => $comments['user'],
                        'author_email' => $comments['email'],
                        'text' => $comments['comment'],
                        'creation_time' => $comments['created_time'],
                    ];

                    foreach (['text'] as $key) {
                        $commentData[$key] = mb_convert_encoding($commentData[$key], 'HTML-ENTITIES', 'UTF-8');
                    }

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
