<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Ui\Component\Source\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magefan\Blog\Model\AuthorRepository;

/**
 * Class Author
 */
class Author extends Column
{
    /**
     * @var AuthorRepository
     */
    protected $authorRepository;

    /**
     * Author constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AuthorRepository $authorRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AuthorRepository $authorRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->authorRepository = $authorRepository;
    }

    /**
     * Prepare Data Source for actions column on dynamic grid
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['author_id'])) {
                    $author = $this->authorRepository->getById($item['author_id']);
                    $item[$this->getData('name')] = [
                        $author->getFirstname() . ' ' . $author->getLastname()
                    ];
                }
            }
        }
        return $dataSource;
    }
}
