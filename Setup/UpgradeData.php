<?php

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magefan\Blog\Model\ResourceModel\Comment;

class UpgradeData implements UpgradeDataInterface
{
    protected $commentResource;

    protected $_commentCollection;

    public function __construct(
        Comment $commentResource
    ) {
        $this->commentResource = $commentResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.9.1') < 0) {
            $connection = $this->commentResource->getConnection();
            $postSelect = $connection->select()->from(
                [$this->commentResource->getTable('magefan_blog_post')]
            )
                ->where('is_active = ?', 1);
            $posts = $connection->fetchAll($postSelect);
            foreach ($posts as $post) {
                $this->commentResource->updatePostCommentsCount($post['post_id']);
            }
        }
    }
}
