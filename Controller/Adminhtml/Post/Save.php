<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
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
     * Before model save
     * @param  \Magefan\Blog\Model\Post $model
     * @param  \Magento\Framework\App\Request\Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        /* Prepare dates */
        $dateFilter = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Filter\Date');
        $data = $model->getData();

        $filterRules = [];
        foreach (['publish_time', 'custom_theme_from', 'custom_theme_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $dateFilter;
            }
        }

        $inputFilter = new \Zend_Filter_Input(
            $filterRules,
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        $model->setData($data);

        /* Prepare author */
        if (!$model->getAuthorId()) {
            $authSession = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
            $model->setAuthorId($authSession->getUser()->getId());
        }

        /* Prepare relative links */
        $data = $request->getPost('data');
        $links = isset($data['links']) ? $data['links'] : null;
        if ($links && is_array($links)) {
            foreach (['post', 'product'] as $linkType) {
                if (!empty($links[$linkType]) && is_array($links[$linkType])) {
                    $linksData = [];
                    foreach ($links[$linkType] as $item) {
                        $linksData[$item['id']] = [
                            'position' => $item['position']
                        ];
                    }
                    $links[$linkType] = $linksData;
                }
            }
            $model->setData('links', $links);
        }

        /* Prepare images */
        $data = $model->getData();
        foreach (['featured_img', 'og_img'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                if (!empty($data[$key]['delete'])) {
                    $model->setData($key, null);
                } else {
                    if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                        $image = $data[$key][0]['name'];
                        $model->setData($key, Post::BASE_MEDIA_PATH . DIRECTORY_SEPARATOR . $image);
                        $imageUploader = $this->_objectManager->get(
                            'Magefan\Blog\ImageUpload'
                        );
                        $imageUploader->moveFileFromTmp($image);
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
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
            $gallery = array();
            foreach ($images as $image) {
                if (empty($image['removed'])) {
                    if (!empty($image['value_id'])) {
                        $gallery[] = $image['value_id'];
                    } else {
                        $imageUploader = $this->_objectManager->get(
                            'Magefan\Blog\ImageUpload'
                        );
                        $imageUploader->moveFileFromTmp($image['file']);
                        $gallery[] = Post::BASE_MEDIA_PATH . DIRECTORY_SEPARATOR . $image['file'];
                    }
                }
            }

            $model->setGalleryImages($gallery);

        }
    }

}
