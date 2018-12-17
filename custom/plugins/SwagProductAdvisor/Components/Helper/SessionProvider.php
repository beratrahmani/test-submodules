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

use Doctrine\DBAL\Query\QueryBuilder;
use Enlight_Components_Session_Namespace as SessionNamespace;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;

class SessionProvider implements SessionProviderInterface
{
    const SESSION_TABLE = 's_plugin_product_advisor_sessions';
    const SESSION_NAME = 'advisor_hash';

    /**
     * @var
     */
    private $isUserLoggedIn;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AnswerValidatorInterface
     */
    private $answerValidator;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * SessionProvider constructor.
     *
     * @param ModelManager                $modelManager
     * @param AnswerValidatorInterface    $answerValidator
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        AnswerValidatorInterface $answerValidator,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->answerValidator = $answerValidator;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash($advisorId)
    {
        $sessionKey = $this::SESSION_NAME . $advisorId;

        if ($this->getUserLoggedIn()) {
            $this->regenerateUserSession($advisorId);
        }

        if (!$this->getSession()->offsetExists($sessionKey)) {
            $this->getSession()->offsetSet($sessionKey, $this->generateHash($advisorId));
        }

        return $this->getSession()->get($sessionKey);
    }

    /**
     * {@inheritdoc}
     */
    public function saveSidebarAnswers($hash, array $answers)
    {
        $this->setAnswerData($hash, $answers);
    }

    /**
     * {@inheritdoc}
     */
    public function saveWizardAnswer($hash, $key, array $answer)
    {
        $this->saveSingleAnswer($hash, $key, $answer);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswersByHash($hash)
    {
        if (empty($hash)) {
            throw new \Exception('No hash is given');
        }

        return $this->readAnswers($hash);
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnHash($hash, $advisorId)
    {
        $sessionKey = $this::SESSION_NAME . $advisorId;
        if (!$this->getSession()->offsetExists($sessionKey)) {
            return false;
        }

        return $hash === $this->getSession()->get($sessionKey);
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateSession($hash, $advisorId)
    {
        $answers = $this->readAnswers($hash);
        $hash = $this->getHash($advisorId);
        $this->setAnswerData($hash, $answers);
    }

    /**
     * {@inheritdoc}
     */
    public function resetSession($hash)
    {
        $this->setAnswerData($hash, []);
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateUserSession($advisorId)
    {
        $queryBuilder = $this->getDbalQueryBuilder();

        $hash = $queryBuilder->select('session.hash')
            ->from($this::SESSION_TABLE, 'session')
            ->where('session.user_id = :userId')
            ->andWhere('session.advisor_id = :advisorId')
            ->setParameter('userId', $this->getSession()->get('sUserId'))
            ->setParameter('advisorId', $advisorId)
            ->execute()
            ->fetchColumn();

        if (!$hash) {
            return;
        }

        $this->getSession()->offsetSet($this::SESSION_NAME . $advisorId, $hash);
    }

    /**
     * @return bool
     */
    private function getUserLoggedIn()
    {
        if ($this->isUserLoggedIn === null) {
            $this->isUserLoggedIn = !empty($this->getSession()->get('sUserId'));
        }

        return $this->isUserLoggedIn;
    }

    /**
     * @return SessionNamespace
     */
    private function getSession()
    {
        return $this->dependencyProvider->getSession();
    }

    /**
     * Returns the DBAL Query Builder
     *
     * @return QueryBuilder
     */
    private function getDbalQueryBuilder()
    {
        return $this->modelManager->getConnection()->createQueryBuilder();
    }

    /**
     * Helper method to generate a hash for the current session.
     *
     * @param int $advisorId
     *
     * @return string
     */
    private function generateHash($advisorId)
    {
        $rndHash = Random::getAlphanumericString(32);

        $this->createDatabaseEntry($rndHash, $advisorId);

        return $rndHash;
    }

    /**
     * Helper method to create a database-entry with the hash and an empty array as data.
     *
     * @param string $hash
     * @param int    $advisorId
     */
    private function createDatabaseEntry($hash, $advisorId)
    {
        $queryBuilder = $this->getDbalQueryBuilder();

        $queryBuilder->insert($this::SESSION_TABLE)
            ->setValue('advisor_id', ':advisorId')
            ->setValue('hash', ':hash')
            ->setValue('data', ':data')
            ->setValue('date', 'NOW()')
            ->setValue('user_id', ':userId')
            ->setParameter('advisorId', $advisorId)
            ->setParameter('hash', $hash)
            ->setParameter('data', json_encode([]))
            ->setParameter('userId', $this->getUserLoggedIn() ? $this->getSession()->get('sUserId') : null)
            ->execute();
    }

    /**
     * Helper method to set the answer-data for a given hash.
     *
     * @param string $hash
     * @param array  $data
     */
    private function setAnswerData($hash, array $data = [])
    {
        $queryBuilder = $this->getDbalQueryBuilder();

        $queryBuilder->update($this::SESSION_TABLE, 'session')
            ->set('data', ':data')
            ->set('date', 'NOW()')
            ->set('user_id', ':userId')
            ->where('session.hash = :hash')
            ->setParameter('data', json_encode($data))
            ->setParameter('hash', $hash)
            ->setParameter('userId', $this->getUserLoggedIn() ? $this->getSession()->get('sUserId') : null)
            ->execute();
    }

    /**
     * Helper method to save a single answer. This is mostly used for the wizard-mode.
     *
     * @param string $hash
     * @param string $questionId
     * @param array  $answer
     */
    private function saveSingleAnswer($hash, $questionId, array $answer)
    {
        $data = $this->readAnswers($hash);

        if (!$answer) {
            $data = $this->removeAnswerByQuestionId($questionId, $data);
        }

        $data = array_merge($data, $answer);

        $this->setAnswerData($hash, $data);
    }

    /**
     * This removes a single answer by the given question-id.
     *
     * @param string $questionId
     * @param array  $answers
     *
     * @return array
     */
    private function removeAnswerByQuestionId($questionId, $answers)
    {
        $needleKey = "q{$questionId}";

        foreach ($answers as $key => $value) {
            if (strpos($key, $needleKey) !== false) {
                unset($answers[$key]);
            }
        }

        return $answers;
    }

    /**
     * Helper method to read all answers by a given hash.
     *
     * @param string $hash
     *
     * @return array
     */
    private function readAnswers($hash)
    {
        $queryBuilder = $this->getDbalQueryBuilder();

        $sessionData = json_decode(
            $queryBuilder->select('session.data')
                ->from('s_plugin_product_advisor_sessions', 'session')
                ->where('session.hash = :hash')
                ->setParameter('hash', $hash)
                ->execute()
                ->fetchColumn(),
            true
        );

        if (!$sessionData) {
            return [];
        }

        return $this->resynchronizeData($sessionData);
    }

    /**
     * Validates if the data is possible due to the current setup and only returns valid data.
     * This does not automatically write the valid data back to the database!
     *
     * @param array $answers
     *
     * @return array
     */
    private function resynchronizeData(array $answers)
    {
        foreach ($answers as $key => $value) {
            if (!$this->answerValidator->validateAnswer($key, $value)) {
                unset($answers[$key]);
            }
        }

        return $answers;
    }
}
