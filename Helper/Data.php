<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Store\Model\ScopeInterface;

/**
 * Magefan Blog Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_MFLAZYLOAD_ENABLED = 'mflazyzoad/general/enabled';
    const XML_PATH_MFLAZYLOAD_METHOD = 'mflazyzoad/general/method';

    /**
     * Retrieve translated & formated date
     * @param  string $format
     * @param  string $dateOrTime
     * @return string
     */
    public static function getTranslatedDate($format, $dateOrTime)
    {
        $time = is_numeric($dateOrTime) ? $dateOrTime : strtotime((string)$dateOrTime);
        $month = ['F' => '%1', 'M' => '%2'];

        foreach ($month as $from => $to) {
            $format = str_replace($from, $to, $format);
        }

        $date = date($format, $time);

        foreach ($month as $to => $from) {
            $date = str_replace($from, __(date($to, $time)), $date);
        }

        return $date;
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function isOriginMfLazyLoadEnable()
    {
        return $this->getConfig(self::XML_PATH_MFLAZYLOAD_ENABLED) && ($this->getConfig(self::XML_PATH_MFLAZYLOAD_METHOD) == 0) && $this->_moduleManager->isEnabled('Magefan_LazyLoad');
    }
}
