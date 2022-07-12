<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Cron;

use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magefan\Blog\Model\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ReSaveExistingPosts
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PostCollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @param Config $config
     * @param PostCollectionFactory $postCollectionFactory
     * @param DateTime $date
     */
    public function __construct(
        Config $config,
        PostCollectionFactory $postCollectionFactory,
        DateTime $date
    )
    {
        $this->config = $config;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->date = $date;
    }


    public function execute()
    {

        $postCollection = $this->postCollectionFactory->create()
            ->addFieldToFilter('publish_time', array('gteq' => $this->date->gmtDate('Y-m-d H:i:s', strtotime('-2 minutes'))))
            ->addFieldToFilter('publish_time', array('lteq' => $this->date->gmtDate()));

        var_dump($this->date->gmtDate());
        var_dump(count($postCollection));

        foreach ($postCollection as $post) {
              var_dump($post->getId());exit;
        }
    }
}