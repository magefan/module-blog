<?php
/**
 * Copyright © 2015-2017 Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller;

use \Magefan\Blog\Model\Url;

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
     * @var \Magefan\Blog\Model\AuthorFactory
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
     * @var array
     */
    protected $_postId;

    /**
     * @var array
     */
    protected $_categoryId;

    /**
     * @var int
     */
    protected $_authorId;

    /**
     * @var int
     */
    protected $_tagId;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     * @param \Magefan\Blog\Model\AuthorFactory $authorFactory
     * @param \Magefan\Blog\Model\TagFactory $tagFactory
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Url $url,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        \Magefan\Blog\Model\AuthorFactory $authorFactory,
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response
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
    }

    /**
     * Validate and Match Blog Pages and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $_identifier = trim($request->getPathInfo(), '/');
        $_identifier = urldecode($_identifier);

        $pathInfo = explode('/', $_identifier);
        $blogRoute = $this->_url->getRoute();

        if ($pathInfo[0] != $blogRoute) {
            return;
        }
        unset($pathInfo[0]);

        if (!count($pathInfo)) {
            $request
                ->setRouteName('blog')
                ->setControllerName('index')
                ->setActionName('index');
        } elseif ($pathInfo[1] == $this->_url->getRoute(Url::CONTROLLER_RSS)) {
            $request
                ->setRouteName('blog')
                ->setControllerName(Url::CONTROLLER_RSS)
                ->setActionName(isset($pathInfo[2]) ? $pathInfo[2] : 'index');
        } elseif ($pathInfo[1] == $this->_url->getRoute(Url::CONTROLLER_SEARCH)
            && !empty($pathInfo[2])
        ) {
            $request
                ->setRouteName('blog')
                ->setControllerName(Url::CONTROLLER_SEARCH)
                ->setActionName('index')
                ->setParam('q', $pathInfo[2]);
        } elseif ($pathInfo[1] == $this->_url->getRoute(Url::CONTROLLER_AUTHOR)
            && !empty($pathInfo[2])
            && ($authorId = $this->_getAuthorId($pathInfo[2]))
        ) {
            $request
                ->setRouteName('blog')
                ->setControllerName(Url::CONTROLLER_AUTHOR)
                ->setActionName('view')
                ->setParam('id', $authorId);
        } elseif ($pathInfo[1] == $this->_url->getRoute(Url::CONTROLLER_TAG)
            && !empty($pathInfo[2])
            && $tagId = $this->_getTagId($pathInfo[2])
        ) {
            $request
                ->setRouteName('blog')
                ->setControllerName(Url::CONTROLLER_TAG)
                ->setActionName('view')
                ->setParam('id', $tagId);
        } else {
            $controllerName = null;
            if (Url::PERMALINK_TYPE_DEFAULT == $this->_url->getPermalinkType()) {
                $controllerName = $this->_url->getControllerName($pathInfo[1]);
                unset($pathInfo[1]);
            }

            $pathInfo = array_values($pathInfo);
            $pathInfoCount = count($pathInfo);

            if ($pathInfoCount == 1) {
                if ((!$controllerName || $controllerName == Url::CONTROLLER_ARCHIVE)
                    && $this->_isArchiveIdentifier($pathInfo[0])
                ) {
                    $request
                        ->setRouteName('blog')
                        ->setControllerName(Url::CONTROLLER_ARCHIVE)
                        ->setActionName('view')
                        ->setParam('date', $pathInfo[0]);
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_POST)
                    && $postId = $this->_getPostId($pathInfo[0])
                ) {
                    $request
                        ->setRouteName('blog')
                        ->setControllerName(Url::CONTROLLER_POST)
                        ->setActionName('view')
                        ->setParam('id', $postId);
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_CATEGORY)
                    && $categoryId = $this->_getCategoryId($pathInfo[0])
                ) {
                    $request
                        ->setRouteName('blog')
                        ->setControllerName(Url::CONTROLLER_CATEGORY)
                        ->setActionName('view')
                        ->setParam('id', $categoryId);
                }
            } elseif ($pathInfoCount > 1) {
                $postId = 0;
                $categoryId = 0;
                $first = true;
                $pathExist = true;

                for ($i = $pathInfoCount - 1; $i >= 0; $i--) {
                    if ((!$controllerName || $controllerName == Url::CONTROLLER_POST)
                        && $first
                        && ($postId = $this->_getPostId($pathInfo[$i]))
                    ) {
                        //we have postId
                    } elseif ((!$controllerName || !$first || $controllerName == Url::CONTROLLER_CATEGORY)
                        && ($cid = $this->_getCategoryId($pathInfo[$i], $first))
                    ) {
                        if (!$categoryId) {
                            $categoryId = $cid;
                        }
                    } else {
                        $pathExist = false;
                        break;
                    }

                    if ($first) {
                        $first = false;
                    }
                }


                if ($pathExist) {
                    if ($postId) {
                        $request
                            ->setRouteName('blog')
                            ->setControllerName(Url::CONTROLLER_POST)
                            ->setActionName('view')
                            ->setParam('id', $postId);
                        if ($categoryId) {
                            $request->setParam('category_id', $categoryId);
                        }
                    } elseif ($categoryId) {
                        $request
                            ->setRouteName('blog')
                            ->setControllerName(Url::CONTROLLER_CATEGORY)
                            ->setActionName('view')
                            ->setParam('id', $categoryId);
                    }
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_POST)
                    && $postId = $this->_getPostId(implode('/', $pathInfo))
                ) {
                    $request
                        ->setRouteName('blog')
                        ->setControllerName(Url::CONTROLLER_POST)
                        ->setActionName('view')
                        ->setParam('id', $postId);
                }
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
                'Magento\Framework\App\Action\Redirect',
                ['request' => $request]
            );
        }

        if (!$condition->getContinue()) {
            return null;
        }

        if (!$request->getModuleName()) {
            return null;
        }

        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $_identifier);

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }

    /**
     * Retrieve post id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getPostId($identifier, $checkSufix = true)
    {
        $key = $identifier . ($checkSufix ? '-checksufix' : '');
        if (!isset($this->_postId[$key])) {
            $sufix = $this->_url->getUrlSufix(Url::CONTROLLER_POST);
            $trimmedIdentifier = $this->_url->trimSufix($identifier, $sufix);
            if ($checkSufix && $sufix && $trimmedIdentifier == $identifier) { //if url without sufix
                $this->_postId[$key] = 0;
            } else {
                $post = $this->_postFactory->create();
                $this->_postId[$key] = $post->checkIdentifier(
                    $trimmedIdentifier,
                    $this->_storeManager->getStore()->getId()
                );
            }
        }

        return $this->_postId[$key];
    }

    /**
     * Retrieve category id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getCategoryId($identifier, $checkSufix = true)
    {
        $key = $identifier . ($checkSufix ? '-checksufix' : '');
        if (!isset($this->_categoryId[$key])) {
            $sufix = $this->_url->getUrlSufix(Url::CONTROLLER_CATEGORY);

            $trimmedIdentifier = $this->_url->trimSufix($identifier, $sufix);

            if ($checkSufix && $sufix && $trimmedIdentifier == $identifier) { //if url without sufix
                $this->_categoryId[$key] = 0;
            } else {
                $category = $this->_categoryFactory->create();
                $this->_categoryId[$key] = $category->checkIdentifier(
                    $trimmedIdentifier,
                    $this->_storeManager->getStore()->getId()
                );
            }
        }

        return $this->_categoryId[$key];
    }

    /**
     * Retrieve category id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getAuthorId($identifier)
    {
        if (is_null($this->_authorId)) {
            $author = $this->_authorFactory->create();
            $this->_authorId = $author->checkIdentifier(
                $identifier
            );
        }

        return $this->_authorId;
    }

    /**
     * Retrieve tag id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getTagId($identifier)
    {
        if (is_null($this->_tagId)) {
            $tag = $this->_tagFactory->create();
            $this->_tagId = $tag->checkIdentifier(
                $identifier
            );
        }

        return $this->_tagId;
    }

    /**
     * Detect arcive identifier
     * @param  string  $identifier
     * @return boolean
     */
    protected function _isArchiveIdentifier($identifier)
    {
        $info = explode('-', $identifier);
        return count($info) == 2
            && strlen($info[0]) == 4
            && strlen($info[1]) == 2
            && is_numeric($info[0])
            && is_numeric($info[1]);
    }
}
