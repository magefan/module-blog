<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model;

use Magefan\Blog\Model\Url;

/**
 * Blog author model
 */
class Author extends \Magento\Framework\Model\AbstractModel
{
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
        $this->_init('Magefan\Blog\Model\ResourceModel\Author');
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
        foreach($authors as $author) {
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

}