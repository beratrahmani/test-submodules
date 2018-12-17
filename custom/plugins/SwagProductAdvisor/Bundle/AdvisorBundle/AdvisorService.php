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

namespace SwagProductAdvisor\Bundle\AdvisorBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware_Components_Snippet_Manager;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionFactoryInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;
use SwagProductAdvisor\Components\Helper\TranslationServiceInterface;
use SwagProductAdvisor\Components\Helper\UrlGeneratorInterface;

class AdvisorService
{
    const POSSIBLE_TEMPLATES = [
        'show_matches',
        'show_matches_and_misses',
        'basic',
        'image',
        'minimal',
    ];

    /**
     * @var QuestionFactoryInterface[]
     */
    private $factories = [];

    /**
     * @var PostHandler
     */
    private $postHandler;

    /**
     * @var ProductContextInterface || null
     */
    private $context = null;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslationServiceInterface
     */
    private $translationService;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param Question\QuestionFactoryInterface[] $factories
     * @param PostHandler                         $postHandler
     * @param MediaServiceInterface               $mediaService
     * @param Connection                          $connection
     * @param UrlGeneratorInterface               $urlGenerator
     * @param TranslationServiceInterface         $translationService
     * @param Shopware_Components_Snippet_Manager $snippetManager
     * @param DependencyProviderInterface         $dependencyProvider
     */
    public function __construct(
        \IteratorAggregate $factories,
        PostHandler $postHandler,
        MediaServiceInterface $mediaService,
        Connection $connection,
        UrlGeneratorInterface $urlGenerator,
        TranslationServiceInterface $translationService,
        Shopware_Components_Snippet_Manager $snippetManager,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->factories = $factories;
        $this->postHandler = $postHandler;
        $this->mediaService = $mediaService;
        $this->connection = $connection;
        $this->urlGenerator = $urlGenerator;
        $this->translationService = $translationService;
        $this->snippetManager = $snippetManager;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * Returns an advisor instance with all necessary information.
     *
     * @param int                     $advisorId
     * @param ProductContextInterface $context
     * @param array                   $answers
     *
     * @throws \Exception
     *
     * @return Advisor
     */
    public function get($advisorId, ProductContextInterface $context, array $answers = [])
    {
        $this->context = $context;
        $advisorData = $this->buildAdvisor($advisorId);

        if (empty($advisorData['id'])) {
            throw new \Exception('No advisor with id ' . $advisorId . ' known');
        }

        if (!$advisorData['active']) {
            throw new \Exception('Advisor with id ' . $advisorId . ' and name \'' . $advisorData['name'] . '\' is inactive');
        }

        $answers = $this->postHandler->handle($answers);
        $questions = $this->buildQuestions($advisorId, $answers);

        $advisor = $this->getAdvisorData(new Advisor($advisorId, $advisorData, $questions));

        return $advisor;
    }

    /**
     * Helper method to fill the current active question in the wizard-mode.
     *
     * @param Advisor $advisor
     * @param int     $questionId
     */
    public function setCurrentQuestion(Advisor $advisor, $questionId)
    {
        $advisor->setCurrentQuestion(
            $advisor->getQuestion(
                $questionId
            )
        );
    }

    /**
     * @param int   $id
     * @param array $postData
     *
     * @return QuestionInterface[]
     */
    private function buildQuestions($id, array $postData)
    {
        $data = $this->getQuestions($id);

        if (!$this->dependencyProvider->getShop()->getDefault()) {
            $data = $this->fillQuestionTranslations($data);
        }

        $questions = [];

        foreach ($data as $row) {
            $factory = $this->getQuestionFactory($row);
            $key = 'q' . $row['id'];
            $post = array_key_exists($key, $postData) ? $postData[$key] : [];
            $questions[] = $factory->factory($row, $this->context, $post);
        }

        return $questions;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return QuestionFactoryInterface
     */
    private function getQuestionFactory(array $data)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($data)) {
                return $factory;
            }
        }

        throw new \Exception(sprintf('Questions of type %s not supported', $data['type']));
    }

    /**
     * Returns the advisor-meta-data.
     *
     * @param int $id
     *
     * @return array $advisor
     */
    private function buildAdvisor($id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $advisor = $queryBuilder->select('advisor.*')
            ->from('s_plugin_product_advisor_advisor', 'advisor')
            ->where('advisor.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetch();

        //Replaces underscores in the key with a camelcase string
        foreach ($advisor as $key => $item) {
            $newKey = $this->underscoreToCamelCase($key);
            if ($newKey !== $key) {
                $advisor[$newKey] = $item;
                unset($advisor[$key]);
            }
        }

        if (!in_array($advisor['listingLayout'], $this::POSSIBLE_TEMPLATES)) {
            $advisor['listingLayout'] = 'show_matches_and_misses';
        }

        if (!$this->dependencyProvider->getShop()->getDefault()) {
            $advisor = $this->fillBasicTranslations($advisor);
        }

        return $this->fillDefaultData($advisor);
    }

    /**
     * Read all questions from the database and convert them into the necessary structure.
     *
     * @param int $id
     *
     * @return array
     */
    private function getQuestions($id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $questions = $queryBuilder
            ->select(
                'questions.*,
                advisor.mode as questionType,
                GROUP_CONCAT(DISTINCT answers.id) as answerIds'
            )
            ->from('s_plugin_product_advisor_advisor', 'advisor')
            ->leftJoin(
                'advisor',
                's_plugin_product_advisor_question',
                'questions',
                'questions.advisor_id = advisor.id'
            )
            ->innerJoin(
                'questions',
                's_plugin_product_advisor_answer',
                'answers',
                'answers.question_id = questions.id'
            )
            ->where('advisor.id = :id')
            ->setParameter('id', $id)
            ->groupBy('questions.id')
            ->orderBy('questions.order', 'ASC')
            ->execute()
            ->fetchAll();

        return array_map(
            function ($item) {
                return $this->handleQuestion($item);
            },
            $questions
        );
    }

    /**
     * Handles all relevant question information
     *
     * @param array $item
     *
     * @return array
     */
    private function handleQuestion(array $item)
    {
        $item['answerIds'] = explode(',', $item['answerIds']);
        $item['answers'] = $answers = $this->getAnswersByIds($item);

        switch ($item['type']) {
            case 'price':
                if (strtolower($item['template']) === 'range_slider') {
                    $item['configuration'] = $this->getPriceConfiguration($answers);
                    break;
                }

                $item['steps'] = $this->getPriceSteps($answers);
                break;
            case 'attribute':
                $item['configuration'] = ['field' => $item['configuration']];
                //Intentionally didn't use the break
                // no break
            default:
                if ($item['questionType'] === Advisor::MODE_WIZARD) {
                    $item['data'] = $this->buildWizardAnswer($answers);
                } else {
                    $item['data'] = $this->buildDefaultAnswer($answers);
                }
                break;
        }

        return $item;
    }

    /**
     * Converts underscore separated string into a camelCase separated string
     *
     * @param string $str
     *
     * @return string
     */
    private function underscoreToCamelCase($str)
    {
        $func = function ($c) {
            return strtoupper($c[1]);
        };

        return preg_replace_callback('/_([a-zA-Z])/', $func, $str);
    }

    /**
     * Build default answer-array e.g. for attribute-filter
     *
     * @param array $answers
     *
     * @return array
     */
    private function buildDefaultAnswer(array $answers)
    {
        $data = [];
        foreach ($answers as $answer) {
            $data[] = [
                'id' => $answer['id'],
                'value' => $answer['value'],
                'key' => $answer['key'],
                'label' => $answer['answer'],
                'css' => $answer['css_class'],
            ];
        }

        return $data;
    }

    /**
     * Helper method to build a wizard answer.
     *
     * @param $answers
     *
     * @return array
     */
    private function buildWizardAnswer($answers)
    {
        $data = [];

        foreach ($answers as $answer) {
            $data[] = [
                'id' => $answer['id'],
                'key' => $answer['key'],
                'value' => $answer['value'],
                'label' => $answer['answer'],
                'css' => $answer['css_class'],
                'mediaId' => $answer['media_id'],
                'rowId' => $answer['row_id'],
                'columnId' => $answer['column_id'],
            ];
        }

        return $data;
    }

    /**
     * Create the price-configuration for the price-slider.
     *
     * @param array $answers
     *
     * @return array
     */
    private function getPriceConfiguration(array $answers)
    {
        $data = [];

        foreach ($answers as $answer) {
            if ($answer['key'] === 'minPrice') {
                $data['min'] = $answer['answer'];
                $data['minCss'] = $answer['css_class'];
            }

            if ($answer['key'] === 'maxPrice') {
                $data['max'] = $answer['answer'];
                $data['maxCss'] = $answer['css_class'];
            }
        }

        return $data;
    }

    /**
     * Helper method to properly set the price-steps
     *
     * @param array $answers
     *
     * @return array
     */
    private function getPriceSteps(array $answers)
    {
        $steps = [];

        $mediaIds = array_column($answers, 'media_id');
        $media = [];
        if ($mediaIds) {
            $media = $this->mediaService->getList($mediaIds, $this->context);
        }

        foreach ($answers as $answer) {
            $stepMedia = null;
            if (array_key_exists($answer['media_id'], $media)) {
                $stepMedia = $media[$answer['media_id']];
            }

            $steps[] = [
                'price' => $answer['value'],
                'guid' => $answer['id'],
                'rowId' => $answer['row_id'],
                'colId' => $answer['column_id'],
                'css' => $answer['css_class'],
                'label' => $answer['answer'],
                'media' => $stepMedia,
            ];
        }

        return $steps;
    }

    /**
     * Helper method to read all answers by the given answer-id's
     *
     * @param array $item
     *
     * @return array
     */
    private function getAnswersByIds(array $item)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select('answers.*')
            ->from('s_plugin_product_advisor_answer', 'answers')
            ->where('answers.id IN ( :answerIds )')
            ->setParameter('answerIds', $item['answerIds'], Connection::PARAM_INT_ARRAY)
            ->orderBy('answers.order')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param Advisor $advisor
     *
     * @return Advisor
     */
    private function getAdvisorData(Advisor $advisor)
    {
        $media = $this->mediaService->get(
            $advisor->getTeaserBannerId(),
            $this->context
        );

        if ($media) {
            $advisor->setTeaser($media);
        }

        if ($advisor->getMode() === Advisor::MODE_WIZARD) {
            $this->handleWizard($advisor);
        }

        return $advisor;
    }

    /**
     * Helper method to handle the wizard-mode.
     * It collects all the data needed and provides the data to the template.
     *
     * @param Advisor $advisor
     */
    private function handleWizard(Advisor $advisor)
    {
        $firstQuestion = reset($advisor->getQuestions());
        $lastQuestion = end($advisor->getQuestions());

        if (!$firstQuestion) {
            return;
        }

        $advisor->setFirstQuestionUrl(
            $this->urlGenerator->generateWizardQuestionsUrl(
                $advisor->getId(),
                $firstQuestion
            )
        );

        if (!$lastQuestion) {
            return;
        }

        $advisor->setLastQuestionUrl(
            $this->urlGenerator->generateWizardQuestionsUrl(
                $advisor->getId(),
                $lastQuestion
            )
        );
    }

    /**
     * Fills the advisor-array with all known translations for the current shop-instance.
     *
     * @param array $advisor
     *
     * @return array
     */
    private function fillBasicTranslations(array $advisor)
    {
        /** @var TranslationServiceInterface $translator */
        $translator = $this->translationService;

        return $translator->translateBasic($advisor);
    }

    /**
     * Fills the questions with the translation-data.
     *
     * @param array $questionData
     *
     * @return array
     */
    private function fillQuestionTranslations(array $questionData)
    {
        /** @var TranslationServiceInterface $translator */
        $translator = $this->translationService;

        return $translator->translateQuestions($questionData);
    }

    /**
     * Helper method to set all default texts to the advisor settings, if not already set in the backend.
     *
     * @param array $advisor
     *
     * @return array
     */
    private function fillDefaultData(array $advisor)
    {
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->snippetManager->getNamespace('frontend/advisor/main');
        if (!$advisor['listingTitleFiltered']) {
            $advisor['listingTitleFiltered'] = $snippetManager->get('ListingTitleFilteredDefault');
        }

        if (!$advisor['remainingPostsTitle']) {
            $advisor['remainingPostsTitle'] = $snippetManager->get('ListingTitleRemainingDefault');
        }

        if (!$advisor['topHitTitle']) {
            $advisor['topHitTitle'] = $snippetManager->get('TopHitTitleDefault');
        }

        if (!$advisor['infoLinkText']) {
            $advisor['infoLinkText'] = $snippetManager->get('InfoLinkTextDefault');
        }

        if (!$advisor['buttonText']) {
            $advisor['buttonText'] = $snippetManager->get('ButtonTextDefault');
        }

        return $advisor;
    }
}
