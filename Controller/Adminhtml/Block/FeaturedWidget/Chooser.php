<?php

namespace Magefan\Blog\Controller\Adminhtml\Block\FeaturedWidget;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;

class Chooser extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var
     */
    protected $registry;

    /**
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(Context $context, LayoutFactory $layoutFactory, RawFactory $resultRawFactory,Registry $registry)
    {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $selectedProducts = $this->getRequest()->getParam('selected_products');
        if ($selectedProducts !== null) {
            $this->registry->register('selected_products',$selectedProducts);
        }
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->layoutFactory->create();

        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $layout->createBlock(
            \Magefan\Blog\Block\Adminhtml\Widget\FeaturedWidgetChooser::class,
            '',
            ['data' => ['id' => $uniqId]]
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($pagesGrid->toHtml());
        return $resultRaw;
    }
}