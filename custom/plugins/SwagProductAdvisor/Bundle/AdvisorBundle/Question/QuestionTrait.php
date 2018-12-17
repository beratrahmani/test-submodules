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
 * Trait QuestionTrait
 */
trait QuestionTrait
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var bool
     */
    protected $answered;

    /**
     * @var string
     */
    protected $infoText;

    /**
     * @var string
     */
    protected $question;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $exclude;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var bool
     */
    protected $expandQuestion;

    /**
     * @var float
     */
    protected $boost = 1.0;

    /**
     * @var bool
     */
    protected $hideText = false;

    /**
     * @var string
     */
    protected $questionUrl;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return bool
     */
    public function isAnswered()
    {
        return $this->answered;
    }

    /**
     * @return string
     */
    public function getInfoText()
    {
        return $this->infoText;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return float
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @return bool
     */
    public function isExclude()
    {
        return $this->exclude;
    }

    /**
     * @return bool
     */
    public function shouldExpandQuestion()
    {
        return $this->expandQuestion;
    }

    /**
     * @return bool
     */
    public function shouldHideText()
    {
        return $this->hideText;
    }

    /**
     * @return string
     */
    public function getQuestionUrl()
    {
        return $this->questionUrl;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setQuestionUrl($url)
    {
        $this->questionUrl = $url;

        return $this;
    }
}
