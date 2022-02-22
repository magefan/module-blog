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
        $useLinks = false;

        $data = [
            'title' => 'Magento 2 Blog Post Sample',
            'meta_keywords' => 'magento 2 blog sample',
            'meta_description' => 'Magento 2 blog default post.',
            'identifier' => 'magento-2-blog-post-sample',
            'content_heading' => 'Magento 2 Blog Post Sample',
            'content' =>
                $useLinks
                ? '<p>Welcome to 
                    <a title="Magento Blog" 
                       href="https://magefan.com/magento2-blog-extension" 
                       target="_blank">Magento Blog</a> by
                    <a title="Magento 2 Extensions" 
                       href="https://magefan.com/magento-2-extensions"
                       target="_blank">Magefan</a>. 
                       This is your first post. Edit or delete it, then start blogging!
                </p>
                <p><!-- pagebreak --></p>
                <p>Please also read&nbsp;
                    <a title="Magento 2 Blog online documentation" 
                       href="https://magefan.com/blog/magento-2-blog-extension-documentation" 
                       target="_blank">Magento 2 Blog online documentation</a>&nbsp;and&nbsp;
                    <a href="https://magefan.com/blog/add-read-more-tag-to-blog-post-content" 
                       target="_blank">How to add "read more" tag to post content</a>
                </p>
                <p>Follow Magefan on:</p>
                <p>
                    <a title="Magento 2 Blog Extension GitHub" 
                       href="https://github.com/magefan/module-blog" 
                       target="_blank">Magento 2 Blog Extension GitHub</a>&nbsp;|&nbsp;
                    <a href="https://twitter.com/magento2fan" title="Magefan at Twitter"
                       target="_blank">Magefan at Twitter</a>&nbsp;|&nbsp;
                    <a href="https://www.facebook.com/magefan/"  title="Magefan at Facebook"
                       target="_blank">Magefan at Facebook</a>
                </p>'
                : '<p>Welcome to Magento 2 Blog extension by Magefan.
                        This is your first post. Edit or delete it, then start blogging!
                </p>',
            'store_ids' => [0]
        ];

        $this->_postFactory->create()->setData($data)->save();
    }
}
