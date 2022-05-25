<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Magefan\Blog\Model\Config;
use Magento\Theme\Block\Html\Header\Logo;

/**
 * Disable page cache in preview mode
 */
class LayoutLoadBeforeObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $config;

    /**
     * LayoutLoadBeforeObserver constructor.
     * @param \Magento\Framework\Registry $registry
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        RequestInterface $request,
        Config $config
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Page block html topmenu gethtml before
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->isEnabled()) {
            $post = $this->registry->registry('current_blog_post');
            $layout = $observer->getLayout();
            if ($post && $post->getIsPreviewMode()) {
                $layout->getUpdate()->addHandle('blog_non_cacheable');
            }
            if (!$this->config->isBlogCssIncludeOnAll()) {
                if ($this->config->isBlogCssIncludeOnHome() && $this->request->getFullActionName() === 'cms_index_index') {
                    $layout->getUpdate()->addHandle('blog_css');
                }

                if ($this->config->isBlogCssIncludeOnProduct() && $this->request->getFullActionName() === 'catalog_product_view') {
                    $layout->getUpdate()->addHandle('blog_css');
                }
            } else {
                $layout->getUpdate()->addHandle('blog_css');
            }
        }
    }
}
