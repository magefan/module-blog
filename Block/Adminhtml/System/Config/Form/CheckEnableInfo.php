<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

/**
 * Class CheckEnableInfo
 * @package Magefan\Blog\Block\Adminhtml\System\Config\Form
 */
class CheckEnableInfo extends \Magento\Backend\Block\Template
{
    /**
     * Magefan Blog Plus
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
     * CheckEnableInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magefan\Blog\Model\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\ProductMetadataInterface $metadata
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magefan\Blog\Model\Config $config,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        array $data = []
    ) {
        $this->config = $config;
        $this->moduleList  = $moduleList;
        $this->metadata = $metadata;
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
        return !$this->config->getConfig(self::XML_PATH_KEY)
            && ($this->metadata->getEdition() != 'Community'
                || $this->moduleList->has(self::MAGEFAN_BLOG_PLUS)
        );
    }
}
