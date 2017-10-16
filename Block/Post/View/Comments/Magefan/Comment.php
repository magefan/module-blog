<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post\View\Comments\Magefan;

use Magento\Store\Model\ScopeInterface;

/**
 * Magefan comment block
 *
 * @method string getComment()
 * @method $this setComment(\Magefan\Blog\Model\Comment $comment)
 */
class Comment extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Magefan_Blog::post/view/comments/magefan/comment.phtml';

    public function getReplies()
    {
        $comment = $this->getComment();
        if (!$comment->isReply()) {
            return $comment->getChildComments()
                ->addActiveFilter()
                ->setPageSize(5)
                ->setOrder('creation_time', 'DESC');
        } else {
            return [];
        }
    }
}
