<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\Blog\Model;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class SlashUrlRedirect Model
 */
class SlashUrlRedirect
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\HttpInterface
     */
    protected $response;

    /**
     * NoSlashUrlRedirect constructor.
     * @param UrlInterface $urlInterface
     * @param ActionFlag $actionFlag
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface|null $request
     * @param \Magento\Framework\App\ResponseInterface|null $response
     */
    public function __construct(
        UrlInterface $urlInterface,
        ActionFlag $actionFlag,
        ScopeConfigInterface $scopeConfig,
        ?\Magento\Framework\App\RequestInterface $request = null,
        ?\Magento\Framework\App\ResponseInterface $response = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->actionFlag = $actionFlag;
        $this->urlInterface = $urlInterface;
        $this->request = $request ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\RequestInterface::class
        );
        $this->response = $response ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\ResponseInterface::class
        );
    }

    /**
     * Executes observer to handle URL modifications and redirections
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $moduleEnabled = $this->scopeConfig->getValue(Config::XML_PATH_EXTENSION_ENABLED, ScopeInterface::SCOPE_STORE);

        if ($moduleEnabled) {
            $currentUrl = $this->urlInterface->getCurrentUrl();
            $result = explode('?', $currentUrl);

            $advancedPermalinkEnabled =  $this->scopeConfig->getValue(
                Config::XML_PATH_ADVANCED_PERMALINK_ENABLED,
                ScopeInterface::SCOPE_STORE
            );

            if (!$advancedPermalinkEnabled) {
                foreach ([
                    Url::CONTROLLER_POST,
                    Url::CONTROLLER_CATEGORY,
                    Url::CONTROLLER_AUTHOR,
                    Url::CONTROLLER_TAG
                ] as $controllerName) {

                    $controllerSufix = $this->scopeConfig->getValue(
                        'mfblog/permalink/' . $controllerName . '_sufix',
                        ScopeInterface::SCOPE_STORE
                    );
                    if ($controllerSufix) {
                        if (strpos($result[0], $controllerSufix) === strlen($result[0]) - strlen($controllerSufix)) {
                            return;
                        }
                    }
                }
            } else {
                $controllerSufix = '.html';
                if ($controllerSufix) {
                    if (strpos($result[0], $controllerSufix) === strlen($result[0]) - strlen($controllerSufix)) {
                        return;
                    }
                }
            }

            $result[0] = trim($result[0], '/') . '/';
            $urlSlash = implode('?', $result);

            if ($urlSlash != $currentUrl) {
                if ($this->request->isXmlHttpRequest()
                    || $this->request->isPost()
                ) {
                    return;
                }
                $this->actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                $this->response->setRedirect($urlSlash, 301)->sendResponse();
            }
        }
    }
}
