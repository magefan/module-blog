<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Api\AuthorInterface;
use Magento\Framework\Model\AbstractModel;
use Magefan\Blog\Api\ShortContentExtractorInterface;

/**
 * Blog author model
 */
class Author extends AbstractModel implements AuthorInterface
{

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Url $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_url = $url;
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\Blog\Model\ResourceModel\Author::class);
        $this->_collectionName = \Magefan\Blog\Model\ResourceModel\Author\Collection::class;
        $this->controllerName = URL::CONTROLLER_AUTHOR;
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore(int $storeId): bool
    {
        return $this->getIsActive();
    }

    /**
     * Retrieve author name (used in identifier generation)
     * @return string | null
     */
    public function getTitle()
    {
        return $this->getName();
    }

    /**
     * Retrieve meta title
     * @return string
     */
    public function getMetaTitle()
    {
        $title = $this->getData('meta_title');
        if (!$title) {
            $title = $this->getTitle();
        }

        return trim($title);
    }

    /**
     * Retrieve meta description
     * @return string
     */
    public function getMetaDescription()
    {
        $desc = $this->getData('meta_description');

        if (!$desc) {
            $desc = $this->getShortContentExtractor()->execute($this->getData('content'));
            $desc = str_replace(['<p>', '</p>'], [' ', ''], $desc);
        }

        $desc = strip_tags($desc);
        if (mb_strlen($desc) > 200) {
            $desc = mb_substr($desc, 0, 200);
        }

        return trim($desc);
    }

    /**
     * Retrieve author identifier
     * @return string | null
     */
    public function getIdentifier()
    {
        return preg_replace(
            "/[^A-Za-z0-9\-]/",
            '',
            strtolower($this->getName('-'))
        );
    }

    /**
     * Check if author identifier exist
     * return author id if author exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        $authors = $this->getCollection();
        foreach ($authors as $author) {
            if ($author->getIdentifier() == $identifier) {
                return $author->getId();
            }
        }

        return 0;
    }

    /**
     * Retrieve author url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_AUTHOR);
    }

    /**
     * Retrieve author url
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_AUTHOR);
    }

    /**
     * Retrieve author name
     *
     * @param string $separator
     * @return string
     */
    public function getName($separator = ' ')
    {
        return $this->getFirstname() . $separator . $this->getLastname();
    }

    /**
     * @deprecated use getDynamicData method in graphQL data provider
     * Prepare all additional data
     * @return array
     */
    public function getDynamicData()
    {
        $data = $this->getData();

        $keys = [
            'meta_description',
            'meta_title',
            'author_url',
            'name',
            'title',
            'identifier',
        ];

        $data['author_id'] = $this->getId();

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
     * Retrieve controller name
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Retrieve true if author is active
     * @return boolean
     */
    public function isActive()
    {
        return $this->getIsActive();
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
}
