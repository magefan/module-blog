<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\App\ResourceConnection;

class TagInStore implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
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
        return '2.9.8';
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->delete(
            $this->resourceConnection->getTableName('magefan_blog_tag_store'),
            ['store_id = ?' => 0]
        );

        $tagSelect = $connection->select()->from(
            $this->resourceConnection->getTableName('magefan_blog_tag')
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
                        $this->resourceConnection->getTableName('magefan_blog_tag_store'),
                        $data
                    );
                    $data = [];
                }
            }
        }
    }
}
