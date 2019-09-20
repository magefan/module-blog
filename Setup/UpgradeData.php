<?php

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magefan\Blog\Model\ResourceModel\Comment;
use Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    protected $commentResource;

    protected $_commentCollection;

    public function __construct(
        Comment $commentResource,
        CollectionFactory $commentCollection
    )
    {
        $this->_commentCollection = $commentCollection;
        $this->commentResource = $commentResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(),'2.9.1.2') < 0)  {
            $comments = $this->_commentCollection->create();
            foreach ($comments as $comment)
            $this->commentResource->afterSave($comment);
        }
    }
}