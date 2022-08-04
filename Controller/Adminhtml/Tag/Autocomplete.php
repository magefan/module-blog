<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Blog\Controller\Adminhtml\Tag;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Tag Ajax Autocomplete
 */
class Autocomplete extends \Magefan\Blog\Controller\Adminhtml\Tag
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $search = (string)$this->getRequest()->getParam('search');
        $collection = $this->_objectManager->create(\Magefan\Blog\Model\Tag\AutocompleteData::class);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson= $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($collection->getItems($search));
        return $resultJson;
    }
}
