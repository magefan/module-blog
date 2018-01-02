<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Ui\DataProvider\Comment\Related;

use \Magento\Ui\DataProvider\AbstractDataProvider;
use Magefan\Blog\Model\ResourceModel\Comment\Collection\GridFactory as CollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class CommentDataProvider
 */
class CommentDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var comment
     */
    private $comment;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();

        if (!$this->getComment()) {
            return $collection;
        }

        $collection->addFieldToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getComment()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Retrieve comment
     *
     * @return CommentInterface|null
     */
    protected function getComment()
    {
        if (null !== $this->comment) {
            return $this->comment;
        }

        if (!($id = $this->request->getParam('current_comment_id'))) {
            return null;
        }

        return $this->comment = $this->commentRepository->getById($id);
    }
}
