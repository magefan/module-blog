<?php

declare(strict_types=1);

/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Adminhtml\Grid\Column;

/**
 * Admin blog grid statuses
 */
class Statuses extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add to column decorated status
     *
     * @return array
     */
    public function getFrameCallback(): array
    {
        return [$this, 'decorateStatus'];
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param  \Magento\Framework\Model\AbstractModel $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     */
    public function decorateStatus(string $value, $row, $column, $isExport): string
    {
        if ($row->getIsActive() || $row->getStatus()) {
            if ($row->getStatus() == 2) {
                $cell = '<span class="grid-severity-minor"><span>' . $value . '</span></span>';
            } else {
                $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
            }
        } else {
            $cell = '<span class="grid-severity-critical"><span>' . $value . '</span></span>';
        }
        return $cell;
    }
}
