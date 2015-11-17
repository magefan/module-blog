<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Post;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog post info block
 */
class Info extends \Magento\Framework\View\Element\Template
{
	/**
     * Block template file
     * @var string
     */
    protected $_template = 'post/info.phtml';

    /**
     * Retrieve formated posted date
     * @var string
     * @return string
     */
    public function getPostedOn($format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($this->getPost()->getData('publish_time')));
    }

}
