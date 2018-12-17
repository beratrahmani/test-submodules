<?php

/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question;

use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Class QuestionFactory
 */
class QuestionFactory implements QuestionFactoryInterface
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * QuestionFactory constructor.
     *
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(MediaServiceInterface $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function supports(array $data)
    {
        return in_array($data['type'], ['property', 'manufacturer']);
    }

    /**
     * @param array                $data
     * @param ShopContextInterface $context
     * @param array                $post
     *
     * @return Question
     */
    public function factory(array $data, ShopContextInterface $context, array $post)
    {
        $question = new Question(
            $data['id'],
            $data['question'],
            $data['template'],
            $data['type'],
            $data['exclude'],
            !empty($post),
            $data['info_text'],
            $data['needs_to_be_answered'],
            $data['expand_question'],
            $data['boost'],
            $data['number_of_columns'],
            $data['number_of_rows'],
            $data['column_height'],
            $data['hide_text']
        );

        if (!empty($post)) {
            $post = explode('|', $post['values']);
        }

        $mediaIds = array_column($data['data'], 'mediaId');
        $media = [];
        if (!empty($mediaIds)) {
            $media = $this->mediaService->getList($mediaIds, $context);
        }

        foreach ($data['data'] as $answerData) {
            $answerMedia = null;
            if (isset($media[$answerData['mediaId']])) {
                $answerMedia = $media[$answerData['mediaId']];
            }

            $answer = new Answer(
                $answerData['id'],
                $answerData['key'],
                $answerData['value'],
                $answerData['label'],
                in_array($answerData['id'], $post),
                $answerData['css'],
                $answerMedia,
                $answerData['columnId'],
                $answerData['rowId']
            );

            $question->addAnswer($answer);
        }

        return $question;
    }
}
