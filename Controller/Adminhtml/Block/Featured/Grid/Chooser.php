<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\Block\Featured\Grid;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;

class Chooser extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Context       $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory    $resultRawFactory
     */
    public function __construct(Context $context, LayoutFactory $layoutFactory, RawFactory $resultRawFactory)
    {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute() : Raw
    {
        $layout = $this->layoutFactory->create();

        $uniqId = $this->getRequest()->getParam('uniq_id');

        if ($uniqId !== null) {
            $pagesGrid = $layout->createBlock(
                \Magefan\Blog\Block\Adminhtml\Widget\Featured\Grid::class,
                '',
                ['data' => ['id' => $uniqId]]
            );
        }
        else {
            $pagesGrid = $layout->createBlock(
                \Magefan\Blog\Block\Adminhtml\System\Config\Form\Featured\Grid::class,
                ''
            );
        }

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($pagesGrid->toHtml());
        return $resultRaw;
    }
}