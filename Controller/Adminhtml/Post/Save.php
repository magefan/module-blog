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

        /* Prepare empty categories and coauthors */
        foreach (['categories', 'coauthors'] as $key) {
            if (!$request->getPost($key)) {
                $model->setData($key, []);
            }
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
        $this->prepareImagesBeforeSave($model, ['featured_img', 'featured_list_img', 'og_img']);

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
                        $image['file'] = $imageUploader->moveFileFromTmp($image['file'], true);
                        $gallery[] = $image['file'];
                    }
                }
            }

            $model->setGalleryImages($gallery);
        }

        /* Prepare Tags */
        $tagInput = trim((string)$request->getPost('tag_input'));
        if ($tagInput) {
            $tagInput = explode(',', $tagInput);

            $tagsCollection = $this->_objectManager->create(\Magefan\Blog\Model\ResourceModel\Tag\Collection::class);
            $allTags = [];
            foreach ($tagsCollection as $item) {
                if (!$item->getTitle()) {
                    continue;
                }
                $allTags[((string)$item->getTitle())] = $item->getId();
            }

            $tags = [];
            foreach ($tagInput as $tagTitle) {
                $tagTitle = trim((string)$tagTitle);
                if (!$tagTitle) {
                    continue;
                }
                if (empty($allTags[$tagTitle])) {
                    $tagModel = $this->_objectManager->create(\Magefan\Blog\Model\Tag::class);
                    $tagModel->setData('title', $tagTitle);
                    $tagModel->setData('is_active', 1);
                    $tagModel->save();

                    $tags[] = $tagModel->getId();
                } else {
                    $tags[] = $allTags[$tagTitle];
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
        $dateTimeFilter = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class);
        $dateFilter = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\Filter\Date::class);

        $filterRules = [];
        foreach (['publish_time', 'end_time', 'custom_theme_from', 'custom_theme_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $dateFilter;
                $data[$dateField] = preg_replace('/(.*)(\+\d\d\d\d\d\d)(\d\d)/U', '$1$3', $data[$dateField]);

                if (!preg_match('/\d{1}:\d{2}/', (string)$data[$dateField])) {
                    /*$data[$dateField] .= " 00:00";*/
                    $filterRules[$dateField] = $dateFilter;
                } else {
                    $filterRules[$dateField] = $dateTimeFilter;
                }
            }
        }

        $inputFilter = $this->getFilterInput(
            $filterRules,
            [],
            $data
        );

        $data = $inputFilter->getUnescaped();

        return $data;
    }
}
