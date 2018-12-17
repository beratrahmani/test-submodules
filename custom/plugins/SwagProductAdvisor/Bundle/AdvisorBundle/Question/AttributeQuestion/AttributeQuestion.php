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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\AttributeQuestion;

use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Question;

/**
 * Class AttributeQuestion
 */
class AttributeQuestion extends Question
{
    /**
     * @var string
     */
    private $field;

    /**
     * @param Question $question
     *
     * @return AttributeQuestion
     */
    public static function createFromQuestion(Question $question)
    {
        $self = new self(
            $question->getId(),
            $question->getQuestion(),
            $question->getTemplate(),
            $question->getType(),
            $question->isExclude(),
            $question->isAnswered(),
            $question->getInfoText(),
            $question->isRequired(),
            $question->shouldExpandQuestion(),
            $question->getBoost(),
            $question->getNumberOfColumns(),
            $question->getNumberOfRows(),
            $question->getRowHeight(),
            $question->shouldHideText()
        );
        foreach ($question->getAnswers() as $answer) {
            $self->addAnswer($answer);
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
}
