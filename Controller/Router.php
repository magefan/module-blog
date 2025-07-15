<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller;

use Magefan\Blog\Model\Url;
use Magefan\Blog\Api\UrlResolverInterface;
use Magefan\Blog\Model\Config;

/**
 * Blog Controller Router
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * Category factory
     *
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Author factory
     *
     * @var   \Magefan\Blog\Api\AuthorInterfaceFactory
     */
    protected $_authorFactory;

    /**
     * Tag factory
     *
     * @var \Magefan\Blog\Model\TagFactory
     */
    protected $_tagFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var array;
     */
    protected $ids;

    /**
     * @var UrlResolverInterface
     */
    protected $urlResolver;

    /**
     * @var Config|mixed
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param \Magefan\Blog\Api\AuthorInterfaceFactory $authorFactory
     * @param \Magefan\Blog\Model\TagFactory $tagFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param UrlResolverInterface $urlResolver
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Url $url,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        \Magefan\Blog\Api\AuthorInterfaceFactory $authorFactory,
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        ?UrlResolverInterface $urlResolver = null,
        ?Config $config = null
    ) {

        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_postFactory = $postFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_authorFactory = $authorFactory;
        $this->_tagFactory = $tagFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->urlResolver = $urlResolver ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magefan\Blog\Api\UrlResolverInterface::class
        );
        $this->config = $config ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            Config::class
        );
    }

    /**
     * Validate and Match Blog Pages and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            return null;
        }

        $pathInfo = $request->getPathInfo();
        $_identifier = trim($pathInfo, '/');
        $blogPage = $this->urlResolver->resolve($_identifier);
        if (!$blogPage || empty($blogPage['type']) || (empty($blogPage['id']) && $blogPage['type'] != Url::CONTROLLER_SEARCH)) {
            return null;
        }

        switch ($blogPage['type']) {
            case Url::CONTROLLER_INDEX:
                $blogPage['type'] = 'index';
                $actionName = 'index';
                $idKey = null;
                break;

            case Url::CONTROLLER_RSS:
                $actionName = 'feed';
                $idKey = null;
                break;
            case Url::CONTROLLER_SEARCH:
                $actionName = 'index';
                $idKey = 'q';
                break;
            case Url::CONTROLLER_ARCHIVE:
                $actionName = 'view';
                $idKey = 'date';
                break;
            default:
                $actionName = 'view';
                $idKey = 'id';
        }

        $request
            ->setRouteName('blog')
            ->setControllerName($blogPage['type'])
            ->setActionName($actionName);

        if ($idKey) {
            $request->setParam($idKey, $blogPage['id']);
        }

        if (!empty($blogPage['params']) && is_array($blogPage['params'])) {
            foreach ($blogPage['params'] as $k => $v) {
                $request->setParam($k, $v);
            }
        }

        $condition = new \Magento\Framework\DataObject(
            [
                'identifier' => $_identifier,
                'request' => $request,
                'continue' => true
            ]
        );

        $this->_eventManager->dispatch(
            'magefan_blog_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create(
                \Magento\Framework\App\Action\Redirect::class,
                ['request' => $request]
            );
        }

        if (!$condition->getContinue()) {
            return null;
        }

        if (!$request->getModuleName()) {
            return null;
        }

        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, ltrim($pathInfo, '/'));

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class,
            ['request' => $request]
        );
    }
}
