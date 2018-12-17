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

namespace SwagProductAdvisor\Components;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use Shopware\Models\ProductStream\ProductStream;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Models\Answer;
use SwagProductAdvisor\Models\Question;

class DeHydration implements DeHydrationInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Media
     */
    private $mediaManager;

    /**
     * DeHydration constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->mediaManager = $this->modelManager->getRepository(Media::class);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateAdvisor(array $data)
    {
        $stream = $this->getStreams($data['streamId']);
        $questions = $this->getQuestions($data);
        $teaserBanner = $this->mediaManager->find($data['teaserBannerId']);

        if (empty($data['id'])) {
            $productAdvisor = new Advisor();
        } else {
            $productAdvisorRepository = $this->modelManager->getRepository(Advisor::class);
            $productAdvisor = $productAdvisorRepository->find($data['id']);
        }

        /** @var Advisor $productAdvisor */
        $productAdvisor = $productAdvisor->fromArray($data);
        $productAdvisor->setStream($stream);
        $productAdvisor->setQuestions($questions);
        $productAdvisor->setTeaserBanner($teaserBanner);

        return $productAdvisor;
    }

    /**
     * {@inheritdoc}
     */
    public function dehydrateProductNumberSearchResult(ProductNumberSearchResult $searchResult)
    {
        $dehydratedSearchResult = [];

        /** @var BaseProduct $product */
        foreach ($searchResult->getProducts() as $product) {
            $dehydratedSearchResult[] = $product->getId();
        }

        return $dehydratedSearchResult;
    }

    /**
     * @param array $data
     *
     * @return ArrayCollection
     */
    private function getQuestions(array $data)
    {
        $questionManager = $this->modelManager->getRepository(Question::class);
        $questions = [];

        foreach ($data['questions'] as $questionData) {
            $question = null;

            if (!empty($questionData['id'])) {
                $question = $questionManager->findOneBy(['id' => $questionData['id']]);
            }

            if (!$question) {
                $question = new Question();
            }

            $question->fromArray($questionData);
            $answers = $this->getAnswers($questionData['answers'], $question);

            $question->setAnswers($answers);

            $questions[] = $question;
        }

        return new ArrayCollection($questions);
    }

    private function getAnswers(array $answers)
    {
        $answerManager = $this->modelManager->getRepository(Answer::class);
        $answerArray = [];

        foreach ($answers as $answer) {
            if (empty($answer['media'])) {
                $answer['media'] = null;
            }

            if (!empty($answer['id'])) {
                $answerModel = $answerManager->find($answer['id']);
            }

            if (!$answerModel) {
                $answerModel = new Answer();
            }

            $answerModel->fromArray($answer);

            if ($answer['media']) {
                $answerModel->setMedia($this->mediaManager->find($answer['media']));
            }

            $answerArray[] = $answerModel;
        }

        return $answerArray;
    }

    /**
     * @param int $stream
     *
     * @return ProductStream null
     */
    private function getStreams($stream)
    {
        if (empty($stream)) {
            return null;
        }

        $streamRepository = $this->modelManager->getRepository(ProductStream::class);

        return $streamRepository->find($stream);
    }
}
