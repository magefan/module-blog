<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar rss
 */
class Rss extends \Magento\Framework\View\Element\Template
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'rss_feed';

    /**
     * Available months
     * @var array
     */
    protected $_months;

    /**
     * @var \Magefan\Blog\Model\Url
     */
    private $blogUrl;

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        if ('blog/rss/feed' == $route && empty($params)) {
             return $this->getBlogUrl()->getUrl('feed', 'rss');
        }
        return parent::getUrl($route, $params);
    }


    /**
     * Retrieve blog url model
     *
     * @return  \Magefan\Blog\Model\Url
     */
    private function getBlogUrl()
    {
        if (null === $this->blogUrl) {
            $this->blogUrl = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magefan\Blog\Model\Url::class);
        }

        return $this->blogUrl;
    }
}
