<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magefan\Blog\Model\ResourceModel\Comment;

class UpdatePostCommentsCount implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Comment
     */
    protected $commentResource;

    /**
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @param Comment $commentResource
     * @param ModuleContextInterface $context
     */
    public function __construct(
        Comment        $commentResource,
        ModuleResource $moduleResource
    ) {
        $this->commentResource = $commentResource;
        $this->moduleResource = $moduleResource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.9.1';
    }
}
