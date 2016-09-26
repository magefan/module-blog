<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Helper;

use Magento\Framework\App\Action\Action;

/**
 * Magefan Blog Helper
 */
class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_design = $design;
        $this->_localeDate = $localeDate;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Return result blog page
     *
     * @param Action $action
     * @param \Magento\Framework\Model\AbstractModel $page
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultPage(Action $action, $page)
    {
        if ($page->getCustomThemeFrom() && $page->getCustomThemeTo()) {
            $inRange = $this->_localeDate->isScopeDateInInterval(
                null,
                $page->getCustomThemeFrom(),
                $page->getCustomThemeTo()
            );
        } else {
            $inRange = false;
        }

        if ($page->getCustomTheme()) {
            if ($inRange) {
                $this->_design->setDesignTheme($page->getCustomTheme());
            }
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($inRange
            && $page->getCustomLayout()
            && $page->getCustomLayout() != 'empty'
        ) {
            $handle = $page->getCustomLayout();
        } else {
            $handle = $page->getPageLayout();
        }
        if ($handle) {
            $resultPage->getConfig()->setPageLayout($handle);
        }

        $fullActionName = $action->getRequest()->getFullActionName();
        $resultPage->addHandle($fullActionName);
        $resultPage->addPageLayoutHandles(['id' => $page->getIdentifier()]);

        $this->_eventManager->dispatch(
            $fullActionName . '_render',
            ['page' => $page, 'controller_action' => $action]
        );

        if ($inRange && $page->getCustomLayoutUpdateXml()) {
            $layoutUpdate = $page->getCustomLayoutUpdateXml();
        } else {
            $layoutUpdate = $page->getLayoutUpdateXml();
        }
        if ($layoutUpdate) {
            $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }

        return $resultPage;
    }

}
