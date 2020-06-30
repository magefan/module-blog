<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View\Comments\Magefan;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Magefan comment block
 *
 * @method string getComment()
 * @method $this setComment(\Magefan\Blog\Model\Comment $comment)
 */
class Comment extends Template implements IdentityInterface
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Comment constructor.
     * @param Template\Context $context
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        TimezoneInterface $timezone = null
    ) {
        $this->timezone = $timezone ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * @var array
     */
    protected $repliesCollection = [];

    /**
     * Template file
     * @var string
     */
    protected $_template = 'Magefan_Blog::post/view/comments/magefan/comment.phtml';

    /**
     * Retrieve identities
     *
     * @return string
     */
    public function getIdentities()
    {
        return $this->getComment()->getIdentities();
    }

    /**
     * Retrieve sub-comments collection or empty array
     *
     * @return \Magefan\Blog\Model\ResourceModel\Comment\Collection | array
     */
    public function getRepliesCollection()
    {
        $comment = $this->getComment();
        if (!$comment->isReply()) {
            $cId = $comment->getId();
            if (!isset($this->repliesCollection[$cId])) {
                $this->repliesCollection[$cId] = $this->getComment()->getChildComments()
                    ->addActiveFilter()
                    /*->setPageSize($this->getNumberOfReplies())*/
                    //->setOrder('creation_time', 'DESC'); old sorting
                      ->setOrder('creation_time', 'ASC');
            }

            return $this->repliesCollection[$cId];
        } else {
            return [];
        }
    }

    /**
     * Retrieve number of replies to display
     *
     * @return string
     */
    public function getNumberOfReplies()
    {
        return $this->_scopeConfig->getValue(
            \Magefan\Blog\Model\Config::NUMBER_OF_REPLIES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPublishDate()
    {
        $dateFormat = $this->_scopeConfig->getValue(
            'mfblog/post_view/comments/format_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $gmtDate = $this->getComment()->getPublishDate();

        return $this->timezone->date($gmtDate)->format($dateFormat);
    }
}
