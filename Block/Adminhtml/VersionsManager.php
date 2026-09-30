<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml;

use Magefan\Community\Api\GetModuleVersionInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

abstract class VersionsManager extends Template
{
    /**
     * @var GetModuleVersionInterface
     */
    private $getModuleVersion;

    /**
     * @param Context $context
     * @param GetModuleVersionInterface $getModuleVersion
     * @param array $data
     */
    public function __construct(
        Context $context,
        GetModuleVersionInterface $getModuleVersion,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getModuleVersion = $getModuleVersion;
    }

    /**
     * @return bool
     */
    public function isBlogExtraInstalled(): bool
    {
        return (bool)$this->getModuleVersion->execute('Magefan_BlogExtra');
    }

    /**
     * @return bool
     */
    public function isBlogPlusInstalled(): bool
    {
        return (bool)$this->getModuleVersion->execute('Magefan_BlogPlus');
    }

    /**
     * @return string
     */
    public function getCurrentPlan(): string
    {
        if ($this->isBlogExtraInstalled()) {
            return 'Extra';
        }
        if ($this->isBlogPlusInstalled()) {
            return 'Plus';
        }
        return 'Basic';
    }

    /**
     * @return array
     */
    abstract public function getExtraSelectors(): array;

    /**
     * @return array
     */
    abstract public function getPlusSelectors(): array;

}
