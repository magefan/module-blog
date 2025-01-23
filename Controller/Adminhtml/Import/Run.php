<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
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
        //set_time_limit(0);

        $data = $this->getRequest()->getPost();
        $type = (string)$this->getRequest()->getParam('type');

        try {
            if (empty($type)) {
                throw new \Exception(__('Blog import type is not specified.'), 1);
            }

            if (!isset($data['store_id'])) {
                $params = $this->getRequest()->getParams();
                if (isset($params['store_id'])) {
                    $data['store_id'] = $params['store_id'];
                }
            }

            $_type = ucfirst($type);

            $import = $this->_objectManager->create('\Magefan\Blog\Model\Import\\'.$_type);
            $import->prepareData($data)->execute();

            $stats = $import->getImportStatistic();

            if ($stats->getData('imported_count')) {
                if (!$stats->getData('skipped_count')) {
                    $this->messageManager->addSuccess(__(
                        'The import process was completed successfully.
                        %1 posts, %2 categories, %3 tags, %4 authors and %5 comments where imported.',
                        $stats->getData('imported_posts_count'),
                        $stats->getData('imported_categories_count'),
                        $stats->getData('imported_tags_count'),
                        $stats->getData('imported_authors_count'),
                        $stats->getData('imported_comments_count')
                    ));
                } else {
                    $this->messageManager->addNotice(__(
                        'The import process completed. %1 posts, %2 categories, %3 tags, %4 authors and %5 comments where imported.
                        Some posts or categories or tags or authors or comments where skipped. %6 %7 %8 %9 %10',
                        $stats->getData('imported_posts_count'),
                        $stats->getData('imported_categories_count'),
                        $stats->getData('imported_tags_count'),
                        $stats->getData('imported_authors_count'),
                        $stats->getData('imported_comments_count'),
                        $stats->getData('skipped_posts') ? (__('Skipped Posts') .
                            ': '. implode(', ', $stats->getData('skipped_posts')) . '. ') : '',
                        $stats->getData('skipped_categories') ? (__('Skipped Categories') .
                            ': '. implode(', ', $stats->getData('skipped_categories')) . '. ') : '',
                        $stats->getData('skipped_tags') ? (__('Skipped Tags') .
                            ': '. implode(', ', $stats->getData('skipped_tags')) . '. ') : '',
                        $stats->getData('skipped_authors') ? (__('Skipped Authors') .
                            ': '. implode(', ', $stats->getData('skipped_authors')) . '. ') : '',
                        $stats->getData('skipped_comments') ? (__('Skipped Comments') .
                            ': '. implode(', ', $stats->getData('skipped_comments')) . '. ') : ''
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
            $this->_getSession()->setData('import_' . $type . '_form_data', $data);
            if ($formPath = (string)$this->getRequest()->getParam('form')) {
                $this->_redirect(str_replace('_', '/', $formPath));
            } else {
                $this->_redirect('*/*/form', ['type' => $type]);
            }

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
