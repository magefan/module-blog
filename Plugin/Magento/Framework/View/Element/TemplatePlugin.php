<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Plugin\Magento\Framework\View\Element;

use Magento\Framework\Module\Manager;

class TemplatePlugin
{

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * TemplatePlugin constructor.
     * @param Manager $moduleManager
     */
    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\View\Element\Template $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetTemplate(\Magento\Framework\View\Element\Template $subject, callable $proceed)
    {
        if ('aminfotab.conflicts' === $subject->getNameInLayout() && $this->moduleManager->isEnabled('Amasty_Base')) {
            foreach ($conflictsMessages = $subject->getConflictsMessages() as $key => $conflictsMessage) {
                if (strpos($conflictsMessage, 'Magefan')) {
                    return '';
                }
            }
        }

        return $proceed();
    }
}
