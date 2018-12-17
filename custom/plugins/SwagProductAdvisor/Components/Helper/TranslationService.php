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

namespace SwagProductAdvisor\Components\Helper;

use Doctrine\DBAL\Connection;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Translation as Translator;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;
use SwagProductAdvisor\Models\Advisor;
use SwagProductAdvisor\Models\Question;

class TranslationService implements TranslationServiceInterface
{
    const BASIC_KEY = 'advisorBasic';
    const QUESTION_KEY = 'advisorQuestion';
    const VALUE_KEY = 'advisorValue';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * @param Connection                  $connection
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(Connection $connection, DependencyProviderInterface $dependencyProvider)
    {
        $this->connection = $connection;
        $this->shop = $dependencyProvider->getShop();
        $this->translator = new Translator();
    }

    /**
     * {@inheritdoc}
     */
    public function translateBasic(array $advisor)
    {
        $translation = $this->getTranslation(self::BASIC_KEY, $advisor['id']);

        $advisor = array_merge($advisor, $translation);

        return $advisor;
    }

    /**
     * {@inheritdoc}
     */
    public function translateQuestions(array $questionData)
    {
        foreach ($questionData as &$question) {
            $question = $this->translateQuestion($question);
        }

        return $questionData;
    }

    /**
     * {@inheritdoc}
     */
    public function translateQuestion(array $question)
    {
        $translation = $this->getTranslation(self::QUESTION_KEY, $question['id']);

        if ($translation['question']) {
            $question['question'] = $translation['question'];
        }

        if ($translation['infoText']) {
            $question['info_text'] = $translation['infoText'];
        }

        if ($this->isPriceQuestion($question['type'])) {
            if ($this->isPriceSliderQuestion($question['template'])) {
                $question = $this->translatePriceSliderAnswers($question);

                return $question;
            }

            $question['steps'] = $this->translatePriceSteps($question['steps']);

            return $question;
        }

        $question['data'] = $this->translateDefaultAnswers($question['data']);

        return $question;
    }

    /**
     * {@inheritdoc}
     */
    public function translatePriceSliderAnswers(array $question)
    {
        foreach ($question['answers'] as $answer) {
            $translation = $this->getTranslation(self::VALUE_KEY, $answer['id']);

            if (!$translation || !isset($translation['answer_value'])) {
                continue;
            }

            if ($answer['key'] === 'minPrice') {
                $question['configuration']['min'] = $translation['answer_value'];
            }

            if ($answer['key'] === 'maxPrice') {
                $question['configuration']['max'] = $translation['answer_value'];
            }
        }

        return $question;
    }

    /**
     * {@inheritdoc}
     */
    public function translatePriceSteps(array $steps)
    {
        foreach ($steps as &$priceStep) {
            $translation = $this->getTranslation(self::VALUE_KEY, $priceStep['guid']);

            if (!$translation) {
                continue;
            }

            $priceStep['label'] = $translation['answer_value'];
        }

        return $steps;
    }

    /**
     * {@inheritdoc}
     */
    public function translateDefaultAnswers(array $answers)
    {
        foreach ($answers as &$answer) {
            $translation = $this->getTranslation(self::VALUE_KEY, $answer['id']);

            if (!$translation || !isset($translation['answer_value'])) {
                continue;
            }

            $answer['label'] = $translation['answer_value'];
        }

        return $answers;
    }

    /**
     * {@inheritdoc}
     */
    public function cloneTranslations(Advisor $newAdvisor, Advisor $oldAdvisor)
    {
        $this->cloneAdvisorBasic($newAdvisor->getId(), $oldAdvisor->getId());
        $this->cloneQuestions($newAdvisor->getQuestions(), $oldAdvisor->getQuestions());
    }

    /**
     * {@inheritdoc}
     */
    public function cloneAdvisorBasic($newId, $oldId)
    {
        $translation = $this->getRawTranslations(self::BASIC_KEY, $oldId);

        if (!$translation) {
            return;
        }

        $this->cloneTranslation($newId, $translation);
    }

    /**
     * {@inheritdoc}
     */
    public function cloneQuestions(\Traversable $newQuestions, \Traversable $oldQuestions)
    {
        /** @var Question $oldQuestion */
        foreach ($oldQuestions as $key => $oldQuestion) {
            $translation = $this->getRawTranslations(self::QUESTION_KEY, $oldQuestion->getId());

            /** @var Question $newQuestion */
            $newQuestion = $newQuestions[$key];

            if ($translation) {
                $this->cloneTranslation($newQuestion->getId(), $translation);
            }

            foreach ($oldQuestion->getAnswers() as $answerKey => $oldAnswer) {
                $newAnswer = $newQuestion->getAnswers()[$answerKey];

                $translation = $this->getRawTranslations(self::VALUE_KEY, $oldAnswer->getId());

                if (!$translation) {
                    continue;
                }

                $this->cloneTranslation($newAnswer->getId(), $translation);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cloneQuestionToNewId(array $oldQuestion, array $newQuestion)
    {
        $translation = $this->getRawTranslations(self::QUESTION_KEY, $oldQuestion['id']);

        if ($translation) {
            $this->cloneTranslation($newQuestion['id'], $translation);
        }

        foreach ($oldQuestion['answers'] as $key => $oldAnswer) {
            $newAnswer = $newQuestion['answers'][$key];

            if (!$newAnswer) {
                continue;
            }

            $translation = $this->getRawTranslations(self::VALUE_KEY, $oldAnswer['id']);

            if (!$translation) {
                continue;
            }

            $this->cloneTranslation($newAnswer['id'], $translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkForTranslationClone(array $rawQuestionData, array $parentData)
    {
        foreach ($rawQuestionData as $key => $rawQuestion) {
            if (!isset($rawQuestion['translationCloneId'])) {
                continue;
            }

            $oldQuestion = $this->findQuestionById($rawQuestion['translationCloneId'], $parentData);
            if (!$oldQuestion) {
                continue;
            }

            $this->cloneQuestionToNewId($oldQuestion, $parentData[$key]);
        }
    }

    /**
     * Inserts the translation-array into the database with the new given id.
     *
     * @param string $key
     * @param array  $translation
     */
    private function cloneTranslation($key, $translation)
    {
        if (!$translation) {
            return;
        }

        $builder = $this->connection->createQueryBuilder();

        $builder->insert('s_core_translations')
            ->values([
                'objecttype' => ':objectType',
                'objectdata' => ':objectData',
                'objectkey' => ':objectKey',
                'objectlanguage' => ':objectLanguage',
                'dirty' => ':dirty',
            ])
            ->setParameter('objectType', $translation['objecttype'])
            ->setParameter('objectData', $translation['objectdata'])
            ->setParameter('objectKey', $key)
            ->setParameter('objectLanguage', $translation['objectlanguage'])
            ->setParameter('dirty', $translation['dirty'])
            ->execute();
    }

    /**
     * Reads the translation using the default translation-component.
     *
     * @param string $key
     * @param string $identifier
     *
     * @return array
     */
    private function getTranslation($key, $identifier)
    {
        return $this->translator->read(
            $this->shop->getId(),
            $key,
            $identifier
        );
    }

    /**
     * Reads the raw translation-data by a given key and identifier.
     * This will also return the object-language and the object-type.
     *
     * @param string $key
     * @param string $identifier
     *
     * @return array
     */
    private function getRawTranslations($key, $identifier)
    {
        $builder = $this->connection->createQueryBuilder();

        return $builder->select([
            'objecttype',
            'objectdata',
            'objectlanguage',
            'dirty',
        ])
            ->from('s_core_translations', 'translation')
            ->where('objectkey = :objectKey')
            ->andWhere('objecttype = :objectType')
            ->setParameter(':objectKey', $identifier)
            ->setParameter(':objectType', $key)
            ->execute()
            ->fetch();
    }

    /**
     * @param string $questionType
     *
     * @return bool
     */
    private function isPriceQuestion($questionType)
    {
        return $questionType === 'price';
    }

    /**
     * @param string $questionTemplate
     *
     * @return bool
     */
    private function isPriceSliderQuestion($questionTemplate)
    {
        return $questionTemplate === 'range_slider';
    }

    /**
     * Finds a question by the id in a given array of questions.
     *
     * @param string $id
     * @param array  $questionData
     *
     * @return array
     */
    private function findQuestionById($id, array $questionData)
    {
        foreach ($questionData as $question) {
            if ($question['id'] === $id) {
                return $question;
            }
        }

        return [];
    }
}
