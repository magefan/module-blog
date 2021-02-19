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
        $version = $context->getVersion();
        if (version_compare($version, '2.9.1') < 0) {
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

        if (version_compare($version, '2.9.8') < 0) {
            $connection = $this->commentResource->getConnection();
            $tagSelect = $connection->select()->from(
                [$this->commentResource->getTable('magefan_blog_tag')]
            );
            $tags = $connection->fetchAll($tagSelect);


            $count = count($tags);
            if ($count) {
                $data = [];
                foreach ($tags as $i => $tag) {
                    $data[] = [
                        'tag_id' => $tag['tag_id'],
                        'store_id' => 0,
                    ];

                    if (count($data) == 100 || $i == $count - 1) {
                        $this->commentResource->getConnection()->insertMultiple(
                            $this->commentResource->getTable('magefan_blog_tag_store'),
                            $data
                        );
                        $data = [];
                    }
                }
            }
        }
    }
}
