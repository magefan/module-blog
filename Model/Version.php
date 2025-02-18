<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\Blog\Model;

use Magefan\Community\Api\GetModuleInfoInterface;
use Magefan\Community\Api\GetModuleVersionInterface;
use Magento\Framework\Escaper;

/**
 * Version model
 */
class Version
{
    /**
     * @var GetModuleVersionInterface
     */
    protected $getModuleVersion;

    /**
     * @var GetModuleInfoInterface|null
     */
    protected $getModuleInfo;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var string
     */
    protected $_moduleName;

    /**
     * Version constructor.
     * @param GetModuleVersionInterface $getModuleVersion
     * @param GetModuleInfoInterface $getModuleInfo
     * @param Escaper $escaper
     */
    public function __construct(
        GetModuleVersionInterface $getModuleVersion,
        GetModuleInfoInterface $getModuleInfo,
        Escaper $escaper
    ) {
        $this->getModuleVersion = $getModuleVersion;
        $this->getModuleInfo = $getModuleInfo;
        $this->_escaper = $escaper;
    }

    /**
     * @return false|string
     */
    public function getVersion()
    {
        try {
            $moduleName = $this->_getModuleName();

            $currentVersion = $this->getModuleVersion->execute($moduleName);
            $moduleInfo = $this->getModuleInfo->execute($moduleName);

            $plan = '';
            foreach (['Extra', 'Plus'] as $_plan) {
                if ($_currentVersion = $this->getModuleVersion->execute($moduleName . $_plan)) {
                    $plan = $_plan;
                    $currentVersion = $_currentVersion;
                    break;
                }
            }

            $latestVersion = $moduleInfo->getVersion();
            if ($latestVersion) {
                $fullModuleTitle = $moduleInfo->getProductName();
                $moduleTitle = str_replace(['Magento 2', 'Magento'], ['', ''], (string)$fullModuleTitle);
                $moduleTitle = trim($moduleTitle);

            } else {
                $moduleTitle = $this->getModuleTitle();
            }

            $data = $this->_escaper->escapeHtml($moduleTitle) . ($plan ? ' (' . $plan . ')' : '') . ' v' . $this->_escaper->escapeHtml($currentVersion);
            return json_encode($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array|string|string[]
     */
    protected function _getModuleName()
    {
        if (!$this->_moduleName) {
            $class = get_class($this);
            $this->_moduleName = substr($class, 0, strpos($class, '\\Model'));
        }
        return str_replace('\\', '_', $this->_moduleName);
    }

    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle()
    {
        return ucwords(str_replace('_', ' ', $this->_getModuleName())) . ' Extension';
    }
}
