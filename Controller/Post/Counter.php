<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Post;


class Counter extends \Magefan\Blog\Controller\Post\View
{

    protected $_postResource;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct(
            $context,
            $storeManager
        );
    }

    public function execute()
    {
        $post = parent::_initPost();
        $viewCounts = $post->getData('views_count');
        if($viewCounts){
            $viewCounts += 1;
            $post->setData('views_count',$viewCounts);
            $post->save();
        }
        var_dump( $post->getData('views_count'));exit();
    }
}
