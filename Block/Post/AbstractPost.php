<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Abstract post мшуц block
 */
abstract class AbstractPost extends \Magento\Framework\View\Element\Template
{

    /**
     * Deprecated property. Do not use it.
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magefan\Blog\Model\Post
     */
    protected $_post;

    /**
     * Page factory
     *
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_defaultPostInfoBlock = \Magefan\Blog\Block\Post\Info::class;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * @var \Magefan\Blog\Model\Config
     */
    protected $config;

    /**
     * @var \Magefan\Blog\Model\TemplatePool
     */
    protected $templatePool;

    /**
     * AbstractPost constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magefan\Blog\Model\Post $post
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\Url $url
     * @param array $data
     * @param null $config
     * @param null $templatePool
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\Post $post,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\Url $url,
        array $data = [],
        $config = null,
        $templatePool = null
    ) {
        parent::__construct($context, $data);
        $this->_post = $post;
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_postFactory = $postFactory;
        $this->_url = $url;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->config = $config ?: $objectManager->get(
            \Magefan\Blog\Model\Config::class
        );
        $this->templatePool = $templatePool ?: $objectManager->get(
            \Magefan\Blog\Model\TemplatePool::class
        );
    }

    /**
     * Retrieve post instance
     *
     * @return \Magefan\Blog\Model\Post
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData(
                'post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Retrieve post short content
     *
     * @param  mixed $len
     * @param  mixed $endCharacters
     * @return string
     */
    public function getShorContent($len = null, $endCharacters = null)
    {
        return $this->getPost()->getShortFilteredContent($len, $endCharacters);
    }

    public function getShortFilteredContentWithoutImages($len = null, $endCharacters = null)
    {
        return $this->getPost()->getShortFilteredContentWithoutImages($len, $endCharacters);
    }

    /**
     * Retrieve post content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getPost()->getFilteredContent();
    }

    /**
     * Retrieve post info html
     *
     * @return string
     */
    public function getInfoHtml()
    {
        return $this->getInfoBlock()->toHtml();
    }

    /**
     * Retrieve post info block
     *
     * @return \Magefan\Blog\Block\Post\Info
     */
    public function getInfoBlock()
    {
        $k = 'info_block';
        if (!$this->hasData($k)) {
            $blockName = $this->getPostInfoBlockName();
            if ($blockName) {
                $block = $this->getLayout()->getBlock($blockName);
            }

            if (empty($block)) {
                $block = $this->getLayout()->createBlock($this->_defaultPostInfoBlock, uniqid(microtime()));
            }

            $this->setData($k, $block);
        }

        return $this->getData($k)->setPost($this->getPost());
    }

    /**
     * Retrieve 1 if display author information is enabled
     * @return int
     */
    public function authorEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 1 if author page is enabled
     * @return int
     */
    public function authorPageEnabled()
    {
        return (int) $this->_scopeConfig->getValue(
            'mfblog/author/page_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve true if magefan comments are enabled
     * @return bool
     */
    public function magefanCommentsEnabled()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == \Magefan\Blog\Model\Config\Source\CommetType::MAGEFAN;
    }

    /**
     * @return bool
     */
    public function viewsCountEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfblog/post_view/views_count/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magefan\Blog\ViewModel\Style
     */
    public function getStyleViewModel()
    {
        $viewModel = $this->getData('style_view_model');
        if (!$viewModel) {
            $viewModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\Blog\ViewModel\Style::class);
            $this->setData('style_view_model', $viewModel);
        }

        return $viewModel;
    }

    /**
     * Check if AddThis Enabled and key exist
     *
     * @return bool
     */
    public function displayAddThisToolbox()
    {
        $isSocialEnabled = $this->_scopeConfig->getValue(
            'mfblog/social/add_this_enabled',
            ScopeInterface::SCOPE_STORE
        );
        $isSocialIdExist = $this->_scopeConfig->getValue(
            'mfblog/social/add_this_pubid',
            ScopeInterface::SCOPE_STORE
        );

        return $isSocialEnabled && $isSocialIdExist;
    }

    /**
     * @return array
     */
    public function getAllowedSocialNetworks()
    {
        $socialNetworks = $this->_scopeConfig->getValue('mfblog/social/use_social_networks', ScopeInterface::SCOPE_STORE);
        return explode(',',$socialNetworks);
    }

    /**
     * @return array[]
     */
    public function getSocialNetworksSvg()
    {
        return [
            "Facebook" => [
            'svg' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" version="1.1" class="at-icon at-icon-facebook" style="fill: #ffffff;"><title id="at-svg-facebook-1">Share on Facebook</title><g><path d="M22 5.16c-.406-.054-1.806-.16-3.43-.16-3.4 0-5.733 1.825-5.733 5.17v2.882H9v3.913h3.837V27h4.604V16.965h3.823l.587-3.913h-4.41v-2.5c0-1.123.347-1.903 2.198-1.903H22V5.16z" fill-rule="evenodd"></path></g></svg>'
            ],
            "Twitter" => [
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -4 32 32" width="48" height="48" fill="none"><title id="at-svg-twitter-1">Share on Twitter</title><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" fill="#ffffff"></path></svg>'
            ],
            "Pinterest" => [
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" version="1.1" role="img" class="at-icon at-icon-pinterest" style="fill: rgb(255, 255, 255);"><title id="at-svg-pinterest_share-1">Share on Pinterest</title><g><path d="M7 13.252c0 1.81.772 4.45 2.895 5.045.074.014.178.04.252.04.49 0 .772-1.27.772-1.63 0-.428-1.174-1.34-1.174-3.123 0-3.705 3.028-6.33 6.947-6.33 3.37 0 5.863 1.782 5.863 5.058 0 2.446-1.054 7.035-4.468 7.035-1.232 0-2.286-.83-2.286-2.018 0-1.742 1.307-3.43 1.307-5.225 0-1.092-.67-1.977-1.916-1.977-1.692 0-2.732 1.77-2.732 3.165 0 .774.104 1.63.476 2.336-.683 2.736-2.08 6.814-2.08 9.633 0 .87.135 1.728.224 2.6l.134.137.207-.07c2.494-3.178 2.405-3.8 3.533-7.96.61 1.077 2.182 1.658 3.43 1.658 5.254 0 7.614-4.77 7.614-9.067C26 7.987 21.755 5 17.094 5 12.017 5 7 8.15 7 13.252z" fill-rule="evenodd"></path></g></svg>'
            ],
            "LinkedIn" => [
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48"><path fill="#0288D1" d="M42,37c0,2.762-2.238,5-5,5H11c-2.761,0-5-2.238-5-5V11c0-2.762,2.239-5,5-5h26c2.762,0,5,2.238,5,5V37z"/><title id="at-svg-twitter-2">Share on LinkedIn</title><path fill="#FFF" d="M12 19H17V36H12zM14.485 17h-.028C12.965 17 12 15.888 12 14.499 12 13.08 12.995 12 14.514 12c1.521 0 2.458 1.08 2.486 2.499C17 15.887 16.035 17 14.485 17zM36 36h-5v-9.099c0-2.198-1.225-3.698-3.192-3.698-1.501 0-2.313 1.012-2.707 1.99C24.957 25.543 25 26.511 25 27v9h-5V19h5v2.616C25.721 20.5 26.85 19 29.738 19c3.578 0 6.261 2.25 6.261 7.274L36 36 36 36z"/></svg>'
            ]
        ];
    }
}
