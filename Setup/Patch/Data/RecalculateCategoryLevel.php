<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class RecalculateCategoryLevel implements DataPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();
        $table = $this->schemaSetup->getTable('magefan_blog_category');

        $rows = $connection->fetchAll(
            $connection->select()->from($table, ['category_id', 'path'])
        );

        if (!$connection->tableColumnExists($table, 'level')) {
            return;
        }
        foreach ($rows as $row) {
            $path = (string)$row['path'];
            $level = $path !== '' ? count(explode('/', $path)) + 1 : 1;

            $connection->update(
                $table,
                ['level' => $level],
                ['category_id = ?' => $row['category_id']]
            );
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
