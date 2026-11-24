<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Controller\Rss;

use Magefan\Blog\Model\Config;
use Magento\Framework\App\Action\Context;

/**
 * Blog rss feed view
 */
class Feed extends \Magefan\Blog\App\Action\Action
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Executes the RSS feed generation process if the module and RSS feed are enabled.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->moduleEnabled() || !$this->config->isRssFeedEnabled()) {
            return $this->_forwardNoroute();
        }

        $this->_view->loadLayout();
        $this->getResponse()
            ->setHeader('Content-type', 'text/xml; charset=UTF-8')
            ->setBody(
                $this->_view->getLayout()->getBlock('blog.rss.feed')->toHtml()
            );
    }
}
