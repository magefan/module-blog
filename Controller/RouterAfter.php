<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller;

use Magefan\Blog\Api\UrlResolverInterface;
use Magefan\Blog\Model\Url;
use Magefan\Blog\Model\Config;

class RouterAfter  implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var array;
     */
    protected $ids;

    /**
     * @var mixed
     */
    protected $blogUrl;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magefan\Blog\Model\PostFactory
     */
    private $postFactory;

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magefan\Blog\Api\AuthorInterfaceFactory
     */
    private $authorFactory;

    /**
     * @var \Magefan\Blog\Model\TagFactory
     */
    private $tagFactory;

    /**
     * @var \Magefan\Blog\Api\UrlResolverInterface|UrlResolverInterface|mixed
     */
    private $urlResolver;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory,
        \Magefan\Blog\Api\AuthorInterfaceFactory $authorFactory,
        \Magefan\Blog\Model\TagFactory $tagFactory,
        \Magefan\Blog\Model\Url $blogUrl,
        ?UrlResolverInterface $urlResolver = null,
        ?Config $config = null
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->authorFactory = $authorFactory;
        $this->tagFactory = $tagFactory;
        $this->blogUrl = $blogUrl;
        $this->urlResolver = $urlResolver ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magefan\Blog\Api\UrlResolverInterface::class
        );
        $this->config = $config ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            Config::class
        );
    }


    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            return null;
        }
        $_identifier = trim($request->getPathInfo(), '/');
        $_identifier = urldecode($_identifier);

        $storeId = $this->storeManager->getStore()->getId();

        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }
            $stores[] = $store->getId();
        }

        foreach ($stores as $_storeId) {
            if ($_storeId == $storeId) {
                continue;
            }
            $blogUrl = $this->blogUrl;
            $_originStoreId = $blogUrl->getStoreId();
            $blogUrl->setStoreId($_storeId);
            $blogPage = $this->getBlogPage($_identifier);
            $blogUrl->setStoreId($_originStoreId);

            if (!$blogPage || empty($blogPage['type']) || empty($blogPage['id'])) {
                continue;
            }
            $redirectUrl = null;
            switch ($blogPage['type']) {
                case Url::CONTROLLER_INDEX:
                    $redirectUrl = $blogUrl->getBaseUrl();
                    break;
                case Url::CONTROLLER_TAG:
                    $blogPage['type'] = 'tag';
                    $tag = $this->tagFactory->create()->load($blogPage['id']);
                    if ($tag->getId()) {
                        $redirectUrl = $tag->getTagUrl();
                    }
                    break;
                case Url::CONTROLLER_AUTHOR:
                    $blogPage['type'] = 'author';
                    $author = $this->authorFactory->create()->load($blogPage['id']);
                    if ($author->getId()) {
                        $redirectUrl = $author->getAuthorUrl();
                    }
                    break;
                case Url::CONTROLLER_RSS:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_RSS
                    );
                    break;
                case Url::CONTROLLER_SEARCH:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_SEARCH
                    );
                    break;
                case Url::CONTROLLER_ARCHIVE:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_ARCHIVE
                    );
                    break;
                case Url::CONTROLLER_POST:
                case Url::CONTROLLER_CATEGORY:
                    $redirectId = $blogPage['id'];

                    if ($blogPage['type'] == Url::CONTROLLER_POST) {
                        $post = $this->postFactory->create()->load($redirectId);
                        if ($post->isVisibleOnStore($_originStoreId)) {
                            if ($post->isVisibleOnStore($storeId)) {
                                $redirectUrl = $post->getPostUrl();
                            } else {
                                $redirectUrl = $blogUrl->getBaseUrl();
                            }
                        }
                    } elseif ($blogPage['type'] == Url::CONTROLLER_CATEGORY) {
                        $category = $this->categoryFactory->create()->load($redirectId);
                        if ($category->isVisibleOnStore($_originStoreId)) {
                            if ($category->isVisibleOnStore($storeId)) {
                                $redirectUrl = $category->getCategoryUrl();
                            } else {
                                $redirectUrl = $blogUrl->getBaseUrl();
                            }
                        }
                    }
                    break;
            }

            if ($redirectUrl) {
                $this->response->setRedirect($redirectUrl, 301);
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Redirect::class,
                    ['request' => $request]
                );
            }
        }
    }

    /**
     * @param $_identifier
     * @return array|void|null
     */
    protected function getBlogPage($_identifier)
    {
        $urlResolver = $this->urlResolver;
        $urlResolver->setStoreId($this->blogUrl->getStoreId());
        return $urlResolver->resolve($_identifier);
    }
}
