<?php
/**
 * Copyright © 2015-2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Comment;

/**
 * Blog comment status enabled controller
 */
class MassDisabled extends \Magefan\Blog\Controller\Adminhtml\Comment
{
  const ENABLED = 0;

  public function execute() {

    $params = $this->getRequest()->getParams();
    $params['status'] = self::ENABLED;

    $this->_forward('massStatus', null, null, $params);

  }
}
