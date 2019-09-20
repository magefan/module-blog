<?php

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magefan\Blog\Model\ResourceModel\Comment;
use Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    protected $commentResource;

    protected $_commentCollection;

    public function __construct(
        Comment $commentResource,
        CollectionFactory $commentCollection
    )
    {
        $this->_commentCollection = $commentCollection;
        $this->commentResource = $commentResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(),'2.9.1.3') < 0)  {

            $connection = $this->commentResource->getConnection();

            $select = $connection->select()->from(
                [$this->commentResource->getTable('magefan_blog_comment')]
                )
                ->where('post_id = ?', 1)
                ->where('status = ?', 1);
            $result = $connection->fetchAll($select);
            //$comments = $this->_commentCollection->create();
            $commentsIds = [];
            foreach ($result as $comment) {
                foreach ($commentsIds as $commentsId) {
                    if ( in_array($comment['comment_id'], $commentsId)  ) {
                        array_push($commentsId, $comment['comment_id'] );
                        $this->commentResource->updatePostCommentCount(1);
                    }
                }
            }
        }
    }
}