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

/**
 * Class PriceRangeQuestion
 */
class PriceRangeQuestion implements QuestionInterface, \JsonSerializable, PriceQuestionInterface
{
    use QuestionTrait;

    /**
     * @var null|float
     */
    private $selectedMin;

    /**
     * @var null|float
     */
    private $selectedMax;

    /**
     * @var null|float
     */
    private $min;

    /**
     * @var null|float
     */
    private $max;

    /**
     * @var string
     */
    private $minCss;

    /**
     * @var string
     */
    private $maxCss;

    /**
     * PropertyQuestion constructor.
     *
     * @param int    $id
     * @param string $question
     * @param string $template
     * @param string $type
     * @param $exclude
     * @param string      $minCss
     * @param string      $maxCss
     * @param null|float  $min
     * @param null|float  $max
     * @param null        $selectedMin
     * @param null        $selectedMax
     * @param bool        $answered
     * @param null|string $infoText
     * @param bool        $required
     * @param bool        $expandQuestion
     * @param float       $boost
     */
    public function __construct(
        $id,
        $question,
        $template,
        $type,
        $exclude,
        $minCss,
        $maxCss,
        $min = null,
        $max = null,
        $selectedMin = null,
        $selectedMax = null,
        $answered = false,
        $infoText = null,
        $required = false,
        $expandQuestion = false,
        $boost = 1.0
    ) {
        $this->id = $id;
        $this->question = $question;
        $this->template = $template;
        $this->type = $type;
        $this->minCss = $minCss;
        $this->maxCss = $maxCss;
        $this->min = (int) $min;
        $this->max = (int) $max;
        $this->selectedMin = $selectedMin;
        $this->selectedMax = $selectedMax;
        $this->answered = $answered;
        $this->infoText = $infoText;
        $this->required = $required;
        $this->expandQuestion = $expandQuestion;
        $this->boost = $boost;
        $this->exclude = $exclude;
    }

    /**
     * @return string
     */
    public function getMinCss()
    {
        return $this->minCss;
    }

    /**
     * @return string
     */
    public function getMaxCss()
    {
        return $this->maxCss;
    }

    /**
     * @return null|float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return null|float
     */
    public function getMax()
    {
        return $this->max;
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
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
