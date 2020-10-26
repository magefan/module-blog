<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Setup;

use Magefan\Blog\Model\Post;
use Magefan\Blog\Model\PostFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Post factory
     *
     * @var \Magefan\Blog\Model\PostFactory
     */
    private $_postFactory;

    /**
     * State
     *
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Init
     *
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     */
    public function __construct(
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_postFactory = $postFactory;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            /* Do nothing, it's OK */
        }

        $url =  $this->scopeConfig
            ->getValue(
                'web/unsecure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                0
            );
        $useLinks = \Magefan\Community\Model\UrlChecker::showUrl($url);

        $data = [
            'title' => 'Hello world!',
            'meta_keywords' => 'magento 2 blog',
            'meta_description' => 'Magento 2 blog default post.',
            'identifier' => 'hello-world',
            'content_heading' => 'Hello world!',
            'content' =>
                $useLinks
                ? '<p>Welcome to 
                    <a title="Magento Blog" 
                       href="https://magefan.com/magento2-blog-extension" 
                       target="_blank">Magento Blog</a> by
                    <a title="Magento 2 Extensions" 
                       href="https://magefan.com/magento2-extensions"
                       target="_blank">Magefan</a>. 
                       This is your first post. Edit or delete it, then start blogging!
                </p>
                <p><!-- pagebreak --></p>
                <p>Please also read&nbsp;
                    <a title="Magento 2 Blog online documentation" 
                       href="https://magefan.com/blog/magento-2-blog-extension-documentation" 
                       target="_blank">Online documentation</a>&nbsp;and&nbsp;
                    <a href="https://magefan.com/blog/add-read-more-tag-to-blog-post-content" 
                       target="_blank">How to add "read more" tag to post content</a>
                </p>
                <p>Follow Magefan on:</p>
                <p>
                    <a title="Blog Extension for Magento 2" 
                       href="https://github.com/magefan/module-blog" 
                       target="_blank">GitHub</a>&nbsp;|&nbsp;
                    <a href="https://twitter.com/magento2fan" 
                       target="_blank">Twitter</a>&nbsp;|&nbsp;
                    <a href="https://www.facebook.com/magefan/" 
                       target="_blank">Facebook</a>
                </p>'
                : '<p>Welcome to Magento 2 Blog extension by Magefan.
                        This is your first post. Edit or delete it, then start blogging!
                </p>',
            'store_ids' => [0]
        ];

        $this->_postFactory->create()->setData($data)->save();
    }
}
