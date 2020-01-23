<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Block\Widget\Link;

use Magento\Framework\View\Element\Template;
use Magefan\Blog\Model\Url;

/**
 * Class Link for Category
 */
class CategoryLink extends \Magefan\Blog\Block\Widget\Link\AdstractLink
{

    /**
     * CategoryLink constructor.
     * @param Template\Context $context
     * @param Url $urlFinder
     * @param string $className
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $urlFinder,
        $className = 'category',
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlFinder,
            $className,
            $data
        );
    }
}
