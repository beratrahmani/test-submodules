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

use Enlight_Controller_Front;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;

class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var \Enlight_Controller_Router
     */
    private $router;

    /**
     * @var SessionProviderInterface
     */
    private $sessionProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var RewriteUrlGeneratorInterface
     */
    private $rewriteUrlGenerator;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * UrlGenerator constructor.
     *
     * @param Enlight_Controller_Front     $controllerFront
     * @param SessionProviderInterface     $sessionProvider
     * @param ModelManager                 $modelManager
     * @param RewriteUrlGeneratorInterface $rewriteUrlGenerator
     * @param Shopware_Components_Config   $config
     */
    public function __construct(
        Enlight_Controller_Front $controllerFront,
        SessionProviderInterface $sessionProvider,
        ModelManager $modelManager,
        RewriteUrlGeneratorInterface $rewriteUrlGenerator,
        Shopware_Components_Config $config
    ) {
        $this->router = $controllerFront->Router();
        $this->sessionProvider = $sessionProvider;
        $this->modelManager = $modelManager;
        $this->rewriteUrlGenerator = $rewriteUrlGenerator;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generateWizardQuestionsUrl($advisorId, QuestionInterface $question)
    {
        return $this->router->assemble([
            'controller' => 'advisor',
            'advisorId' => $advisorId,
            'action' => 'question',
            'questionId' => $question === null ?: $question->getId(),
            'hash' => $this->sessionProvider->getHash($advisorId),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePreviousQuestionUrl(Advisor $advisor, $hash)
    {
        $questions = $advisor->getQuestions();
        $questionIndex = $advisor->getCurrentQuestionIndex() - 1;

        if ($questionIndex === 0) {
            return;
        }

        $previousQuestion = null;
        foreach ($questions as $key => $question) {
            if ($key === $questionIndex - 1) {
                $previousQuestion = $question;
                break;
            }
        }

        if (!$previousQuestion) {
            return;
        }

        return $this->router->assemble([
            'controller' => 'advisor',
            'advisorId' => $advisor->getId(),
            'action' => 'question',
            'questionId' => $previousQuestion->getId(),
            'hash' => $hash,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateNextQuestionUrl(Advisor $advisor, $hash)
    {
        $questions = $advisor->getQuestions();
        $questionIndex = $advisor->getCurrentQuestionIndex();

        foreach ($questions as $key => $question) {
            if ($key === $questionIndex) {
                return $this->router->assemble([
                    'controller' => 'advisor',
                    'advisorId' => $advisor->getId(),
                    'action' => 'save',
                    'questionId' => $question->getId(),
                    'hash' => $hash,
                ]);
            } elseif ($key === count($questions) - 1) {
                //We reached the last question
                return $this->router->assemble([
                    'controller' => 'advisor',
                    'advisorId' => $advisor->getId(),
                    'action' => 'save',
                    'hash' => $hash,
                    'isLastQuestion' => true,
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateStartUrl($advisorId, $advisorName)
    {
        /** @var \Shopware\Models\Shop\Shop $mainShop */
        $mainShop = $this->modelManager->getRepository(Shop::class)->getActiveDefault();
        $mainShop->registerResources();

        $mainShop->setBaseUrl($mainShop->getBaseUrl() ?: $mainShop->getBasePath());

        $this->rewriteUrlGenerator->createRewriteUrls($advisorId, $advisorName);

        $context = Context::createFromShop($mainShop, $this->config);

        $context->setGlobalParams([
            'module' => 'frontend',
            'controller' => 'advisor',
            'advisorId' => $advisorId,
        ]);

        $context->setHost($mainShop->getHost());
        $context->setBaseUrl($mainShop->getBaseUrl());

        if (!$mainShop->getSecure()) {
            $context->setSecure($mainShop->getSecure());
        }

        return $this->router->assemble([
            'controller' => 'advisor',
            'advisorId' => $advisorId,
        ], $context);
    }
}
