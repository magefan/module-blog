<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magefan\Blog\Model\ResourceModel\Comment;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class TagInStore implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Comment
     */
    protected $commentResource;

    /**
     * @param Comment $commentResource
     */
    public function __construct(
        Comment $commentResource
    )
    {
        $this->commentResource = $commentResource;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
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

    public static function getVersion()
    {
        return '2.9.8';
    }
}
