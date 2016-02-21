<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Post;

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
        $dateFilter = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Framework\Stdlib\DateTime\Filter\Date');
        $data = $model->getData();

        $inputFilter = new \Zend_Filter_Input(
            ['publish_time' => $dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        $model->setData($data);

        if ($links = $request->getPost('links')) {

            $jsHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magento\Backend\Helper\Js');

            $links = is_array($links) ? $links : [];
            $linkTypes = ['relatedposts', 'relatedproducts'];
            foreach ($linkTypes as $type) {

                if (isset($links[$type])) {
                    $links[$type] = $jsHelper->decodeGridSerializedInput($links[$type]);

                    $model->setData($type.'_links', $links[$type]);
                }
            }
        }
    }

}
