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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Question\PriceQuestion;

use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionTrait;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Step;

/**
 * Class PriceDefaultQuestion
 */
class PriceDefaultQuestion implements QuestionInterface, \JsonSerializable, PriceQuestionInterface
{
    use QuestionTrait;

    /**
     * @var array
     */
    private $steps = [];

    /**
     * @var null|float
     */
    private $selectedMin;

    /**
     * @var null|float
     */
    private $selectedMax;

    /**
     * @var int|null
     */
    private $numberOfColumns;

    /**
     * @var int|null
     */
    private $numberOfRows;

    /**
     * @var int|null
     */
    private $rowHeight;

    /**
     * PriceDefaultQuestion constructor.
     *
     * @param int         $id
     * @param string      $question
     * @param string      $template
     * @param string      $type
     * @param bool        $exclude
     * @param Step[]      $steps
     * @param null        $selectedMin
     * @param null        $selectedMax
     * @param bool        $answered
     * @param null|string $infoText
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
        array $steps,
        $selectedMin = null,
        $selectedMax = null,
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
        $this->infoText = $infoText;
        $this->required = $required;
        $this->expandQuestion = $expandQuestion;
        $this->boost = $boost;
        $this->type = $type;
        $this->selectedMin = $selectedMin;
        $this->selectedMax = $selectedMax;
        $this->answered = $answered;
        $this->template = $template;
        $this->steps = $steps;
        $this->numberOfColumns = $numberOfColumns;
        $this->numberOfRows = $numberOfRows;
        $this->rowHeight = $rowHeight;
        $this->exclude = $exclude;
        $this->hideText = $hideText;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param Step $step
     *
     * @return $this
     */
    public function addStep(Step $step)
    {
        $this->steps[] = $step;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectedMax()
    {
        return $this->selectedMax;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectedMin()
    {
        return $this->selectedMin;
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
     * @param int $colIndex
     * @param int $rowIndex
     *
     * @return null|Step
     */
    public function getStepOfCell($colIndex, $rowIndex)
    {
        foreach ($this->steps as $step) {
            if ($step->getColId() == $colIndex && $step->getRowId() == $rowIndex && !empty($step->getValue())) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @return Step
     */
    public function getSelectedStep()
    {
        foreach ($this->steps as $step) {
            if ($step->isSelected()) {
                return $step;
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $array = get_object_vars($this);
        $array['selectedStep'] = $this->getSelectedStep();

        return $array;
    }
}
