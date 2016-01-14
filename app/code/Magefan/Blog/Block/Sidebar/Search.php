<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Sidebar;

/**
 * Blog sidebar categories block
 */
class Search extends \Magento\Framework\View\Element\Template
{
	use Widget;

	/**
     * @var \Magefan\Blog\Model\Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magefan\Blog\Model\Url $url
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_url = $url;
    }

	/**
     * @var string
     */
    protected $_widgetKey = 'search';

	/**
	 * Retrieve query
	 * @return string
	 */
	public function getQuery()
	{
		return urldecode($this->getRequest()->getParam('q', ''));
	}

	/**
	 * Retrieve serch form action url
	 * @return string
	 */
	public function getFormUrl()
	{
		return $this->_url->getUrl('', \Magefan\Blog\Model\Url::CONTROLLER_SEARCH);
	}

}
