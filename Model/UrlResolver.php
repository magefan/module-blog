<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Api\AuthorInterfaceFactory;
use Magefan\Blog\Api\UrlResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Blog Url Resolver
 */
class UrlResolver implements UrlResolverInterface
{
    const PERMALINK_POST_USE_CATEGORIES = 'mfblog/permalink/post_use_categories';

    /**
     * @var array;
     */
    protected $ids;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TagFactory
     */
    protected $tagFactory;

    /**
     * @var AuthorInterfaceFactory
     */
    protected $authorFactory;

    /**
     * @var int|null
     */
    protected $storeId;

    /**
     * @var Config|mixed
     */
    protected $config;

    /**
     * UrlResolver constructor.
     * @param Url $url
     * @param PostFactory $postFactory
     * @param CategoryFactory $categoryFactory
     * @param AuthorInterfaceFactory $authorFactory
     * @param TagFactory $tagFactory
     * @param StoreManagerInterface $storeManager
     * @param Config|null $config
     */
    public function __construct(
        Url $url,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        AuthorInterfaceFactory $authorFactory,
        TagFactory $tagFactory,
        StoreManagerInterface $storeManager,
        Config $config = null
    ) {
        $this->url = $url;
        $this->postFactory = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->authorFactory = $authorFactory;
        $this->storeManager = $storeManager;
        $this->config = $config ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magefan\Blog\Model\Config::class
        );
    }

    /**
     * @param string $path
     * @return array
     */
    public function resolve($path)
    {
        $identifier = trim($path, '/');
        $identifier = urldecode($identifier);

        $pathInfo = explode('/', $identifier);
        $blogRoute = $this->url->getRoute();

        if ($pathInfo[0] != $blogRoute) {
            return;
        }

        unset($pathInfo[0]);

        if (!count($pathInfo)) {
            return ['id' => 1, 'type' => Url::CONTROLLER_INDEX];
        } elseif ($pathInfo[1] == $this->url->getRoute(Url::CONTROLLER_RSS)) {
            if (!isset($pathInfo[2]) || in_array($pathInfo[2], ['index', 'feed'])) {
                return ['id' => 1, 'type' => Url::CONTROLLER_RSS];
            }
        } elseif ($pathInfo[1] == $this->url->getRoute(Url::CONTROLLER_SEARCH)) {
            return ['id' => empty($pathInfo[2]) ? '' : $pathInfo[2], 'type' => Url::CONTROLLER_SEARCH];
        } elseif ($pathInfo[1] == $this->url->getRoute(Url::CONTROLLER_AUTHOR)
            && !empty($pathInfo[2])
            && ($authorId = $this->_getAuthorId($pathInfo[2]))
        ) {
            return ['id' => $authorId, 'type' => Url::CONTROLLER_AUTHOR];
        } elseif ($pathInfo[1] == $this->url->getRoute(Url::CONTROLLER_TAG)
            && !empty($pathInfo[2])
            && $tagId = $this->_getTagId($pathInfo[2])
        ) {
            return ['id' => $tagId, 'type' => Url::CONTROLLER_TAG];
        } else {
            $controllerName = null;
            if (Url::PERMALINK_TYPE_DEFAULT == $this->url->getPermalinkType()) {
                $controllerName = $this->url->getControllerName($pathInfo[1]);
                unset($pathInfo[1]);
            }

            $pathInfo = array_values($pathInfo);
            $pathInfoCount = count($pathInfo);

            if ($pathInfoCount == 1) {
                if ((!$controllerName || $controllerName == Url::CONTROLLER_ARCHIVE)
                    && $this->_isArchiveIdentifier($pathInfo[0])
                ) {
                    return ['id' => $pathInfo[0], 'type' => Url::CONTROLLER_ARCHIVE];
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_POST)
                    && $postId = $this->_getPostId($pathInfo[0])
                ) {
                    return ['id' => $postId, 'type' => Url::CONTROLLER_POST];
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_CATEGORY)
                    && $categoryId = $this->_getCategoryId($pathInfo[0])
                ) {
                    return ['id' => $categoryId, 'type' => Url::CONTROLLER_CATEGORY];
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
                        if ($categoryId) {
                            if (!(bool)$this->config->getConfig(self::PERMALINK_POST_USE_CATEGORIES)) {
                                return null;
                            }
                            $factory = Url::CONTROLLER_POST . 'Factory';
                            $model = $this->$factory->create()->load($postId);

                            $parentCategories = $model->getParentCategories();
                            if (!count($parentCategories) || !isset($parentCategories[$categoryId])) {
                                return null;
                            }
                        }

                        $result = ['id' => $postId, 'type' => Url::CONTROLLER_POST];
                        if ($categoryId) {
                            $result['params'] = [
                                'category_id' => $categoryId
                            ];
                        }
                        return $result;
                    } elseif ($categoryId) {
                        return ['id' => $categoryId, 'type' => Url::CONTROLLER_CATEGORY];
                    }
                } elseif ((!$controllerName || $controllerName == Url::CONTROLLER_POST)
                    && $postId = $this->_getPostId(implode('/', $pathInfo))
                ) {
                    return ['id' => $postId, 'type' => Url::CONTROLLER_POST];
                }
            }
        }

        return null;
    }

    /**
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Retrieve post id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getPostId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->postFactory,
            Url::CONTROLLER_POST,
            $identifier,
            $checkSufix
        );
    }

    /**
     * Retrieve category id by identifier
     * @param  string $identifier
     * @return int
     */
    protected function _getCategoryId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->categoryFactory,
            Url::CONTROLLER_CATEGORY,
            $identifier,
            $checkSufix
        );
    }

    /**
     * Retrieve category id by identifier
     * @param string $identifier
     * @param bool $checkSufix
     * @return int
     */
    protected function _getAuthorId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->authorFactory,
            Url::CONTROLLER_AUTHOR,
            $identifier,
            $checkSufix
        );
    }

    /**
     * Retrieve tag id by identifier
     * @param string $identifier
     * @param bool $checkSufix
     * @return int
     */
    protected function _getTagId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->tagFactory,
            Url::CONTROLLER_TAG,
            $identifier,
            $checkSufix
        );
    }

    /**
     * @param $factory
     * @param string $controllerName
     * @param string $identifier
     * @param bool $checkSufix
     * @return mixed
     */
    protected function getObjectId($factory, $controllerName, $identifier, $checkSufix)
    {
        $storeId = $this->storeId ?: $this->storeManager->getStore()->getId();
        $key =  $storeId . '-' . $controllerName . '-' .$identifier . ($checkSufix ? '-checksufix' : '');
        if (!isset($this->ids[$key])) {
            $sufix = $this->url->getUrlSufix($controllerName);

            $trimmedIdentifier = $this->url->trimSufix($identifier, $sufix);

            if ($checkSufix && $sufix && $trimmedIdentifier == $identifier) { //if url without sufix
                $this->ids[$key] = 0;
            } else {
                $object = $factory->create();
                $this->ids[$key] = $object->checkIdentifier(
                    $trimmedIdentifier,
                    $storeId
                );
            }
        }

        return $this->ids[$key];
    }

    /**
     * Detect arcive identifier
     * @param  string  $identifier
     * @return boolean
     */
    protected function _isArchiveIdentifier($identifier)
    {
        $info = explode('-', $identifier);
        if (!empty($info[1])) {
            $month = strlen($info[1]) == 2 && is_numeric($info[1]);
        } else {
            $month = true;
        }
        return (count($info) == 2 || count($info) == 1)
            && strlen($info[0]) == 4
            && is_numeric($info[0])
            && $month;
    }
}
