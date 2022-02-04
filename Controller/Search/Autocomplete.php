<?php

namespace Magefan\Blog\Controller\Search;

use Magefan\Blog\Model\PostRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Autocomplete extends \Magento\Framework\App\Action\Action
{

    protected $filterBuilder;
    protected $searchCriteriaBuilder;
    protected $request;
    protected $postRepository;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        PostRepository $postRepository,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request = $request;
        $this->postRepository = $postRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $searchTerm = $this->request->getParam('searchTerm');

        $titleFilter = $this->filterBuilder
            ->setField('title')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();
        $contentFilter = $this->filterBuilder
            ->setField('content')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();
        $contentHeadingFilter = $this->filterBuilder
            ->setField('content_heading')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();
        $metaDescriptionFilter = $this->filterBuilder
            ->setField('meta_description')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();

        $filterGroup = [$titleFilter, $contentFilter, $contentHeadingFilter, $metaDescriptionFilter];
        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filterGroup)->create();

        $searchResult = $this->postRepository->getList($searchCriteria);
        $postList = [];
        $postData = ['count' => $searchResult->getTotalCount(), 'docs' => $postList];
        if($searchResult->getTotalCount()) {
            foreach ($searchResult->getItems() as $post) {
                $postId = $post['post_id'];
                $data = [
                    'id' => $postId,
                    'title' => $post['title'],
                    'url' => $this->postRepository->getById($postId)->getPostUrl(),
                ];
                $postList[] = $data;
            }
        }
        $postData['docs'] = $postList;
        $result = $this->resultJsonFactory->create();
        return $result->setData($postData);
    }
}