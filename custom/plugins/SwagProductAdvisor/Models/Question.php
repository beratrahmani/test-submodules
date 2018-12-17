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

namespace SwagProductAdvisor\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_plugin_product_advisor_question")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Question extends ModelEntity
{
    /**
     * @var Advisor
     * @ORM\ManyToOne(targetEntity="SwagProductAdvisor\Models\Advisor", inversedBy="questions")
     * @ORM\JoinColumn(name="advisor_id", referencedColumnName="id")
     */
    protected $advisor;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="SwagProductAdvisor\Models\Answer",
     *     mappedBy="question",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $answers;
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="`order`", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var bool
     * @ORM\Column(name="exclude", type="boolean", nullable=false)
     */
    private $exclude = false;

    /**
     * @var string
     * @ORM\Column(name="question", type="string", nullable=false)
     */
    private $question;

    /**
     * @var string
     * @ORM\Column(name="template", type="string", nullable=true)
     */
    private $template;

    /**
     * @var string
     * @ORM\Column(name="info_text", type="text", nullable=true)
     */
    private $infoText;

    /**
     * @var string
     * @ORM\Column(name="configuration", type="text", nullable=true)
     */
    private $configuration;

    /**
     * @var int
     * @ORM\Column(name="number_of_rows", type="integer", nullable=true)
     */
    private $numberOfRows;

    /**
     * @var int
     * @ORM\Column(name="number_of_columns", type="integer", nullable=true)
     */
    private $numberOfColumns;

    /**
     * @var bool
     * @ORM\Column(name="needs_to_be_answered", type="boolean")
     */
    private $needsToBeAnswered;

    /**
     * @var bool
     * @ORM\Column(name="expand_question", type="boolean")
     */
    private $expandQuestion;

    /**
     * @var int
     * @ORM\Column(name="column_height", type="integer", nullable=true)
     */
    private $columnHeight;

    /**
     * @var int
     * @ORM\Column(name="boost", type="integer")
     */
    private $boost;

    /**
     * @var bool
     * @ORM\Column(name="multiple_answers", type="boolean")
     */
    private $multipleAnswers;

    /**
     * @var bool
     * @ORM\Column(name="hide_text", type="boolean")
     */
    private $hideText;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    /**
     * Clone Question
     */
    public function __clone()
    {
        $this->id = null;

        $answers = [];
        foreach ($this->getAnswers() as $value) {
            /** @var Answer $answer */
            $answer = clone $value;
            $answer->setQuestion($this);
            $answers[] = $answer;
        }

        $this->answers = $answers;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param string $question
     *
     * @return $this
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getInfoText()
    {
        return $this->infoText;
    }

    /**
     * @param string $infoText
     *
     * @return $this
     */
    public function setInfoText($infoText)
    {
        $this->infoText = $infoText;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     *
     * @return $this
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfColumns()
    {
        return $this->numberOfColumns;
    }

    /**
     * @param int $numberOfColumns
     *
     * @return $this
     */
    public function setNumberOfColumns($numberOfColumns)
    {
        $this->numberOfColumns = $numberOfColumns;

        return $this;
    }

    /**
     * @return int
     */
    public function getColumnHeight()
    {
        return $this->columnHeight;
    }

    /**
     * @param int $columnHeight
     *
     * @return $this
     */
    public function setColumnHeight($columnHeight)
    {
        $this->columnHeight = $columnHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfRows()
    {
        return $this->numberOfRows;
    }

    /**
     * @param int $numberOfRows
     *
     * @return $this
     */
    public function setNumberOfRows($numberOfRows)
    {
        $this->numberOfRows = $numberOfRows;

        return $this;
    }

    /**
     * @return Advisor
     */
    public function getAdvisor()
    {
        return $this->advisor;
    }

    /**
     * @param Advisor $advisor
     *
     * @return $this
     */
    public function setAdvisor($advisor)
    {
        return $this->setManyToOne($advisor, Advisor::class, 'advisor');
    }

    /**
     * @return ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ArrayCollection $answers
     *
     * @return $this
     */
    public function setAnswers($answers)
    {
        return $this->setOneToMany($answers, Answer::class, 'answers', 'question');
    }

    /**
     * @param Answer $answer
     *
     * @return $this
     */
    public function addAnswer($answer)
    {
        $this->answers->add($answer);

        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedsToBeAnswered()
    {
        return $this->needsToBeAnswered;
    }

    /**
     * @param bool $needsToBeAnswered
     *
     * @return $this
     */
    public function setNeedsToBeAnswered($needsToBeAnswered)
    {
        $this->needsToBeAnswered = $needsToBeAnswered;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpandQuestion()
    {
        return $this->expandQuestion;
    }

    /**
     * @param bool $expandQuestion
     *
     * @return $this
     */
    public function setExpandQuestion($expandQuestion)
    {
        $this->expandQuestion = $expandQuestion;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipleAnswers()
    {
        return $this->multipleAnswers;
    }

    /**
     * @param bool $multipleAnswers
     *
     * @return $this
     */
    public function setMultipleAnswers($multipleAnswers)
    {
        $this->multipleAnswers = $multipleAnswers;

        return $this;
    }

    /**
     * @return int
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @param int $boost
     *
     * @return $this
     */
    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    /**
     * @return bool
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param bool $exclude
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
    }

    /**
     * @return bool
     */
    public function isHideText()
    {
        return $this->hideText;
    }

    /**
     * @param bool $hideText
     *
     * @return $this
     */
    public function setHideText($hideText)
    {
        $this->hideText = $hideText;

        return $this;
    }
}
