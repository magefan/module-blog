<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Observer;

use Magefan\Blog\Model\Config\Source\DesignVersion;
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
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if ($this->config->isEnabled()) {
            $entity = $this->registry->registry('current_blog_post')
                ?: $this->registry->registry('current_blog_category')
                    ?: $this->registry->registry('current_blog_tag')
                        ?:$this->registry->registry('current_blog_author');
            $layout = $observer->getLayout();
            if ($entity && $entity->getIsPreviewMode()) {
                $layout->getUpdate()->addHandle('blog_preview');
            }

            $designVersion = $this->config->getDesignVersion();

            if (!in_array($designVersion, [DesignVersion::INITIAL, DesignVersion::MODERN])) {
                $dvPrefix = str_replace('-', '_', $designVersion) . '_';
            } else {
                $dvPrefix = '';
            }

            if ($this->config->isBlogCssIncludeOnAll()
                || ($this->config->isBlogCssIncludeOnHome()
                    && $this->request->getFullActionName() === 'cms_index_index')
                || ($this->config->isBlogCssIncludeOnProduct()
                    && $this->request->getFullActionName() === 'catalog_product_view')
            ) {
                $layout->getUpdate()->addHandle('blog_' . $dvPrefix . 'css');
            }

            if (!in_array($designVersion, [DesignVersion::INITIAL, DesignVersion::MODERN])) {
                if ('blog' === $this->request->getModuleName()) {
                    $layout->getUpdate()->addHandle(
                        str_replace('blog_', 'blog_' . $dvPrefix, $this->request->getFullActionName())
                    );
                } elseif ('catalog_product_view' === $this->request->getFullActionName()) {
                    $layout->getUpdate()->addHandle(
                        str_replace('catalog_', 'catalog_' . $dvPrefix, $this->request->getFullActionName())
                    );
                }
            }
        }
    }
}
