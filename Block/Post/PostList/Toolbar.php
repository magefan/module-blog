<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Post\PostList;

use Magefan\Blog\Model\Config;
use Magento\Framework\View\Element\Template\Context;

/**
 * Blog posts list toolbar
 */
class Toolbar extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Config|null
     */
    private $config;

    /**
     * @param Context $context
     * @param array $data
     * @param Config|null $config
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?Config $config = null
    ) {
        parent::__construct($context, $data);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->config = $config ?: $objectManager->create(Config::class);
    }

    /**
     * Page GET parameter name
     */
    const PAGE_PARM_NAME = 'page';

    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * Default block template
     * @var string
     */
    protected $_template = 'post/list/toolbar.phtml';

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection): static
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }

    /**
     * Return products collection instance
     *
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get specified posts limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->getData('limit') ?: $this->_scopeConfig->getValue(
            'mfblog/post_list/posts_per_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        $page = (int) $this->_request->getParam($this->getPageParamName());
        return $page ? $page : 1;
    }

    /**
     * @return bool|\Magento\Framework\DataObject|\Magento\Framework\View\Element\AbstractBlock|\Magento\Theme\Block\Html\Pager
     */
    public function getPagerBlock(): \Magento\Framework\DataObject|false
    {
        $pagerBlock = $this->getChildBlock('post_list_toolbar_pager');
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setPageVarName(
                $this->getPageParamName()
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );
        } else {
            $pagerBlock = false;
        }


        return $pagerBlock;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getPagerBlock();
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPageParamName(): string
    {
        return $this->config->getPagePaginationType() !== 'p' ? 'page' : 'p';
    }
}
