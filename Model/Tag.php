<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Model;

use Magefan\Blog\Model\Url;
use Magefan\Blog\Api\ShortContentExtractorInterface;

/**
 * Tag model
 *
 * @method \Magefan\Blog\Model\ResourceModel\Tag _getResource()
 * @method \Magefan\Blog\Model\ResourceModel\Tag getResource()
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method $this setIdentifier(string $value)
 */
class Tag extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Tag Status
     */
    const STATUS_ENABLED = 1;

    /**
     * blog cache tag
     */
    const CACHE_TAG = 'mfb_t';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_blog_tag';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'blog_tag';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var ShortContentExtractorInterface
     */
    protected $shortContentExtractor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\Blog\Model\Url $url
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Url $url,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\Blog\Model\ResourceModel\Tag::class);
        $this->controllerName = URL::CONTROLLER_TAG;
    }

    /**
     * Retrieve true if tag is active
     * @return boolean
     */
    public function isActive(): bool
    {
        return ($this->getIsActive() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore($storeId): bool
    {
        return $this->getIsActive()
            && (null === $storeId || array_intersect([0, $storeId], $this->getStoreIds()));
    }

    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false): string
    {
        return $plural ? 'Tags' : 'Tag';
    }

    /**
     * Check if tag identifier exist for specific store
     * return tag id if tag exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId): string|false
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Retrieve catgegory url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_TAG);
    }

    /**
     * Retrieve tag url
     * @return string
     */
    public function getTagUrl()
    {
        $url = $this->getData('tag_url');
        if (!$url) {
            $url = $this->_url->getUrl($this, URL::CONTROLLER_TAG);
            $this->setData('tag_url', $url);
        }

        return $url;
    }

    /**
     * Retrieve meta title
     * @return string
     */
    public function getMetaTitle(): string
    {
        $title = $this->getData('meta_title');
        if (!$title) {
            $title = $this->getData('title');
        }

        return trim($title ?: '');
    }

    /**
     * Retrieve meta description
     * @return string
     */
    public function getMetaDescription(): string
    {
        $desc = $this->getData('meta_description');
        if (!$desc) {
            $desc = $this->getShortContentExtractor()->execute($this->getData('content'), 500);
        }

        $stylePattern = "~<style\b[^>]*>.*?</style>~is";
        $desc = preg_replace($stylePattern, '', $desc);
        $desc = trim(strip_tags((string)$desc));
        $desc = str_replace(["\r\n", "\n\r", "\r", "\n"], ' ', $desc);

        if (mb_strlen($desc) > 160) {
            $desc = mb_substr($desc, 0, 160);
            $lastSpace = mb_strrpos($desc, ' ');
            if ($lastSpace !== false) {
                $desc = mb_substr($desc, 0, $lastSpace) . '...';
            }
        }

        return trim($desc);
    }

    /**
     * Retrieve identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Retrieve block identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)$this->getData('identifier');
    }

    /**
     * Retrieve controller name
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @deprecated use getDynamicData method in graphQL data provider
     * Return all additional data
     * @return array
     */
    public function getDynamicData()
    {
        $data = $this->getData();

        $keys = [
            'meta_description',
            'meta_title',
            'tag_url',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace(
                '_',
                '',
                ucwords($key, '_')
            );
            $data[$key] = $this->$method();
        }

        return $data;
    }

    /**
     * @return ShortContentExtractorInterface
     */
    public function getShortContentExtractor()
    {
        if (null === $this->shortContentExtractor) {
            $this->shortContentExtractor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ShortContentExtractorInterface::class);
        }

        return $this->shortContentExtractor;
    }

    /**
     * @return array|mixed|null
     */
    public function getTagImage()
    {
        if (!$this->hasData('tag_image')) {
            if ($file = $this->getData('tag_img')) {
                $image = $this->_url->getMediaUrl($file);

            } else {
                $image = false;
            }
            $this->setData('tag_image', $image);
        }

        return $this->getData('tag_image');
    }
}
