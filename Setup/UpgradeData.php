<?php

namespace Magefan\Blog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magefan\Blog\Model\ResourceModel\Comment;
use Magefan\Blog\Model\Config;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Status;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Comment
     */
    protected $commentResource;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Status
     */
    protected $moduleStatus;

    /**
     * UpgradeData constructor.
     * @param Comment $commentResource
     * @param Config $config
     * @param ModuleListInterface $moduleList
     * @param Status $moduleStatus
     */
    public function __construct(
        Comment $commentResource,
        Config $config,
        ModuleListInterface $moduleList,
        Status $moduleStatus
    ) {
        $this->commentResource = $commentResource;
        $this->config = $config;
        $this->moduleList = $moduleList;
        $this->moduleStatus = $moduleStatus;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->disableAnotherBlogModules();
        if (version_compare($context->getVersion(), '2.9.1') < 0) {
            $connection = $this->commentResource->getConnection();
            $postSelect = $connection->select()->from(
                [$this->commentResource->getTable('magefan_blog_post')]
            )
                ->where('is_active = ?', 1);
            $posts = $connection->fetchAll($postSelect);
            foreach ($posts as $post) {
                $this->commentResource->updatePostCommentsCount($post['post_id']);
            }
        }
    }

    /**
     * Disable all modules witch have Blog part on his name
     */
    public function disableAnotherBlogModules()
    {
        $disableModules = [];

        foreach ($this->moduleList->getNames() as $module) {
            if (false === strpos($module, '_')) {
                continue;
            }

            list($vendor, $name) = explode('_' , $module);
            if ('Magefan' == $vendor) {
                continue;
            }

            if ('Blog' == $name) {
                $disableModules[] = $module;
            }
        }
        if (count($disableModules)) {
            try {
                $this->moduleStatus->setIsEnabled(false, $disableModules);
            } catch(\Exception $e) {
                \Magento\Framework\Exception\LocalizedException(
                    __('Please remove "%1" module/module\'s manually.', implode(',', $disableModules)));
            }
        }
    }
}
