<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magefan\Blog\Model\PostFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CreateSamplePost implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var PostFactory
     */
    private $_postFactory;

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
     * @param State $state
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PostFactory $postFactory,
        State $state,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_postFactory = $postFactory;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            /* Do nothing, it's OK */
        }

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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return[];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return[];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.2';
    }
}
