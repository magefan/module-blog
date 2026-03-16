<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Controller;

use Magefan\Blog\Model\Url;
use Magefan\Blog\Api\UrlResolverInterface;
use Magefan\Blog\Model\Config;
use Magefan\Blog\Model\PostFactory;
use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\TagFactory;
use Magefan\Blog\Api\AuthorInterfaceFactory as AuthorFactory;

/**
 * Class Blog Router
 */
class RedirectBlog implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Url
     */
    protected $blogUrl;

    /**
     * @var UrlResolverInterface
     */
    protected $urlResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var int
     */
    protected $blogObjectStoreId;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var TagFactory
     */
    protected $tagFactory;

    /**
     * @var AuthorFactory
     */
    protected $authorFactory;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Url $blogUrl
     * @param UrlResolverInterface $urlResolver
     * @param Config $config
     * @param PostFactory $postFactory
     * @param CategoryFactory $categoryFactory
     * @param TagFactory $tagFactory
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Url $blogUrl,
        UrlResolverInterface $urlResolver,
        Config $config,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        AuthorFactory $authorFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->blogUrl = $blogUrl;
        $this->urlResolver = $urlResolver;
        $this->config = $config;
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->authorFactory = $authorFactory;
    }

    /**
     * Check if blog is enabled for a given store.
     *
     * @param int $storeId
     * @return bool
     */
    public function enabled($storeId): bool
    {
        return $this->config->isEnabled($storeId);
    }

    /**
     * Match a request to a blog redirect across stores.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            return;
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

            if (!$this->enabled($_storeId)) {
                continue;
            }

            $blogUrl = $this->getBlogUrl();

            $this->blogObjectStoreId = $_storeId;
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
                case Url::CONTROLLER_TAG:
                case Url::CONTROLLER_AUTHOR:
                    $redirectId = $blogPage['id'];
                    $entityType = ucfirst($blogPage['type']);
                    $entity = $this->createBlogEntity($blogPage['type'])->setStoreId($storeId)->load($redirectId);
                    if ($entity->isVisibleOnStore($_originStoreId)) {
                        if ($entity->isVisibleOnStore($storeId)) {
                            $getEntityUrl = 'get' . $entityType . 'Url';
                            $redirectUrl = $entity->$getEntityUrl();
                        } else {
                            $redirectUrl = $blogUrl->getBaseUrl();
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
     * Retrieve blog URL model instance.
     *
     * @return \Magefan\Blog\Model\Url
     */
    protected function getBlogUrl()
    {
        return $this->blogUrl;
    }

    /**
     * Resolve the blog page data for the given URL identifier.
     *
     * @param string $_identifier
     * @return array|void|null
     */
    protected function getBlogPage($_identifier)
    {
        $urlResolver = $this->urlResolver;
        $urlResolver->setStoreId($this->getBlogUrl()->getStoreId());
        $blogPage = $urlResolver->resolve($_identifier);
        return $blogPage;
    }

    /**
     * Create Blog Entity
     *
     * @param string $type
     * @return mixed
     */
    protected function createBlogEntity(string $type)
    {
        $entityFactory = $type . 'Factory';
        return $this->$entityFactory->create();
    }
}
