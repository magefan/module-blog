<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Grid\Column\Render;

/**
 * Author column renderer
 */
class Author extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magefan\Blog\Model\AuthorFactory
     */
    protected $authoryFactory;

    /**
     * @var array
     */
    static protected $authors = [];

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magefan\Blog\Model\AuthorFactory $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magefan\Blog\Model\AuthorFactory $authorFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->authorFactory = $authorFactory;
    }

    /**
     * Render author grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($id = $row->getData($this->getColumn()->getIndex())) {
            $title = $this->getAuthorById($id)->getTitle();
            if ($title) {
                return $title;
            }
        }
        return null;
    }

    /**
     * Retrieve author by id
     *
     * @param   int $id
     * @return  \Magefan\Blog\Model\Author
     */
    protected function getAuthorById($id)
    {
        if (!isset(self::$authors[$id])) {
            self::$authors[$id] = $this->authorFactory->create()->load($id);
        }
        return self::$authors[$id];
    }
}
