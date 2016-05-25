<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Post\Edit\Tab;

/**
 * Admin blog post edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magefan\Blog\Model\Config\Source\Category
     */
    protected $_categoryOption;

    /**
     * @var \Magefan\Blog\Model\Config\Source\Author
     */
    protected $_authoryOption;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magefan\Blog\Model\Config\Source\Category $categoryOption
     * @param \Magefan\Blog\Model\Config\Source\Author $authoryOption
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magefan\Blog\Model\Config\Source\Category $categoryOption,
        \Magefan\Blog\Model\Config\Source\Author $authoryOption,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_categoryOption = $categoryOption;
        $this->_authoryOption = $authoryOption;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Magefan_Blog::post');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Post Information')]);

        if ($model->getId()) {
            $fieldset->addField('post_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'post[title]',
                'label' => __('Post Title'),
                'title' => __('Post Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'post[identifier]',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'class' => 'validate-identifier',
                'note' => __('Relative to Web Site Base URL'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Post Status'),
                'name' => 'post[is_active]',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $field = $fieldset->addField(
            'author_id',
            'select',
            [
                'name' => 'post[author_id]',
                'label' => __('Author'),
                'title' => __('Author'),
                'values' => $this->_authoryOption->toOptionArray(),
                'disabled' => $isElementDisabled,
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'post[store_ids][]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled,
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_ids',
                'hidden',
                ['name' => 'post[store_ids][]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreIds($this->_storeManager->getStore(true)->getId());
        }

        $field = $fieldset->addField(
            'categories',
            'multiselect',
            [
                'name' => 'post[categories][]',
                'label' => __('Categories'),
                'title' => __('Categories'),
                'values' => $this->_categoryOption->toOptionArray(),
                'disabled' => $isElementDisabled,
                'style' => 'width:100%',
            ]
        );

        if (is_array($model->getData('featured_img'))) {
            $model->setData('featured_img', $model->getData('featured_img')['value']);
        }
        $fieldset->addField(
            'featured_img',
            'image',
            [
                'title' => __('Featured Image'),
                'label' => __('Featured Image'),
                'name' => 'post[featured_img]',
                'note' => __('Allow image type: jpg, jpeg, gif, png'),
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'publish_time',
            'date',
            [
                'name' => 'post[publish_time]',
                'label' => __('Publish At'),
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled
            ]
        );


        $this->_eventManager->dispatch('magefan_blog_post_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Post Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Post Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
