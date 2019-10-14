<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View\Comments;

use Magento\Store\Model\ScopeInterface;
use Magefan\Blog\Model\Config\Source\CommetType;

/**
 * Blog post Magefan comments block
 */
class Magefan extends \Magefan\Blog\Block\Post\View\Comments implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var string
     */
    protected $commetType = CommetType::MAGEFAN;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    protected $commentCollection;

    /**
     * @var string
     */
    protected $defaultCommentBlock = \Magefan\Blog\Block\Post\View\Comments\Magefan\Comment::class;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magento\Framework\Locale\ResolverInterface      $localeResolver
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Customer\Model\Url                      $customerUrl
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $localeResolver, $data);
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Retrieve comment block
     *
     * @return \Magefan\Blog\Block\Post\View\Comments\Magefan\Comment
     */
    public function getCommentBlock()
    {
        $k = 'comment_block';
        if (!$this->hasData($k)) {
            $blockName = $this->getCommentBlockName();
            if ($blockName) {
                $block = $this->getLayout()->getBlock($blockName);
            }

            if (empty($block)) {
                $block = $this->getLayout()->createBlock($this->defaultCommentBlock, uniqid(microtime()));
            }

            $this->setData($k, $block);
        }

        return $this->getData($k);
    }

    /**
     * Retrieve comment html
     *
     * @return string
     */
    public function getCommentHtml(\Magefan\Blog\Model\Comment $comment)
    {
        return $this->getCommentBlock()
            ->setComment($comment)
            ->toHtml();
    }

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function prepareCommentCollection()
    {

        $this->commentCollection = $this->getPost()->getComments()
            ->addActiveFilter()
            ->addFieldToFilter('parent_id', 0)
            /*->setPageSize($this->getNumberOfComments())*/
            ->setOrder('creation_time', 'DESC');
    }

    /**
     * Prepare posts collection
     *
     * @return \Magefan\Blog\Model\ResourceModel\Comment\Collection
     */
    public function getCommentsCollection()
    {
        if (null === $this->commentCollection) {
            $this->prepareCommentCollection();
        }

        return $this->commentCollection;
    }

    /**
     * Retrieve customer session
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Retrieve customer url model
     * @return \Magento\Customer\Model\Url
     */
    public function getCustomerUrl()
    {
        return $this->customerUrl;
    }

    /**
     * Retrieve true if customer can post new comment or reply
     *
     * @return string
     */
    public function canPost()
    {
        return $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::GUEST_COMMENT,
            ScopeInterface::SCOPE_STORE
        ) || $this->getCustomerSession()->getCustomerGroupId();
    }

    /**
     * Retrieve number of comments to display
     *
     * @return string
     */
    public function getNumberOfComments()
    {
        return $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::NUMBER_OF_COMMENTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve form url
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('blog/comment/post');
    }

    /**
     * Retrieve identities
     *
     * @return string
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getCommentsCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        return array_unique($identities);
    }
}
