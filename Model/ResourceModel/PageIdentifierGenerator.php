<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\ResourceModel;

/**
 * Page identifier generator
 */
class PageIdentifierGenerator
{
    /**
     * @var \Magefan\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * @var \Magefan\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Construct
     *
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_postFactory = $postFactory;
        $this->_categoryFactory = $categoryFactory;
    }

    public function generate(\Magento\Framework\DataObject $object)
    {
        if ($object->getData('identifier')) {
            return;
        }

        $identifier = $object->getData('title') ? trim($object->getData('title')) : '';
        if (!$identifier) {
            return;
        }

        $from = [
            'Á', 'À', 'Â', 'Ä', 'Ă', 'Ā', 'Ã', 'Å', 'Ą', 'Æ', 'Ć', 'Ċ', 'Ĉ', 'Č', 'Ç', 'Ď', 'Đ', 'Ð', 'É', 'È', 'Ė', 'Ê', 'Ë', 'Ě', 'Ē', 'Ę', 'Ə', 'Ġ', 'Ĝ', 'Ğ', 'Ģ', 'á', 'à', 'â', 'ä', 'ă', 'ā', 'ã', 'å', 'ą', 'æ', 'ć', 'ċ', 'ĉ', 'č', 'ç', 'ď', 'đ', 'ð', 'é', 'è', 'ė', 'ê', 'ë', 'ě', 'ē', 'ę', 'ə', 'ġ', 'ĝ', 'ğ', 'ģ', 'Ĥ', 'Ħ', 'I', 'Í', 'Ì', 'İ', 'Î', 'Ï', 'Ī', 'Į', 'Ĳ', 'Ĵ', 'Ķ', 'Ļ', 'Ł', 'Ń', 'Ň', 'Ñ', 'Ņ', 'Ó', 'Ò', 'Ô', 'Ö', 'Õ', 'Ő', 'Ø', 'Ơ', 'Œ', 'ĥ', 'ħ', 'ı', 'í', 'ì', 'i', 'î', 'ï', 'ī', 'į', 'ĳ', 'ĵ', 'ķ', 'ļ', 'ł', 'ń', 'ň', 'ñ', 'ņ', 'ó', 'ò', 'ô', 'ö', 'õ', 'ő', 'ø', 'ơ', 'œ', 'Ŕ', 'Ř', 'Ś', 'Ŝ', 'Š', 'Ş', 'Ť', 'Ţ', 'Þ', 'Ú', 'Ù', 'Û', 'Ü', 'Ŭ', 'Ū', 'Ů', 'Ų', 'Ű', 'Ư', 'Ŵ', 'Ý', 'Ŷ', 'Ÿ', 'Ź', 'Ż', 'Ž', 'ŕ', 'ř', 'ś', 'ŝ', 'š', 'ş', 'ß', 'ť', 'ţ', 'þ', 'ú', 'ù', 'û', 'ü', 'ŭ', 'ū', 'ů', 'ų', 'ű', 'ư', 'ŵ', 'ý', 'ŷ', 'ÿ', 'ź', 'ż', 'ž',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'І', 'і', 'Ї', 'ї', 'Є', 'є',
            ' & ', '&', ' ', '’', '"', "'", '£', '/', '[', ']', ':', ';', '¬', '¦', '~', '\'', '`', '.',
            "ạ", "ả", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ằ", "ắ", "ặ", "ẳ", "ẵ", "ẹ", "ẻ", "ẽ", "ề", "ế", "ệ", "ể", "ễ", "ị", "ỉ", "ĩ", "ọ", "ỏ", "ồ", "ố", "ộ", "ổ", "ỗ", "ờ", "ớ", "ợ", "ở", "ỡ", "ụ", "ủ", "ũ", "ừ", "ứ", "ự", "ử", "ữ", "ỳ", "ỵ", "ỷ", "ỹ",
            "Ạ", "Ả", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ", "Ẹ", "Ẻ", "Ẽ", "Ề", "Ế", "Ệ", "Ể", "Ễ", "Ị", "Ỉ", "Ĩ", "Ọ", "Ỏ", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ", "Ụ", "Ủ", "Ũ", "Ừ", "Ứ", "Ự", "Ử", "Ữ", "Ỳ", "Ỵ", "Ỷ", "Ỹ"
        ];

        $to = [
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'G', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g', 'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'IJ', 'J', 'K', 'L', 'L', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'CE', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'ij', 'j', 'k', 'l', 'l', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'R', 'R', 'S', 'S', 'S', 'S', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'Y', 'Y', 'Y', 'Z', 'Z', 'Z', 'r', 'r', 's', 's', 's', 's', 'B', 't', 't', 'b', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'y', 'y', 'y', 'z', 'z', 'z',
            'A', 'B', 'V', 'H', 'D', 'e', 'Io', 'Z', 'Z', 'Y', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Ch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia',
            'a', 'b', 'v', 'h', 'd', 'e', 'io', 'z', 'z', 'y', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'ch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia',
            'I', 'i', 'Ji', 'ji', 'Je', 'je',
            '-and-', '-and-', '-', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "e", "e", "e", "e", "e", "e", "e", "e", "i", "i", "i", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "u", "u", "u", "u", "u", "u", "u", "u", "y", "y", "y", "y",
            "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "E", "E", "E", "E", "E", "E", "E", "E", "I", "I", "I", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "U", "U", "U", "U", "U", "U", "U", "U", "Y", "Y", "Y", "Y"
        ];

        $identifier = str_replace($from, $to, $identifier);
        $identifier = mb_strtolower($identifier);
        $identifier = preg_replace('/[?#<>@!&*()$%^\\/+=,{}\s]+/', '-', $identifier);
        $identifier = preg_replace('/[--]+/', '-', $identifier);

        $identifier = trim($identifier, '-');

        $post = $this->_postFactory->create();
        $category = $this->_categoryFactory->create();

        $number = 1;
        while (true) {
            $finalIdentifier = $identifier . ($number > 1 ? '-'.$number : '');

            $postId = $post->checkIdentifier($finalIdentifier, $object->getStoreId());
            $categoryId = $category->checkIdentifier($finalIdentifier, $object->getStoreId());

            if (!$postId && !$categoryId) {
                break;
            } else {
                if ($postId
                    && $postId == $object->getId()
                    && $object instanceof \Magefan\Blog\Model\Post
                ) {
                    break;
                }

                if ($categoryId
                    && $categoryId == $object->getId()
                    && $object instanceof \Magefan\Blog\Model\Category
                ) {
                    break;
                }
            }

            $number++;
        }

        $object->setData('identifier', $finalIdentifier);
    }
}
