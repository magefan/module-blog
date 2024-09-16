<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml;

/**
 * Abstract admin controller
 */
abstract class Actions extends \Magefan\Community\Controller\Adminhtml\Actions
{
    /**
     * Prepare images before object save
     * @param $model
     * @param array $fields
     */
    protected function prepareImagesBeforeSave($model, array $fields)
    {
        $data = $model->getData();
        foreach ($fields as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                if (!empty($data[$key]['delete'])) {
                    $model->setData($key, null);
                } else {
                    if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                        $image = $data[$key][0]['name'];

                        $imageUploader = $this->_objectManager->get(
                            \Magefan\Blog\ImageUpload::class
                        );
                        $image = $imageUploader->moveFileFromTmp($image, true);

                        $model->setData($key, $image);
                    } else {
                        if (isset($data[$key][0]['url']) && false !== strpos($data[$key][0]['url'], '/media/')) {
                            $url = $data[$key][0]['url'];

                            /**
                             *    $url may have two types of values
                             *    /media/.renditions/magefan_blog/a.png
                             *    http://domain.com/media/magefan_blog/tmp/a.png
                             */

                            $keyString = strpos($url, '/.renditions/') !== false ? '/.renditions/' : '/media/';
                            $position = strpos($url, $keyString);

                            $model->setData($key, substr($url, $position + strlen($keyString)));

                        } elseif (isset($data[$key][0]['name'])) {
                            $model->setData($key, $data[$key][0]['name']);
                        }
                    }
                }
            } else {
                $model->setData($key, null);
            }
        }
    }
}
