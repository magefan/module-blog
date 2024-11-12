<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magefan\Blog\Model\ResourceModel\Comment;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class TagInStore implements DataPatchInterface, PatchRevertableInterface
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

        if (version_compare($version, '2.9.8') < 0) {
            $connection = $this->commentResource->getConnection();

            $connection->delete(
                $this->commentResource->getTable('magefan_blog_tag_store'),
                ['store_id = ?' => 0]
            );

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
                        $connection->insertMultiple(
                            $this->commentResource->getTable('magefan_blog_tag_store'),
                            $data
                        );
                        $data = [];
                    }
                }
            }
        }
    }

    public function revert()
    {
    }
}
