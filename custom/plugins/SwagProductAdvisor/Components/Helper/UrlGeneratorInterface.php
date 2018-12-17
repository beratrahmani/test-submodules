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

/**
 * Class UrlGenerator
 */
interface UrlGeneratorInterface
{
    /**
     * @param $advisorId
     * @param QuestionInterface $question
     *
     * @return string
     */
    public function generateWizardQuestionsUrl($advisorId, QuestionInterface $question);

    /**
     * Generate previous question-url.
     *
     * @param Advisor $advisor
     * @param string  $hash
     *
     * @return string|void
     */
    public function generatePreviousQuestionUrl(Advisor $advisor, $hash);

    /**
     * Generate next question-url, if there is a following question.
     * Otherwise a link with the parameter "isLastQuestion" is generated.
     *
     * @param Advisor $advisor
     * @param string  $hash
     *
     * @return string|void
     */
    public function generateNextQuestionUrl(Advisor $advisor, $hash);

    /**
     * Generates the url to the start-/index-page of the given advisor.
     * Since it's called in the backend, simulating a shop is necessary.
     *
     * @param int    $advisorId
     * @param string $advisorName
     *
     * @return string
     */
    public function generateStartUrl($advisorId, $advisorName);
}
