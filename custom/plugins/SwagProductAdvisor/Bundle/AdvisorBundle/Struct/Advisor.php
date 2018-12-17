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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\Struct;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\Question;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;

/**
 * Class Advisor
 */
class Advisor extends Struct implements \JsonSerializable
{
    const MODE_WIZARD = 'wizard_mode';
    const MODE_SIDEBAR = 'sidebar_mode';

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $active;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $infoLinkText;

    /**
     * @var string
     */
    private $buttonText;

    /**
     * @var string
     */
    private $listingTitleFiltered;

    /**
     * @var string
     */
    private $remainingPostsTitle;

    /**
     * @var bool
     */
    private $highlightTopHit;

    /**
     * @var string
     */
    private $topHitTitle;

    /**
     * @var int
     */
    private $minMatchingAttributes;

    /**
     * @var string
     */
    private $listingLayout;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $lastListingSort;

    /**
     * Contains a media-id.
     *
     * @var int
     */
    private $teaserBannerId;

    /**
     * Contains the stream-id.
     *
     * @var int
     */
    private $stream;

    /**
     * @var QuestionInterface[]
     */
    private $questions;

    /**
     * Contains whether or not the advisor has been started already (Wizard only)
     *
     * @var bool
     */
    private $started = false;

    /**
     * Contains the result-array if any result is given yet
     *
     * @var array || null
     */
    private $result = null;

    /**
     * @var Media || null
     */
    private $teaser = null;

    /**
     * Contains the currently active question (Wizard only)
     *
     * @var QuestionInterface || null
     */
    private $currentQuestion = null;

    /**
     * Contains the index of the currently active question
     *
     * @var int || null
     */
    private $currentQuestionIndex = null;

    /**
     * Contains the total count of questions
     *
     * @var int || null
     */
    private $questionCount = null;

    /**
     * Contains the url to the first question of a wizard-advisor
     *
     * @var string
     */
    private $firstQuestionUrl = null;

    /**
     * Contains the url to the last question of a wizard-advisor
     *
     * @var string
     */
    private $lastQuestionUrl = null;

    /**
     * @var array
     */
    private $topHit = null;

    /**
     * @var int
     */
    private $totalCount = null;

    /**
     * @var array
     */
    private $othersTitle = [];

    /**
     * Advisor constructor.
     *
     * @param int                 $id
     * @param array               $data
     * @param QuestionInterface[] $questions
     */
    public function __construct($id, array $data, array $questions)
    {
        $this->id = $id;
        $this->questions = $questions;
        $this->stream = $data['streamId'];

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getInfoLinkText()
    {
        return $this->infoLinkText;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @return string
     */
    public function getListingTitleFiltered()
    {
        return $this->listingTitleFiltered;
    }

    /**
     * @return string
     */
    public function getRemainingPostsTitle()
    {
        return $this->remainingPostsTitle;
    }

    /**
     * @return bool
     */
    public function isHighlightTopHit()
    {
        return $this->highlightTopHit;
    }

    /**
     * @return string
     */
    public function getTopHitTitle()
    {
        return $this->topHitTitle;
    }

    /**
     * @return int
     */
    public function getMinMatchingAttributes()
    {
        return $this->minMatchingAttributes;
    }

    /**
     * @return string
     */
    public function getListingLayout()
    {
        return $this->listingLayout;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getLastListingSort()
    {
        return $this->lastListingSort;
    }

    /**
     * @return int
     */
    public function getTeaserBannerId()
    {
        return $this->teaserBannerId;
    }

    /**
     * @return int
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return QuestionInterface[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param array $result
     *
     * @return $this
     */
    public function setResult(array $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return array || null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Media $teaser
     *
     * @return $this
     */
    public function setTeaser(Media $teaser)
    {
        $this->teaser = $teaser;

        return $this;
    }

    /**
     * @return Media || null
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * @param bool $started
     *
     * @return Advisor $this
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * @param QuestionInterface $currentQuestion
     *
     * @return Advisor $this;
     */
    public function setCurrentQuestion(QuestionInterface $currentQuestion)
    {
        $this->currentQuestion = $currentQuestion;

        return $this;
    }

    /**
     * @return QuestionInterface;
     */
    public function getCurrentQuestion()
    {
        return $this->currentQuestion;
    }

    /**
     * @param string $firstQuestionUrl
     *
     * @return $this
     */
    public function setFirstQuestionUrl($firstQuestionUrl)
    {
        $this->firstQuestionUrl = $firstQuestionUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstQuestionUrl()
    {
        return $this->firstQuestionUrl;
    }

    /**
     * @param string $lastQuestionUrl
     *
     * @return $this
     */
    public function setLastQuestionUrl($lastQuestionUrl)
    {
        $this->lastQuestionUrl = $lastQuestionUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastQuestionUrl()
    {
        return $this->lastQuestionUrl;
    }

    /**
     * @param array $topHit
     *
     * @return Advisor
     */
    public function setTopHit(array $topHit)
    {
        $this->topHit = $topHit;

        return $this;
    }

    /**
     * @return array
     */
    public function getTopHit()
    {
        return $this->topHit;
    }

    /**
     * @param int $totalCount
     *
     * @return Advisor
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param array $othersTitle
     */
    public function setOthersTitle(array $othersTitle)
    {
        $this->othersTitle = $othersTitle;
    }

    /**
     * @return array $othersTitle
     */
    public function getOthersTitle()
    {
        return $this->othersTitle;
    }

    /**
     * @param int $id
     *
     * @return null|QuestionInterface
     */
    public function getQuestion($id)
    {
        foreach ($this->questions as $question) {
            if ($question->getId() === $id) {
                return $question;
            }
        }

        return null;
    }

    /**
     * @return QuestionInterface[]
     */
    public function getAnsweredQuestions()
    {
        return array_filter($this->questions, function (QuestionInterface $question) {
            return $question->isAnswered();
        });
    }

    /**
     * Returns the count of total questions
     *
     * @return int
     */
    public function getQuestionCount()
    {
        if ($this->questionCount === null) {
            $this->questionCount = count($this->questions);
        }

        return $this->questionCount;
    }

    /**
     * Returns the index of the currently active question.
     * It starts at 1 for the first question, not at 0.
     *
     * @return int
     */
    public function getCurrentQuestionIndex()
    {
        if (!$this->currentQuestion) {
            return false;
        }

        if ($this->currentQuestionIndex === null) {
            foreach ($this->questions as $key => $item) {
                if ($item->getId() === $this->currentQuestion->getId()) {
                    $this->currentQuestionIndex = $key + 1;
                    break;
                }
            }
        }

        return $this->currentQuestionIndex;
    }

    /**
     * Returns whether or not the advisor has a required question.
     *
     * @return bool
     */
    public function hasRequired()
    {
        foreach ($this->getQuestions() as $question) {
            if ($question->isRequired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all required questions.
     *
     * @return array
     */
    public function getRequiredQuestions()
    {
        if (!$this->hasRequired()) {
            return [];
        }

        $requiredQuestions = [];

        /** @var Question $question */
        foreach ($this->questions as $question) {
            if (!$question->isRequired()) {
                continue;
            }
            $requiredQuestions[] = $question;
        }

        return $requiredQuestions;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $array = get_object_vars($this);

        $array['hasRequired'] = $this->hasRequired();
        $array['questionCount'] = $this->getQuestionCount();
        $array['answeredQuestions'] = $this->getAnsweredQuestions();
        $array['currentQuestionIndex'] = $this->getCurrentQuestionIndex();
        $array['currentQuestion'] = $this->getCurrentQuestion();

        return $array;
    }
}
