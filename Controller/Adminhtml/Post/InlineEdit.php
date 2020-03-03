<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magefan\Blog\Api\PostRepositoryInterface as PostRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magefan\Blog\Model\Post;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;

/**
 * Blog Post grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_Blog::post';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param PostRepository $postRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        PostRepository $postRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->postRepository = $postRepository;
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

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $postId) {
            /** @var \Magefan\Blog\Model\Post $post */
            $post = $this->postRepository->getById($postId);
            try {
                $postData = $this->filterPost($postItems[$postId]);
                $this->validatePost($postData, $post, $error, $messages);
                $extendedPostData = $post->getData();
                $this->setCmsPostData($post, $extendedPostData, $postData);
                $this->postRepository->save($post);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPostId(
                    $post,
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
     * @param array $blogPostData
     * @return array
     */
    protected function filterPost($blogPostData = [])
    {
        $blogPostData = $this->dataProcessor->filter($blogPostData);
        $blogPostData['custom_theme'] = isset($blogPostData['custom_theme']) ? $blogPostData['custom_theme'] : null;
        $blogPostData['custom_root_template'] = isset($blogPostData['custom_root_template'])
            ? $blogPostData['custom_root_template']
            : null;
        return $blogPostData;
    }

    /**
     * Validate POST data
     *
     * @param array $blogPostData
     * @param Post $post
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $blogPostData, Post $post, &$error, array &$messages)
    {
        if (!$this->dataProcessor->validateRequireEntry($blogPostData)) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithPostId($post, $error->getText());
            }
        }
    }

    /**
     * Add post title to error message
     *
     * @param Post $post
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPostId(Post $post, $errorText)
    {
        return '[Post ID: ' . $post->getId() . '] ' . $errorText;
    }

    /**
     * Set blog post data
     *
     * @param Post $post
     * @param array $extendedPostData
     * @param array $postData
     * @return $this
     */
    public function setCmsPostData(Post $post, array $extendedPostData, array $postData)
    {
        $post->setData(array_merge($post->getData(), $extendedPostData, $postData));
        return $this;
    }
}
