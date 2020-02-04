<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Framework\App\ObjectManager;
use Magefan\Community\Model\Section;

/**
 * Class Check EnableInfo Block
 */
class CheckEnableInfo extends \Magento\Backend\Block\Template
{
    /**
     * Magefan Blog Plus Module
     * @deprecated
     */
    const MAGEFAN_BLOG_PLUS = 'Magefan_BlogPlus';

    /**
     * Extension key config path
     */
    const XML_PATH_KEY = 'mfblog/general/key';

    /**
     * @var \Magefan\Blog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $moduleStatus;

    /**
     * CheckEnableInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magefan\Blog\Model\Config $config
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $metadata
     * @param \Magento\Framework\Module\Status $moduleStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magefan\Blog\Model\Config $config,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Magento\Framework\Module\Status $moduleStatus,
        array $data = []
    ) {
        $this->config = $config;
        $this->moduleList  = $moduleList;
        $this->metadata = $metadata;
        $this->moduleStatus = $moduleStatus;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        foreach ($this->_storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }

                foreach ($stores as $store) {
                    if ($this->config->isEnabled($store->getId())) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isKeyMissing()
    {
        $section = ObjectManager::getInstance()->create(Section::class, ['name' => 'mfblog']);
        return !$this->config->getConfig(self::XML_PATH_KEY)
            && $section->getModule();
    }

    /**
     * Disable all modules witch have Blog part on his name
     */
    public function disableAnotherBlogModules()
    {
        $allowedModules = [
            'Magefan_Blog',
            'Magefan_BlogAuthor',
            'Magefan_BlogPlus',
            'Magefan_BlogExtra'
        ];

        $moduleNames = [];

        foreach ($this->getModulesNameList() as $module) {
            if (strpos($module, 'Blog')
                && !in_array($module, $allowedModules)
            ) {
                $moduleNames[] = $module;
            }
        }

        $this->moduleStatus->setIsEnabled(false, $moduleNames);
    }

    /**
     * @return string[]
     */
    private function getModulesNameList()
    {
        return $this->moduleList->getNames();
    }
}
