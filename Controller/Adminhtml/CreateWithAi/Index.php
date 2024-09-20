<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller\Adminhtml\CreateWithAi;

use Magefan\Blog\Model\Config;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magefan_Blog::post_save';
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Config $config,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if (!$this->config->getConfig('mfblog/ai_writer/enabled_ai_writer')) {
            $this->messageManager->addErrorMessage(__('AI Writer is not enabled. Please enable it first.'));
            $this->_redirect('adminhtml/system_config/edit/section/mfblog');
            return $this->_response;
        }

        if (!$this->config->getConfig('mfblog/ai_writer/chat_gpt_api_key')) {
            $this->messageManager->addErrorMessage(__('Chat-GPT API Key is missing in "AI Writer" configurations.'));
            $this->_redirect('adminhtml/system_config/edit/section/mfblog');
            return $this->_response;
        }

        $pageFactory =  $this->resultPageFactory->create();
        $pageFactory->getConfig()->getTitle()->set(
            'New Post With AI'
        );
        return $pageFactory;
    }
}
