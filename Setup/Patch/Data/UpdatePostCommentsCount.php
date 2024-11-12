<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magefan\Blog\Model\ResourceModel\Comment;

class UpdatePostCommentsCount implements DataPatchInterface, PatchRevertableInterface
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
        Comment $commentResource,
        ModuleResource $moduleResource
    ) {
        $this->commentResource = $commentResource;
        $this->moduleResource = $moduleResource;
    }

    public static function getDependencies()
    {
        return[];
    }

    public function getAliases()
    {
        return[];
    }

    public function apply()
    {
        $version = $this->moduleResource->getDbVersion('Magefan_Blog');
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
    }

    public function revert()
    {
    }
}
