<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Observer;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magefan\Blog\Model\PostRepository;
use Magefan\Blog\Model\PostFactory;
use Magefan\Blog\Model\CategoryFactory;
use Magefan\Blog\Model\TagFactory;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class UpdateUrlRewriteForEntity implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var UrlRewriteFactory
     */
    private $rewriteFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @param RequestInterface $request
     * @param PostRepository $postRepository
     * @param PostFactory $postFactory
     * @param CategoryFactory $categoryFactory
     * @param TagFactory $tagFactory
     * @param UrlRewriteFactory $rewriteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface      $request,
        PostRepository        $postRepository,
        PostFactory           $postFactory,
        CategoryFactory           $categoryFactory,
        TagFactory $tagFactory,
        UrlRewriteFactory     $rewriteFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->request = $request;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->rewriteFactory = $rewriteFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException|Exception
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('data_object');
        if (!$object->isObjectNew() && $object->dataHasChangedFor('identifier')) {
            $stores = $object->getStores() ?: $object->getStoreIds();

            if ($stores) {
                foreach ($stores as $store) {
                    if (!is_numeric($store)) {
                        continue;
                    }

                    try {
                        $storeId = $store == 0 ? $this->storeManager->getDefaultStoreView()->getId() : $store;
                        $modelName = $this->request->getControllerName() . 'Factory';
                        $oldObject = $this->$modelName->create()->load($object->getId());

                        $urlRewriteModel = $this->rewriteFactory->create();
                        $urlRewriteModel->setEntityType('custom')
                            ->setStoreId($storeId)
                            ->setIsSystem(0)
                            ->setRedirectType(301)
                            ->setEntityId($object->getId())
                            ->setTargetPath($object->getUrl())
                            ->setRequestPath($oldObject->getUrl());
                        $urlRewriteModel->save();
                    }catch (\Exception $exception) {
                        continue;
                    }
                }
            }
        }
    }
}