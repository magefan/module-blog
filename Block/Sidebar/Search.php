<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar categories block
 */
class Search extends \Magento\Framework\View\Element\Template
{
    use Widget;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magefan\Blog\Model\Url $url
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_url = $url;
    }

    /**
     * @var string
     */
    protected $_widgetKey = 'search';

    /**
     * Retrieve query
     * @return string
     */
    public function getQuery(): string
    {
        return urldecode($this->getRequest()->getParam('q', ''));
    }

    /**
     * Retrieve serch form action url
     * @return string
     */
    public function getFormUrl()
    {
        return $this->_url->getUrl('', \Magefan\Blog\Model\Url::CONTROLLER_SEARCH);
    }
}
