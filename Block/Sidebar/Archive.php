<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar archive block
 */
class Archive extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    use Widget;

    /**
     * @var string
     */
    protected $_widgetKey = 'archive';

    /**
     * Available months
     * @var array
     */
    protected $_months;

    /**
     * Prepare posts collection
     *
     * @return void
     */
    protected function _preparePostCollection()
    {
        parent::_preparePostCollection();
        $this->_postCollection->getSelect()->group(
            'CONCAT(YEAR(main_table.publish_time),"-",MONTH(main_table.publish_time))',
            'DESC'
        );
    }

    /**
     * Retrieve available months
     * @return array
     */
    public function getMonths()
    {
        if (null === $this->_months) {
            $format = 'Y-m';
            if ($this->getGroupBy() == 'year') {
                $format = 'Y';
            }

            $this->_months = [];
            $this->_preparePostCollection();
            foreach ($this->_postCollection as $post) {
                $time = strtotime((string)$post->getData('publish_time'));
                $this->_months[date($format, $time)] = $time;
            }
        }

        return $this->_months;
    }

    /**
     * Retrieve year by time
     * @param  int $time
     * @return string
     */
    public function getYear($time)
    {
        return date('Y', $time);
    }

    /**
     * Retrieve month by time
     * @param  int $time
     * @return string
     */
    public function getMonth($time)
    {
        return __(date('F', $time));
    }

    /**
     * Retrieve archive url by time
     * @param  int $time
     * @return string
     */
    public function getTimeUrl($time)
    {
        if ($this->getGroupBy() == 'year') {
            return $this->_url->getUrl(
                date('Y', $time),
                \Magefan\Blog\Model\Url::CONTROLLER_ARCHIVE
            );
        }

        return $this->_url->getUrl(
            date('Y-m', $time),
            \Magefan\Blog\Model\Url::CONTROLLER_ARCHIVE
        );
    }

    /**
     * Retrieve empty identities
     * Fix for varnish error Error 503 Service Unavailable, when have many blog posts
     *
     * @return array
     */
    public function getIdentities()
    {
        return [];
    }

    /**
     * @param $time
     * @return string
     */
    public function getTranslatedDate($time)
    {
        if ($this->getGroupBy() == 'year') {
            $time = is_numeric($time) ? $time : strtotime((string)$time);
            return date('Y', $time);
        }

        $format = $this->_scopeConfig->getValue(
            'mfblog/sidebar/archive/format_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return \Magefan\Blog\Helper\Data::getTranslatedDate($format, $time);
    }

    /**
     * @return mixed
     */
    protected function getGroupBy()
    {
        return $this->_scopeConfig->getValue(
            'mfblog/sidebar/archive/group_by',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
