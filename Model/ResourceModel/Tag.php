<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel;

/**
 * Blog tag resource model
 */
class Tag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magefan_blog_tag', 'tag_id');
    }

    /**
     * Process tag data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['tag_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('magefan_blog_post_tag'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process tag data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setTitle(
            trim(strtolower($object->getTitle()))
        );

        if (!$object->getId()) {
            $tag = $object->getCollection()
                ->addFieldToFilter('title', $object->getTitle())
                ->setPageSize(1)
                ->getFirstItem();
            if ($tag->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The tag is already exist.')
                );
            }
        }

        $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magefan\Blog\Model\ResourceModel\PageIdentifierGenerator');
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The tag URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The tag URL key cannot be made of only numbers.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     *  Check whether tag identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether tag identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^([^?#<>@!&*()$%^\\/+=,{}]+)?$/', $object->getData('identifier'));
    }
}
