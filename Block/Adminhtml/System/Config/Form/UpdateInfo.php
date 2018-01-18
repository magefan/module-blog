<?php

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Module\ModuleListInterface;

class UpdateInfo extends \Magento\Backend\Block\Template
{
    protected $curlClient;

    const MODULE_NAME = 'Blog';

    protected $_moduleList;
    protected $latestVersion;

    const PATH_TO_JSON_FILE = 'https://magefan.com/media/product-versions.json';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->_moduleList = $moduleList;
        $this->jsonHelper = $jsonHelper;
        $this->curlClient = $curl;
        parent::__construct($context, $data);
    }

    public function getCurrentVersion()
    {
        return $this->_moduleList
            ->getOne($this->getModuleName())['setup_version'];
    }

    public function getLatestVersion()
    {
        if (null === $this->latestVersion) {
            $this->curlClient->get(self::PATH_TO_JSON_FILE, []);
            $response = $this->curlClient->getBody();
            try {
                $encodedData = $this->jsonHelper->jsonDecode($response);
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

    public function needToUpdate()
    {
        return (version_compare($this->getCurrentVersion(), $this->getLatestVersion()) < 0 ? true :false);
    }

}