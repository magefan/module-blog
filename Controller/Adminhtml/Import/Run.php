<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Import;

/**
 * Run import controller
 */
class Run extends \Magento\Backend\App\Action
{
	/**
     * Run import
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        set_time_limit(0);

        $data = $this->getRequest()->getPost();
        $type = '';
        try {
            if (empty($data['type'])) {
                throw new \Exception(__('Blog import type is not specified.'), 1);
            }

            $_type = ucfirst($data['type']);
            $import = $this->_objectManager->create('\Magefan\Blog\Model\Import\\'.$_type);
            $type = $data['type'];
            $import->prepareData($data)->execute();

            $stats = $import->getImportStatistic();

            if ($stats->getData('imported_count')) {
                if (!$stats->getData('skipped_count')) {
                    $this->messageManager->addSuccess(__(
                        'The import process was completed successfully. %1 posts and %2 categories where imported.',
                        $stats->getData('imported_posts_count'),
                        $stats->getData('imported_categories_count')
                    ));
                } else {
                    $this->messageManager->addNotice(__(
                        'The import process completed. %1 posts and %2 categories and %3 tags where imported. Some posts or categories or tags where skipped.<br/> %3 %4',
                        $stats->getData('imported_posts_count'),
                        $stats->getData('imported_categories_count'),
                        $stats->getData('imported_tags_count'),
                        $stats->getData('skipped_posts') ? __('Skipped Posts') . ': '. implode(', ', $stats->getData('skipped_posts')) . '.<br/>' : '',
                        $stats->getData('skipped_posts') ? __('Skipped Categories') . ': '. implode(', ', $stats->getData('skipped_categories')) . '. ' : '',
                        $stats->getData('skipped_posts') ? __('Skipped Tags') . ': '. implode(', ', $stats->getData('skipped_tags')) . '. ' : ''
                    ));
                }
            } else {
                if (!$stats->getData('skipped_count')) {
                    $this->messageManager->addNotice(__('Nothing to import.'));
                } else {
                    throw new \Exception(__('Can not make import.'), 1);
                }
            }

            $this->_getSession()->setData('import_'.$type.'_form_data', null);
            $this->_redirect('*/*/');

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong: ').' '.$e->getMessage());
            $this->_getSession()->setData('import_'.$type.'_form_data', $data);
            $this->_redirect('*/*/'.$type);
        }
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_Blog::import');
    }
}
