<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Block\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Post chooser controller
 */
class Chooser extends \Magento\Backend\App\Action
{

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, LayoutFactory $layoutFactory, RawFactory $resultRawFactory)
    {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Chooser Source action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Layout $layout */
        $layout = $this->layoutFactory->create();

        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $layout->createBlock(
            \Magefan\Blog\Block\Adminhtml\Block\Widget\Chooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($pagesGrid->toHtml());
        return $resultRaw;
    }
}
