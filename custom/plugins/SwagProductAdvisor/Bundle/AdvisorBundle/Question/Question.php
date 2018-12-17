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

/**
 * Class Question
 */
class Question implements QuestionInterface, \JsonSerializable
{
    use QuestionTrait;

    /**
     * @var int|null
     */
    protected $numberOfColumns = null;

    /**
     * @var int|null
     */
    protected $numberOfRows = null;

    /**
     * @var int|null
     */
    protected $rowHeight = null;

    /**
     * @var Answer[]
     */
    private $answers = [];

    /**
     * PropertyQuestion constructor.
     *
     * @param int         $id
     * @param string      $question
     * @param string      $template
     * @param string      $type
     * @param bool        $exclude
     * @param bool        $answered
     * @param string|null $infoText
     * @param bool        $required
     * @param bool        $expandQuestion
     * @param float       $boost
     * @param int|null    $numberOfColumns
     * @param int|null    $numberOfRows
     * @param int|null    $rowHeight
     * @param bool        $hideText
     */
    public function __construct(
        $id,
        $question,
        $template,
        $type,
        $exclude,
        $answered = false,
        $infoText = null,
        $required = false,
        $expandQuestion = false,
        $boost = 1.0,
        $numberOfColumns = null,
        $numberOfRows = null,
        $rowHeight = null,
        $hideText = false
    ) {
        $this->id = $id;
        $this->question = $question;
        $this->template = $template;
        $this->type = $type;
        $this->exclude = $exclude;
        $this->answered = $answered;
        $this->infoText = $infoText;
        $this->required = $required;
        $this->expandQuestion = $expandQuestion;
        $this->boost = $boost ?: 1.0;
        $this->numberOfColumns = $numberOfColumns;
        $this->numberOfRows = $numberOfRows;
        $this->rowHeight = $rowHeight;
        $this->hideText = $hideText;
    }

    /**
     * @return Answer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param Answer $answer
     */
    public function addAnswer(Answer $answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * @return Answer[]
     */
    public function getSelectedAnswers()
    {
        return array_filter($this->answers, function (Answer $answer) {
            return $answer->isSelected();
        });
    }

    /**
     * @param string $value
     *
     * @return null|Answer
     */
    public function getAnswerByValue($value)
    {
        foreach ($this->answers as $answer) {
            if ($answer->getValue() == $value) {
                return $answer;
            }
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @return null|Answer
     */
    public function getAnswer($id)
    {
        foreach ($this->answers as $answer) {
            if ($answer->getAnswerId() === $id) {
                return $answer;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getNumberOfColumns()
    {
        return $this->numberOfColumns;
    }

    /**
     * @return int
     */
    public function getNumberOfRows()
    {
        return $this->numberOfRows;
    }

    /**
     * @return int
     */
    public function getRowHeight()
    {
        return $this->rowHeight;
    }

    /**
     * @return int
     */
    public function shouldHideText()
    {
        return $this->hideText;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
