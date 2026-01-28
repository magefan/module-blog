<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magefan\Blog\Model\Config\Source\DesignVersion;

class SetDesignVersion implements DataPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * Constructor
     *
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

        $table = $this->schemaSetup->getTable('core_config_data');

        $select = $connection->select()
            ->from($table)
            ->where('path = ?', 'mfblog/post_view/design/template');

        foreach ($connection->fetchAll($select) as $data) {
            if (!$data['value']) {
                /* If used not Modern template for the blog post, then set initial design version */
                unset($data['config_id']);
                $data['path'] = 'mfblog/general/design_version';
                $data['value'] = DesignVersion::INITIAL;
                $connection->insert($table, $data);
            }
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * Retrieve aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Retrieve the list of class dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
