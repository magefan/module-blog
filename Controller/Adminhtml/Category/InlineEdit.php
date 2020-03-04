<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magefan\Blog\Api\CategoryRepositoryInterface as CategoryRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magefan\Blog\Model\Category;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;

/**
 * Blog Category grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_Blog::category';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param CategoryRepository $categoryRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        CategoryRepository $categoryRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->categoryRepository = $categoryRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Process the request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $categoryItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($categoryItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($categoryItems) as $categoryId) {
            /** @var \Magefan\Blog\Model\Category $category */
            $category = $this->categoryRepository->getById($categoryId);
            try {
                $categoryData = $this->filterPost($categoryItems[$categoryId]);
                $this->validatePost($categoryData, $category, $error, $messages);
                $extendedCategoryData = $category->getData();
                $this->setBlogCategoryData($category, $extendedCategoryData, $categoryData);
                $this->categoryRepository->save($category);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPostId($category, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPostId($category, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPostId(
                    $category,
                    __('Something went wrong while saving the post.')
                );
                $error = true;
            }
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }

    /**
     * Filtering POSTed data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $blogCategoryData = $this->dataProcessor->filter($postData);
        $blogCategoryData['custom_theme'] = isset($blogCategoryData['custom_theme']) ? $blogCategoryData['custom_theme'] : null;
        $blogCategoryData['custom_root_template'] = isset($blogCategoryData['custom_root_template'])
            ? $blogCategoryData['custom_root_template']
            : null;
        return $blogCategoryData;
    }

    /**
     * Validate POST data
     *
     * @param array $blogCategoryData
     * @param Category $category
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $blogCategoryData, Category $category, &$error, array &$messages)
    {
        if (!$this->dataProcessor->validateRequireEntry($blogCategoryData)) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithPostId($category, $error->getText());
            }
        }
    }

    /**
     * Add post title to error message
     *
     * @param Category $category
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPostId(Category $category, $errorText)
    {
        return '[Category ID: ' . $category->getId() . '] ' . $errorText;
    }

    /**
     * Set blog category data
     *
     * @param Category $category
     * @param array $extendedCategoryData
     * @param array $categoryData
     * @return $this
     */
    public function setBlogCategoryData(Category $category, array $extendedCategoryData, array $categoryData)
    {
        $category->setData(array_merge($category->getData(), $extendedCategoryData, $categoryData));
        return $this;
    }
}
