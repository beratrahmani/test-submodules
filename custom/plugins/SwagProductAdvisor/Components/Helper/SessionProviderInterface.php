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

/**
 * Class SessionProvider
 */
interface SessionProviderInterface
{
    /**
     * Method to return the current hash for the current user.
     * If there is no hash generated yet, it will create one.
     *
     * @param int $advisorId
     *
     * @return string
     */
    public function getHash($advisorId);

    /**
     * Method to save the given answers to the database using the hash.
     * This is only used for the sidebar-mode.
     *
     * @param string $hash
     * @param array  $answers
     */
    public function saveSidebarAnswers($hash, array $answers);

    /**
     * Method to save a given wizard-answer to the database using the hash.
     * This is only used for the wizard-mode.
     *
     * @param string $hash
     * @param string $key
     * @param array  $answer
     */
    public function saveWizardAnswer($hash, $key, array $answer);

    /**
     * Method to read the answers by a given hash.
     *
     * @param string $hash
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getAnswersByHash($hash);

    /**
     * Method to validate if a hash is the own hash
     *
     * @param string $hash
     * @param int    $advisorId
     *
     * @return bool
     */
    public function isOwnHash($hash, $advisorId);

    /**
     * Method to regenerate the session by a given hash.
     *
     * @param string $hash
     * @param int    $advisorId
     */
    public function regenerateSession($hash, $advisorId);

    /**
     * Resets the session-data to an empty array.
     *
     * @param string $hash
     */
    public function resetSession($hash);

    /**
     * Method to regenerate the user-session, if any is given.
     *
     * @param int $advisorId
     */
    public function regenerateUserSession($advisorId);
}
