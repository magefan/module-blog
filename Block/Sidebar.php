<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog sidebar block
 */
class Sidebar extends \Magento\Framework\View\Element\Text
{

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setText('');
        $childNames = $this->getChildNames();

        usort($childNames, [$this, 'sortChilds']);

        $layout = $this->getLayout();
        foreach ($childNames as $child) {
            $this->addText($layout->renderElement($child, false));
        }

        return parent::_toHtml();
    }

    /**
     * Sort by sort order param
     * @param  string $a
     * @param  string $b
     * @return boolean
     */
    public function sortChilds($a, $b)
    {
        $layout = $this->getLayout();
        $blockA = $layout->getBlock($a);
        $blockB = $layout->getBlock($b);
        if ($blockA && $blockB) {
            $r = $blockA->getSortOrder() > $blockB->getSortOrder() ? 1 : - 1;
            return $r;
        }
    }
}
