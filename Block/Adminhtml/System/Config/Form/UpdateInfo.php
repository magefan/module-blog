<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Module\ModuleListInterface;
use Magefan\Blog\Model\AdminNotificationFeed;

/**
 * Class Update Info Block
 */
class UpdateInfo extends \Magento\Backend\Block\Template
{
    const MODULE_NAME = 'Blog';
    const LATESTS_VERSION_CACHE_KEY = 'magefan_latests_product_versions';

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var mixed
     */
    protected $latestVersion;

    /**
     * @var mixed
     */
    protected $currentVersion;

    /**
     * @var AdminNotificationFeed
     */
    protected $adminNotificationFeed;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * UpdateInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param AdminNotificationFeed $adminNotificationFeed
     * @param ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AdminNotificationFeed $adminNotificationFeed,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->cacheManager = $context->getCache();
        $this->adminNotificationFeed = $adminNotificationFeed;
        $this->moduleList = $moduleList;
        $this->jsonHelper = $jsonHelper;
        $this->curlClient = $curl;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCurrentVersion()
    {
        if (null === $this->currentVersion) {
            $this->currentVersion = $this->moduleList
                ->getOne($this->getModuleName())['setup_version'];
        }

        return $this->currentVersion;
    }

    /**
     * @return bool
     */
    public function getLatestVersion()
    {
        if (null === $this->latestVersion) {
            $latestVersions = $this->cacheManager->load(self::LATESTS_VERSION_CACHE_KEY);
            if (false === $latestVersions) {
                try {
                    $this->curlClient->get(
                        'https://m'.'a'.'g'.'e'.'f'.'a'.'n'.'.'.'c'.'o'.'m/media/product-versions.json',
                        []
                    );
                    $latestVersions = (string)$this->curlClient->getBody();
                } catch (\Exception $e) {
                    $latestVersions = '';
                }
                $this->cacheManager->save($latestVersions, self::LATESTS_VERSION_CACHE_KEY);
            }

            try {
                $encodedData = $this->jsonHelper->jsonDecode($latestVersions);
                if (!$encodedData) {
                    throw new \Exception('Empty response');
                }
                $this->latestVersion = $encodedData[self::MODULE_NAME];
            } catch (\Exception $e) {
                $this->latestVersion = false;
            }
        }

        return $this->latestVersion;
    }

    /**
     * @return bool
     */
    public function needToUpdate()
    {
        return (version_compare($this->getCurrentVersion(), $this->getLatestVersion()) < 0);
    }
}
