<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Ajax Autocomplete
 */
class Autocomplete extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $search = $this->getRequest()->getParam('search');
        $collection = $this->_objectManager->create('Magefan\Blog\Model\AutocompleteData\PostsTags');

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson= $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($collection->getItems($search));
        return $resultJson;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
