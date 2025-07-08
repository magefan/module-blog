<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\Blog\Model\Config as BlogConfig;

class SetDesignVersion implements DataPatchInterface
{

    private $postViewDsign = 'mfblog/post_view/design/template';

    private $dsignVersion = 'mfblog/general/design_version';

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlogConfig
     */
    private $blogConfig;

    /**
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param BlogConfig $blogConfig
     */
    public function __construct(
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        BlogConfig $blogConfig
    )
    {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->blogConfig = $blogConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $defaultStoreValue = '';
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            $value = $this->blogConfig->getConfig(
                $this->postViewDsign,
                $store->getId()
            );

            if ($defaultStoreValue === $value) {
                continue;
            }

            if (!$defaultStoreValue) {
                $defaultStoreValue = $value;
            }

            if ($value == 'modern') {
                $this->configWriter->save(
                    $this->dsignVersion,
                    \Magefan\Blog\Model\Config\Source\DesignVersion::MODERN,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
            }
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

}
