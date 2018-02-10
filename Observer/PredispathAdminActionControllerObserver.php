<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Blog observer
 */
class PredispathAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var \Magefan\Blog\Model\AdminNotificationFeedFactory
     */
    protected $_feedFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \Magefan\Blog\Model\Comment\Notification
     */
    protected $commentNotification;

    /**
     * @param \Magefan\Blog\Model\AdminNotificationFeedFactory $feedFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magefan\Blog\Model\Comment\Notification $commentNotification,
     */
    public function __construct(
        \Magefan\Blog\Model\AdminNotificationFeedFactory $feedFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magefan\Blog\Model\Comment\Notification $commentNotification
    ) {
        $this->_feedFactory = $feedFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->commentNotification = $commentNotification;
    }

    /**
     * Predispath admin action controller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \Magefan\Blog\Model\AdminNotificationFeed */
            $feedModel->checkUpdate();

            /** Check pending blog comments */
            $this->commentNotification->checkComments();
        }
    }
}
