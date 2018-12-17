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

use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;

class AdvisorValidator implements AdvisorValidatorInterface
{
    private $validators = [
        'minimumAnswers',
        'requiredFields',
    ];

    /**
     * @var SessionProviderInterface
     */
    private $sessionProvider;

    /**
     * @var AnswerBuilderInterface
     */
    private $answerBuilder;

    /**
     * @param SessionProviderInterface $sessionProvider
     * @param AnswerBuilderInterface   $answerBuilder
     */
    public function __construct(
        SessionProviderInterface $sessionProvider,
        AnswerBuilderInterface $answerBuilder
    ) {
        $this->sessionProvider = $sessionProvider;
        $this->answerBuilder = $answerBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAdvisor(Advisor $advisor, $validation = null)
    {
        $errors = [];

        if ($validation) {
            $method = 'validate' . ucfirst($validation);
            if (!method_exists($this, $method)) {
                throw new \Exception("Method with name {$method} not found.");
            }

            return $this->$method($advisor);
        }

        foreach ($this->validators as $validator) {
            $method = 'validate' . ucfirst($validator);
            if (method_exists($this, $method)) {
                $validationReturn = $this->$method($advisor);
                if (!$validationReturn) {
                    continue;
                }
                $errors[$validator] = $validationReturn;
            }
        }

        if (!$errors) {
            return [];
        }

        return ['advisorErrors' => $errors];
    }

    /**
     * Validates if the minimum amount of answers is given.
     *
     * @param Advisor $advisor
     *
     * @return array
     */
    private function validateMinimumAnswers(Advisor $advisor)
    {
        $answers = $this->answerBuilder->getUniqueAnswers($this->getAnswers($advisor->getId()));
        $minMatchingAttributes = $advisor->getMinMatchingAttributes();
        $answerCount = count($answers);

        if ($answerCount < $minMatchingAttributes) {
            return [
                'difference' => $minMatchingAttributes - $answerCount,
            ];
        }

        return [];
    }

    /**
     * Validates if every required-field is answered.
     * In the sidebar mode it will validate if the answer is given at all.
     * For the wizard-mode, we also need to check if a previous non-answered question was a required-question.
     *
     * @param Advisor $advisor
     *
     * @return array
     */
    private function validateRequiredFields(Advisor $advisor)
    {
        if (!$advisor->hasRequired()) {
            return [];
        }

        $answeredQuestionIds = $this->getAnsweredQuestionIds($advisor->getAnsweredQuestions());
        $requiredQuestions = $advisor->getRequiredQuestions();

        $missingQuestions = [];

        /** @var QuestionInterface $question */
        foreach ($requiredQuestions as $question) {
            if (in_array($question->getId(), $answeredQuestionIds, true)) {
                continue;
            }

            $missingQuestions[] = $question;
        }

        if ($missingQuestions) {
            return [
                'missingQuestions' => json_decode(json_encode($missingQuestions), true),
                'missingQuestionCount' => count($missingQuestions),
            ];
        }

        return [];
    }

    /**
     * Returns the ids of all answered questions.
     *
     * @param array $questions
     *
     * @return array
     */
    private function getAnsweredQuestionIds(array $questions)
    {
        return array_map(function ($item) {
            /* @var QuestionInterface $item */
            return $item->getId();
        }, $questions);
    }

    /**
     * Helper method to read out all answers given for the current advisor.
     *
     * @param int $advisorId
     *
     * @return array
     */
    private function getAnswers($advisorId)
    {
        $hash = $this->sessionProvider->getHash($advisorId);

        return $this->sessionProvider->getAnswersByHash($hash);
    }
}
