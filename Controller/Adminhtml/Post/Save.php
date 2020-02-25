<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Post;

use Magefan\Blog\Model\Post;

/**
 * Blog post save controller
 */
class Save extends \Magefan\Blog\Controller\Adminhtml\Post
{
    /**
     * @var string
     */
    protected $_allowedKey = 'Magefan_Blog::post_save';

    /**
     * Before model save
     * @param  \Magefan\Blog\Model\Post $model
     * @param  \Magento\Framework\App\Request\Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        /* Prepare author */
        if (!$model->getAuthorId()) {
            $authSession = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
            $model->setAuthorId($authSession->getUser()->getId());
        }

        /* Prepare relative links */
        $data = $request->getPost('data');
        $links = isset($data['links']) ? $data['links'] : ['post' => [], 'product' => []];
        if (is_array($links)) {
            foreach (['post', 'product'] as $linkType) {
                if (isset($links[$linkType]) && is_array($links[$linkType])) {
                    $linksData = [];
                    foreach ($links[$linkType] as $item) {
                        $linksData[$item['id']] = [
                            'position' => isset($item['position']) ? $item['position'] : 0
                        ];
                    }
                    $links[$linkType] = $linksData;
                } else {
                    $links[$linkType] = [];
                }
            }
            $model->setData('links', $links);
        }

        /* Prepare images */
        $data = $model->getData();
        foreach (['featured_img', 'featured_list_img', 'og_img'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                if (!empty($data[$key]['delete'])) {
                    $model->setData($key, null);
                } else {
                    if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                        $image = $data[$key][0]['name'];

                        $imageUploader = $this->_objectManager->get(
                            \Magefan\Blog\ImageUpload::class
                        );
                        $image = $imageUploader->moveFileFromTmp($image);

                        $model->setData($key, Post::BASE_MEDIA_PATH . '/' . $image);
                    } else {
                        if (isset($data[$key][0]['name'])) {
                            $model->setData($key, $data[$key][0]['name']);
                        }
                    }
                }
            } else {
                $model->setData($key, null);
            }
        }

        /* Prepare Media Gallery */
        $data = $model->getData();

        if (!empty($data['media_gallery']['images'])) {
            $images = $data['media_gallery']['images'];
            usort($images, function ($imageA, $imageB) {
                if (!isset($imageA['position'])) {
                    $imageA['position'] = 0;
                }
                if (!isset($imageB['position'])) {
                    $imageB['position'] = 0;
                }
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
            $gallery = [];
            foreach ($images as $image) {
                if (empty($image['removed'])) {
                    if (!empty($image['value_id'])) {
                        $gallery[] = $image['value_id'];
                    } elseif (!empty($image['file'])) {
                        $imageUploader = $this->_objectManager->get(
                            \Magefan\Blog\ImageUpload::class
                        );
                        $image['file'] = $imageUploader->moveFileFromTmp($image['file']);
                        $gallery[] = Post::BASE_MEDIA_PATH . '/' . $image['file'];
                    }
                }
            }

            $model->setGalleryImages($gallery);
        }

        /* Prepare Tags */
        $tagInput = trim($request->getPost('tag_input'));
        if ($tagInput) {
            $tagInput = explode(',', $tagInput);

            $tagsCollection = $this->_objectManager->create(\Magefan\Blog\Model\ResourceModel\Tag\Collection::class);
            $allTags = [];
            foreach ($tagsCollection as $item) {
                $allTags[strtolower($item->getTitle())] = $item->getId();
            }

            $tags = [];
            foreach ($tagInput as $tagTitle) {
                if (empty($allTags[strtolower($tagTitle)])) {
                    $tagModel = $this->_objectManager->create(\Magefan\Blog\Model\Tag::class);
                    $tagModel->setData('title', $tagTitle);
                    $tagModel->setData('is_active', 1);
                    $tagModel->save();

                    $tags[] = $tagModel->getId();
                } else {
                    $tags[] = $allTags[strtolower($tagTitle)];
                }
            }
            $model->setData('tags', $tags);
        } else {
            $model->setData('tags', []);
        }
    }

    /**
     * Filter request params
     * @param  array $data
     * @return array
     */
    protected function filterParams($data)
    {
        /* Prepare dates */
        $dateFilter = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class);

        $filterRules = [];
        foreach (['publish_time', 'custom_theme_from', 'custom_theme_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $dateFilter;
                $data[$dateField] = preg_replace('/(.*)(\+\d\d\d\d\d\d)(\d\d)/U', '$1$3', $data[$dateField]);

                if (!preg_match('/\d{1}:\d{2}/', $data[$dateField])) {
                    $data[$dateField] .= " 00:00";
                }
            }
        }

        $inputFilter = new \Zend_Filter_Input(
            $filterRules,
            [],
            $data
        );

        $data = $inputFilter->getUnescaped();

        return $data;
    }
}
