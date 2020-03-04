<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action\Context;
use Magefan\Blog\Api\TagRepositoryInterface as TagRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magefan\Blog\Model\Tag;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;

/**
 * Blog Tag grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_Blog::tag_save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param TagRepository $tagRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        TagRepository $tagRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->tagRepository = $tagRepository;
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

        $tagItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($tagItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($tagItems) as $tagId) {
            /** @var \Magefan\Blog\Model\Tag $tag */
            $tag = $this->tagRepository->getById($tagId);
            try {
                $tagData = $this->filterPost($tagItems[$tagId]);
                $this->validatePost($tagData, $tag, $error, $messages);
                $extendedTagData = $tag->getData();
                $this->setBlogTagData($tag, $extendedTagData, $tagData);
                $this->tagRepository->save($tag);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPostId($tag, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPostId($tag, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPostId(
                    $tag,
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
        $blogTagData = $this->dataProcessor->filter($postData);
        $blogTagData['custom_theme'] = isset($blogTagData['custom_theme']) ? $blogTagData['custom_theme'] : null;
        $blogTagData['custom_root_template'] = isset($blogTagData['custom_root_template'])
            ? $blogTagData['custom_root_template']
            : null;
        return $blogTagData;
    }

    /**
     * Validate POST data
     *
     * @param array $blogTagData
     * @param Tag $tag
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $blogTagData, Tag $tag, &$error, array &$messages)
    {
        if (!$this->dataProcessor->validateRequireEntry($blogTagData)) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithPostId($tag, $error->getText());
            }
        }
    }

    /**
     * Add post title to error message
     *
     * @param Tag $tag
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPostId(Tag $tag, $errorText)
    {
        return '[Tag ID: ' . $tag->getId() . '] ' . $errorText;
    }

    /**
     * Set blog tag data
     *
     * @param Tag $tag
     * @param array $extendedTagData
     * @param array $tagData
     * @return $this
     */
    public function setBlogTagData(Tag $tag, array $extendedTagData, array $tagData)
    {
        $tag->setData(array_merge($tag->getData(), $extendedTagData, $tagData));
        return $this;
    }
}
