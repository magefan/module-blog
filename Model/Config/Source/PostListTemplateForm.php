<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\BlogExtra\Block\Widget;

use Magefan\Blog\Model\Config\Source\DesignVersion;

class PostListTemplateForm implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray(): array
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->get(\Magefan\Blog\Model\Config::class);

        if ($config->getDesignVersion() == DesignVersion::VALOR ) {
            return $objectManager->get(\Magefan\Blog\Model\Config\Source\PostListTemplateNew::class)->toOptionArray();
        }
        return $objectManager->get(\Magefan\Blog\Model\Config\Source\PostListTemplate::class)->toOptionArray();
    }
}