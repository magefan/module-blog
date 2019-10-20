<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\Blog\Ui\DataProvider\Comment\Form;

use Magefan\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class CommentDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magefan\Blog\Model\ResourceModel\comment\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * CommentDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $commentCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\UrlInterface $url
     * @param array $meta
     * @param array $data
     * @param \Magento\Framework\Escaper|null $escaper
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $commentCollectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\UrlInterface $url,
        array $meta = [],
        array $data = [],
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->collection = $commentCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->url = $url;

        $this->escaper = $escaper ?: \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Framework\Escaper::class
        );
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $comment \Magefan\Blog\Model\Comment */
        foreach ($items as $comment) {
            $this->loadedData[$comment->getId()] = $comment->getData();

            $post = $comment->getPost();
            $this->loadedData[$comment->getId()]['post_url'] = [
                'url' => $this->url->getUrl('blog/post/edit', ['id' => $post->getId()]),
                'title' => $post->getTitle(),
                'text' => '#' . $post->getId() . '. ' . $post->getTitle(),
            ];

            $author = $comment->getAuthor();
            switch ($comment->getAuthorType()) {
                case \Magefan\Blog\Model\Config\Source\AuthorType::GUEST:
                    $this->loadedData[$comment->getId()]['author_url'] = [
                        'url' => 'mailto:' . $author->getEmail(),
                        'title' => $author->getNickname(),
                        'text' => $author->getNickname() .
                            ' - ' . $author->getEmail() .
                            ' (' . __('Guest')  . ')',
                    ];
                    break;
                case \Magefan\Blog\Model\Config\Source\AuthorType::CUSTOMER:
                    $this->loadedData[$comment->getId()]['author_url'] = [
                        'url' => $this->url->getUrl(
                            'customer/index/edit',
                            ['id' => $comment->getCustomerId()]
                        ),
                        'title' => $author->getNickname(),
                        'text' => '#' . $comment->getCustomerId() .
                            '. ' . $author->getNickname() .
                            ' (' . __('Customer')  . ')',
                    ];
                    break;
                case \Magefan\Blog\Model\Config\Source\AuthorType::ADMIN:
                    $this->loadedData[$comment->getId()]['author_url'] = [
                        'url' => $this->url->getUrl(
                            'admin/user/edit',
                            ['id' => $comment->getAdminId()]
                        ),
                        'title' => $author->getNickname(),
                        'text' => '#' . $comment->getAdminId() .
                            '. ' . $author->getNickname() .
                            ' (' . __('Admin')  . ')',
                    ];
                    break;
            }

            if ($comment->getParentId()
                && ($parentComment = $comment->getParentComment())
            ) {
                $text = (mb_strlen($parentComment->getText()) > 200) ?
                    (mb_substr($parentComment->getText(), 0, 200) . '...') :
                    $parentComment->getText();
                $text = $this->escaper->escapeHtml($text);
                $this->loadedData[$comment->getId()]['parent_url'] = [
                    'url' => $this->url->getUrl('blog/comment/edit', ['id' => $parentComment->getId()]),
                    'title' => $this->escaper->escapeHtml($parentComment->getText()),
                    'text' => '#' . $parentComment->getId() . '. ' . $text,
                ];
            } else {
                $this->loadedData[$comment->getId()]['parent_url'] = [
                    'url' => '',
                    'title' => '',
                    'text' => '',
                ];
            }
        }

        $data = $this->dataPersistor->get('blog_comment_form_data');
        if (!empty($data)) {
            $comment = $this->collection->getNewEmptyItem();
            $comment->setData($data);
            $this->loadedData[$comment->getId()] = $comment->getData();
            $this->dataPersistor->clear('blog_comment_form_data');
        }

        return $this->loadedData;
    }
}
