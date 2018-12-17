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
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Routing\RouterInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\AdvisorService;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionTrait;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Components\Helper\AdvisorValidatorInterface;
use SwagProductAdvisor\Components\Helper\AnswerBuilderInterface;
use SwagProductAdvisor\Components\Helper\ResultHelperInterface;
use SwagProductAdvisor\Components\Helper\SessionProviderInterface;
use SwagProductAdvisor\Components\Helper\UrlGeneratorInterface;

/**
 * Class Shopware_Controllers_Frontend_Advisor
 */
class Shopware_Controllers_Frontend_Advisor extends Enlight_Controller_Action
{
    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'save',
        ];
    }

    /**
     * The main action to load the advisor and to fill the view with all necessary data.
     */
    public function indexAction()
    {
        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');

        $advisor = $this->getAdvisor();
        $hash = $sessionProvider->getHash($advisor->getId());
        $this->View()->assign(
            $this->getViewConfig($advisor, $hash)
        );
    }

    /**
     * This action is called to save the current answer-data.
     * Sidebar-Mode:
     * This action gets called right before showing the result.
     * Wizard-Mode:
     * This action gets called with each step/answer.
     */
    public function saveAction()
    {
        $isWizard = $this->getAdvisor()->getMode() === Advisor::MODE_WIZARD;
        $this->saveAnswer($isWizard);
        if ($isWizard && !$this->Request()->getParam('isLastQuestion')) {
            $this->redirectToNextQuestion();

            return;
        }

        /** @var AdvisorValidatorInterface $advisorValidator */
        $advisorValidator = $this->container->get('swag_product_advisor.advisor_validator');

        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');

        $answers = $sessionProvider->getAnswersByHash($this->Request()->getParam('hash'));
        $advisor = $this->getAdvisor($answers);

        $requiredFields = $advisorValidator->validateAdvisor($advisor, 'requiredFields');

        if (!empty($requiredFields)) {
            $this->redirectToLastQuestion($advisor, true);

            return;
        }

        $this->redirectToResult();
    }

    /**
     * This action is called to only save a value for a question without redirecting or doing any further stuff.
     * It is used for the "skip to question"-dropdown in the wizard-mode to save a value before directing to the desired
     * question.
     */
    public function quickSaveAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        try {
            $this->saveAnswer(true);
            $this->View()->assign('success', true);
        } catch (Exception $e) {
            $this->View()->assign('success', false);
        }
    }

    /**
     * This is a single question-page in the wizard-mode.
     */
    public function questionAction()
    {
        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');

        /** @var AdvisorService $advisorService */
        $advisorService = $this->get('swag_product_advisor.advisor_service');

        $hash = $this->Request()->getParam('hash');
        $advisorId = $this->Request()->getParam('advisorId');

        if (!$sessionProvider->isOwnHash($hash, $advisorId)) {
            $sessionProvider->regenerateSession($hash, $advisorId);
        }

        $answers = $sessionProvider->getAnswersByHash($hash);
        $advisor = $this->getAdvisor($answers);

        $advisor->setStarted(true);

        $advisorService->setCurrentQuestion($advisor, $this->Request()->getParam('questionId'));

        $hash = $sessionProvider->getHash($advisorId);

        $this->View()->assign(
            $this->getQuestionViewConfig($advisor, $hash)
        );
    }

    /**
     * The action which is called to show the result.
     * The answers are loaded using the hash given.
     */
    public function resultAction()
    {
        $this->View()->assign($this->getResult());
    }

    /**
     * The action to reset an advisor, which is called when clicking the "reset advisor"-button.
     * It will redirect to the index-action afterwards.
     */
    public function resetAction()
    {
        $advisorId = $this->Request()->getParam('advisorId');
        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');
        $hash = $sessionProvider->getHash($advisorId);

        $sessionProvider->resetSession($hash);

        $this->redirect(
            [
                'action' => 'index',
                'advisorId' => $this->Request()->getParam('advisorId'),
            ]
        );
    }

    /**
     * This is called from the infinite-scrolling implementation for the result-listing.
     * Will return the necessary products in a new template.
     */
    public function ajaxResultAction()
    {
        $this->Request()->setQuery('sCategory', null);
        $advisorParams = json_decode($this->Request()->getParam('advisorParams'), true);
        foreach ($advisorParams as $key => $value) {
            $this->Request()->setQuery($key, $value);
        }

        $result = $this->getResult();
        $result['advisor']['result'] = $this->rewriteProductUrls($result['advisor']['result']);
        $result['theme'] = $this->loadThemeConfig();
        $result['advisorAjaxResult'] = true;

        $this->View()->assign($result);

        $listing = $this->View()->fetch('frontend/advisor/ajax_result.tpl');

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode(['listing' => $listing]));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

    /**
     * Helper method to get the advisor-data and return it to the index-action, prepared for the view.
     *
     * @param array $answers
     *
     * @return Advisor
     */
    public function getAdvisor(array $answers = [])
    {
        /** @var AdvisorService $advisorService */
        $advisorService = $this->get('swag_product_advisor.advisor_service');

        /** @var ContextServiceInterface $contextService */
        $contextService = $this->container->get('shopware_storefront.context_service');
        $productContext = $contextService->getProductContext();

        /** @var Advisor $advisor */
        $advisor = $advisorService->get(
            $this->Request()->getParam('advisorId'),
            $productContext,
            $answers
        );

        return $advisor;
    }

    /**
     * Helper method to receive the actual result for the result-page of the advisor.
     * It will use the request-params to get all information needed.
     *
     * @return array
     */
    public function getResult()
    {
        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');

        /** @var AdvisorValidatorInterface $advisorValidator */
        $advisorValidator = $this->container->get('swag_product_advisor.advisor_validator');

        /** @var ResultHelperInterface $resultHelper */
        $resultHelper = $this->container->get('swag_product_advisor.result_helper');

        /** @var ContextServiceInterface $contextService */
        $contextService = $this->container->get('shopware_storefront.context_service');
        $productContext = $contextService->getProductContext();

        $hash = $this->Request()->getParam('hash');
        $advisorId = $this->Request()->getParam('advisorId');

        if (!$sessionProvider->isOwnHash($hash, $advisorId)) {
            $sessionProvider->regenerateSession($hash, $advisorId);
        }
        $answers = $sessionProvider->getAnswersByHash($hash);

        $advisor = $this->getAdvisor($answers);
        $errors = $advisorValidator->validateAdvisor($advisor);

        if ($errors) {
            return array_merge($errors, $this->getViewConfig($advisor, $sessionProvider->getHash($advisorId)));
        }

        $resultHelper->getAdvisorResult($advisor, $this->Request(), $answers);

        return $this->getListingConfig(
            $advisor,
            $resultHelper->getCriteria(
                $advisor,
                $this->Request(),
                $productContext
            ),
            $sessionProvider->getHash($advisorId)
        );
    }

    /**
     * Helper method to save an answer to the database.
     *
     * @param bool $isWizard
     */
    public function saveAnswer($isWizard)
    {
        /** @var SessionProviderInterface $sessionProvider */
        $sessionProvider = $this->get('swag_product_advisor.session_provider');

        /** @var AnswerBuilderInterface $answerBuilder */
        $answerBuilder = $this->get('swag_product_advisor.answer_builder');

        $answers = $answerBuilder->buildAnswers($this->Request()->getPost());
        $hash = $this->Request()->getParam('hash');

        if ($isWizard) {
            $sessionProvider->saveWizardAnswer($hash, $this->Request()->getParam('questionKey'), $answers);

            return;
        }
        $sessionProvider->saveSidebarAnswers($hash, $answers);
    }

    /**
     * Helper method to get the general advisor view-data.
     *
     * @param Advisor $advisor
     * @param string  $hash
     *
     * @return array
     */
    public function getViewConfig(Advisor $advisor, $hash)
    {
        /** @var AnswerBuilderInterface $answerBuilder */
        $answerBuilder = $this->get('swag_product_advisor.answer_builder');

        $advisorArray = $this->convertAdvisorToArray($advisor);

        return [
            'advisor' => $advisorArray,
            'advisorParams' => json_encode(
                $answerBuilder->filterParams(
                    $this->Request()->getParams(),
                    function ($key) {
                        $parts = explode('_', $key);

                        return $key !== 'advisorId' && count($parts) <= 1 && !in_array('values', $parts, true) && $key !== 'hash';
                    }
                )
            ),
            'advisorResetUrl' => $this->get('front')->Router()->assemble(
                [
                    'controller' => 'advisor',
                    'action' => 'reset',
                    'advisorId' => $advisor->getId(),
                ]
            ),
            'advisorHash' => $hash,
            'advisorState' => $this->getAdvisorState(),
            'advisorCanonicalUrl' => $this->front->Router()->assemble([
                'controller' => 'advisor',
                'advisorId' => $advisor->getId(),
            ]),
        ];
    }

    /**
     * Helper method to return the relevant view-variables for a question-page in the wizard-mode.
     *
     * @param Advisor $advisor
     * @param $hash
     *
     * @return array
     */
    public function getQuestionViewConfig(Advisor $advisor, $hash)
    {
        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->container->get('swag_product_advisor.url_generator');

        array_map(function ($item) use ($urlGenerator, $advisor) {
            /* @var QuestionTrait $item */
            $item->setQuestionUrl($urlGenerator->generateWizardQuestionsUrl($advisor->getId(), $item));
        }, $advisor->getQuestions());

        $resultArray = [
            'advisorPreviousQuestion' => $urlGenerator->generatePreviousQuestionUrl($advisor, $hash),
            'advisorNextQuestion' => $urlGenerator->generateNextQuestionUrl($advisor, $hash),
        ];

        if ($this->Request()->getParam('requiredFieldsMissing')) {
            /** @var AdvisorValidatorInterface $advisorValidator */
            $advisorValidator = $this->container->get('swag_product_advisor.advisor_validator');

            $validation = $advisorValidator->validateAdvisor($advisor, 'requiredFields');

            $resultArray['missingQuestions'] = $validation['missingQuestions'];
        }

        return array_merge($this->getViewConfig($advisor, $hash), $resultArray);
    }

    /**
     * Helper method to return the listing-configuration when displaying the result.
     *
     * @param Advisor  $advisor
     * @param Criteria $criteria
     * @param string   $hash
     *
     * @return array
     */
    public function getListingConfig(Advisor $advisor, Criteria $criteria, $hash)
    {
        /** @var \Shopware\Components\QueryAliasMapper $mapper */
        $mapper = $this->get('query_alias_mapper');
        $mapper->replaceShortRequestQueries($this->Request());

        $currentPage = (int) $this->Request()->getParam('sPage', 1);

        return array_merge(
            $this->getViewConfig($advisor, $hash),
            [
                'advisorUrl' => $this->getAdvisorBaseUrl(),
                'sPage' => $currentPage,
                'pageIndex' => $currentPage,
                'pages' => ceil($advisor->getTotalCount() / $criteria->getLimit()),
                'pageSizes' => explode('|', $this->get('config')->get('numberArticlesToShow')),
                'criteria' => $criteria,
                'sPerPage' => $criteria->getLimit(),
                'shortParameters' => $mapper->getQueryAliases(),
            ]
        );
    }

    /**
     * Helper method to redirect to the result-page.
     */
    private function redirectToResult()
    {
        $this->redirect(
            [
                'action' => 'result',
                'advisorId' => $this->Request()->getParam('advisorId'),
                'hash' => $this->Request()->getParam('hash'),
            ]
        );
    }

    /**
     * Helper method to redirect to the next question in the wizard-mode.
     */
    private function redirectToNextQuestion()
    {
        $this->redirect(
            [
                'action' => 'question',
                'advisorId' => $this->Request()->getParam('advisorId'),
                'hash' => $this->Request()->getParam('hash'),
                'questionId' => $this->Request()->getParam('questionId'),
            ]
        );
    }

    /**
     * Helper method to redirect the user to the last question.
     *
     * @param Advisor $advisor
     * @param bool    $requiredFieldsMissing
     */
    private function redirectToLastQuestion(Advisor $advisor, $requiredFieldsMissing = false)
    {
        $lastQuestion = end($advisor->getQuestions());
        $this->redirect(
            [
                'action' => 'question',
                'advisorId' => $this->Request()->getParam('advisorId'),
                'hash' => $this->Request()->getParam('hash'),
                'questionId' => $lastQuestion->getId(),
                'requiredFieldsMissing' => $requiredFieldsMissing,
            ]
        );
    }

    /**
     * Returns the current state of the advisor.
     * Necessary to display the proper template, e.g. the start-page or the result-listing.
     *
     * @return string
     */
    private function getAdvisorState()
    {
        $currentAction = $this->Request()->getActionName();
        $state = 'start';

        if (!$currentAction) {
            return $state;
        }

        if ($currentAction === 'question') {
            $state = 'wizard';
        }

        if ($currentAction === 'result') {
            $state = 'listing';
        }

        return $state;
    }

    /**
     * Converts the advisor object into array.
     * Skips the product structs, so the attributes remain objects.
     *
     * @param Advisor $advisor
     *
     * @return array
     */
    private function convertAdvisorToArray(Advisor $advisor)
    {
        $result = $advisor->getResult();
        $topHit = $advisor->getTopHit();

        $advisorArray = json_decode(json_encode($advisor), true);
        $advisorArray['result'] = $result;

        if ($topHit) {
            $advisorArray['topHit'] = $topHit;
        }

        return $advisorArray;
    }

    /**
     * Assemble a base url for the advisor
     *
     * @return string
     */
    private function getAdvisorBaseUrl()
    {
        // Remove pagination information to avoid duplicate page parameters in the URL
        $params = $this->Request()->getParams();
        unset($params['sPage']);

        return $this->get('front')->Router()->assemble($params);
    }

    /**
     * @return array
     */
    private function loadThemeConfig()
    {
        $inheritance = $this->container->get('theme_inheritance');

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $this->container->get('Shop');

        $config = $inheritance->buildConfig($shop->getTemplate(), $shop, false);

        $this->get('template')->addPluginsDir(
            $inheritance->getSmartyDirectories(
                $shop->getTemplate()
            )
        );

        return $config;
    }

    /**
     * Rewrites the product URLs to be beautified already.
     *
     * @param array $products
     *
     * @return array
     */
    private function rewriteProductUrls(array $products)
    {
        $urls = array_map(function ($product) {
            return $product['linkDetails'];
        }, $products);

        /** @var RouterInterface $router */
        $router = $this->get('router');
        $rewrite = $router->generateList($urls);

        foreach ($products as $key => &$product) {
            if (!array_key_exists($key, $rewrite)) {
                continue;
            }
            $product['linkDetails'] = $rewrite[$key];
        }

        return $products;
    }
}
