<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magefan\Blog\Model\PostFactory;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CreateSamplePost implements DataPatchInterface
{
    /**
     * @var PostFactory
     */
    private $_postFactory;

    /**
     * @var PostCollectionFactory
     */
    private $postCollection;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param PostFactory $postFactory
     * @param PostCollectionFactory $postCollection
     * @param State $state
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PostFactory $postFactory,
        PostCollectionFactory $postCollection,
        State $state,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_postFactory = $postFactory;
        $this->postCollection = $postCollection;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    public function apply()
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            /* Do nothing, it's OK */
        }

        if (!$this->postCollection->create()->getSize()) {

            $data = [
                'title' => 'Magento 2 Blog Post Sample',
                'meta_keywords' => 'magento 2 blog sample',
                'meta_description' => 'Magento 2 blog default post.',
                'identifier' => 'magento-2-blog-post-sample',
                'content_heading' => 'Magento 2 Blog Post Sample',
                'content' => '<p>Welcome to Magento 2 Blog extension by Magefan. This is your first post. Edit or delete it, then start blogging!</p>',
                'store_ids' => [0]
            ];

            $this->_postFactory->create()->setData($data)->save();
        }
    }

    public static function getDependencies()
    {
        return[];
    }

    public function getAliases()
    {
        return[];
    }
}
