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

namespace SwagProductAdvisor\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Media\Media;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper as CoreHelper;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Models\Answer;
use SwagProductAdvisor\Models\Question;
use SwagProductAdvisor\Tests\Helper\QuestionHelper;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    private $createdAdvisors = [];
    private $createdFilteredProductStreams = [];

    /**
     * Helper constructor.
     */
    public function __construct()
    {
        $this->coreHelper = new CoreHelper();
        $this->entityManager = Shopware()->Models();
        $this->container = Shopware()->Container();
        $this->questionHelper = new QuestionHelper();
        $this->registerNamespaces();
    }

    /**
     * @return QuestionHelper
     */
    public function getQuestionHelper()
    {
        return $this->questionHelper;
    }

    /**
     * Register the PluginNamespace
     */
    public function registerNamespaces()
    {
        $loader = $this->container->get('loader');
        $dir = dirname(__DIR__);

        $loader->registerNamespace('SwagProductAdvisor\Models', $dir . '/Models/');

        $this->container->get('modelannotations')->addPaths([$dir . '/Models/']);
    }

    /**
     * @param array $data
     *
     * @return Advisor
     */
    public function createAdvisor(array $data = [])
    {
        $advisor = new Advisor();
        $advisorData = array_merge($this->getAdvisorData(), $data);

        $advisor->fromArray($advisorData);

        $this->entityManager->persist($advisor);
        $this->entityManager->flush();

        $this->createdAdvisors[] = $advisor->getId();

        return $advisor;
    }

    /**
     * @param array $data
     *
     * @return ProductStream
     */
    public function createFilteredProductStream(array $data)
    {
        $data = array_merge($this->getFilteredProductStreamData(), $data);
        $productStream = new ProductStream();
        $productStream->fromArray($data);

        $this->entityManager->persist($productStream);
        $this->entityManager->flush();

        $this->createdFilteredProductStreams[] = $productStream->getId();

        return $productStream;
    }

    /**
     * @return \Shopware\Models\Media\Media
     */
    public function getTeaserBanner()
    {
        return $this->entityManager->getRepository(Media::class)->find(768);
    }

    /**
     * Cleans up all persisted entities.
     */
    public function cleanUp()
    {
        foreach ($this->createdAdvisors as $advisor) {
            $this->removeAdvisor($advisor);
        }

        foreach ($this->createdFilteredProductStreams as $productStream) {
            $this->removeProductStream($productStream);
        }

        $this->coreHelper->cleanUp();
    }

    /**
     * @param array $params
     *
     * @return \Enlight_Controller_Request_RequestHttp
     */
    public function createRequest(array $params = [])
    {
        $request = new \Enlight_Controller_Request_RequestHttp();

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $request->setParam($key, $value);
            }
        }

        return $request;
    }

    /**
     * Returns a default set of given answers due to the default questions.
     *
     * @param Question[] $questions
     *
     * @return array
     */
    public function getAnsweredQuestions(array $questions)
    {
        $attributeQuestion = $questions[0];
        $propertyQuestion = $questions[1];
        $manufacturerQuestion = $questions[2];

        $attributeAnswerSet = [
            $attributeQuestion->getAnswers()[0]->getId(),
            $attributeQuestion->getAnswers()[2]->getId(),
        ];

        $propertyQuestionAnswerSet = [
            $propertyQuestion->getAnswers()[3]->getId(),
            $propertyQuestion->getAnswers()[4]->getId(),
        ];

        $manufacturerQuestionAnswerSet = [
            $manufacturerQuestion->getAnswers()[0]->getId(),
        ];

        // Do not change the order!
        $answerArray["q{$attributeQuestion->getId()}_values"] = implode('|', $attributeAnswerSet);
        $answerArray["q{$propertyQuestion->getId()}_values"] = implode('|', $propertyQuestionAnswerSet);
        $answerArray["q{$manufacturerQuestion->getId()}_values"] = implode('|', $manufacturerQuestionAnswerSet);

        return $answerArray;
    }

    /**
     * @return array
     */
    public function getQuestions()
    {
        $questions = [];
        $questionArray = $this->questionHelper->getMixedQuestions();

        /** @var \SwagProductAdvisor\Bundle\AdvisorBundle\Question\Question $question */
        foreach ($questionArray as $question) {
            $answerArray = $question['answers'];
            $answers = [];

            foreach ($answerArray as $answer) {
                $answerObject = new Answer();
                $answerObject->fromArray($answer);
                $answers[] = $answerObject;
            }

            $questionObject = new Question();
            $questionObject->fromArray($question);
            $questionObject->setAnswers(new ArrayCollection($answers));
            $questions[] = $questionObject;
        }

        return $questions;
    }

    /**
     * Returns some basic example data for an advisor.
     *
     * @return array
     */
    private function getAdvisorData()
    {
        $basicData = [
            'active' => 1,
            'name' => 'Example advisor',
            'description' => 'Example Product Advisor Description Lorem ipsum dolor sit amet',
            'infoLinkText' => 'Additional information',
            'buttonText' => 'Search now',
            'remainingPostsTitle' => 'Unfiltered listing',
            'listingTitleFiltered' => 'Filtered listing title',
            'highlightTopHit' => true,
            'topHitTitle' => 'Perfect hit',
            'minMatchingAttributes' => 2,
            'listingLayout' => 'show_matches_and_misses',
            'mode' => 'sidebar_mode',
            'lastListingSort' => 'DESC',
        ];
        $associationData = $this->getAssociatedData();

        return array_merge($basicData, $associationData);
    }

    /**
     * Returns the associated data for the advisor, e.g. the stream or the teaser-banner.
     *
     * @return array
     */
    private function getAssociatedData()
    {
        $productStream = $this->createFilteredProductStream(
            [
                'name' => 'Example Advisor Stream',
                'description' => 'Example Product Stream Description Lorem ipsum dolor sit amet',
                'type' => 1,
            ]
        );

        return [
            'stream' => $productStream,
            'teaserBanner' => $this->getTeaserBanner(),
            'questions' => $this->getQuestions(),
        ];
    }

    /**
     * Returns the conditions and the sorting for the product-stream
     *
     * @return array
     */
    private function getFilteredProductStreamData()
    {
        return [
            'conditions' => '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Condition\\\\CategoryCondition":{"categoryIds":[3]}}',
            'sorting' => '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\PriceSorting":{"direction":"desc"}}',
        ];
    }

    /**
     * Removes the advisor entity by the given id.
     *
     * @param int $advisorId
     */
    private function removeAdvisor($advisorId)
    {
        $advisor = $this->entityManager->find(Advisor::class, $advisorId);
        $this->entityManager->remove($advisor);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Removes the product-stream entity by the given id
     *
     * @param int $productStreamId
     */
    private function removeProductStream($productStreamId)
    {
        $productStream = $this->entityManager->find(ProductStream::class, $productStreamId);
        $this->entityManager->remove($productStream);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
