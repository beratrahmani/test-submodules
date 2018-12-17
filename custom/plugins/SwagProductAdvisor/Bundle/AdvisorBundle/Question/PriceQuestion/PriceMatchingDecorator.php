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

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;
use SwagProductAdvisor\Bundle\SearchBundle\MatchingDecoratorInterface;

/**
 * Class PriceMatchingDecorator
 */
class PriceMatchingDecorator implements MatchingDecoratorInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Zend_Currency
     */
    private $currency;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var Service\PriceCalculationServiceInterface
     */
    private $calculationService;

    /**
     * PriceMatchingDecorator constructor.
     *
     * @param \Shopware_Components_Config              $config
     * @param \Zend_Currency                           $currency
     * @param \Shopware_Components_Snippet_Manager     $snippetManager
     * @param Service\PriceCalculationServiceInterface $calculationService
     */
    public function __construct(
        \Shopware_Components_Config $config,
        \Zend_Currency $currency,
        \Shopware_Components_Snippet_Manager $snippetManager,
        Service\PriceCalculationServiceInterface $calculationService
    ) {
        $this->config = $config;
        $this->currency = $currency;
        $this->snippetManager = $snippetManager;
        $this->calculationService = $calculationService;
    }

    public function decorate(ProductSearchResult $result, ShopContextInterface $context, Advisor $advisor)
    {
        $question = $this->getAnsweredPriceQuestion($advisor);
        if (!$question) {
            return;
        }

        $match = new QuestionMatch($this->createLabel($question), 'price');

        foreach ($result->getProducts() as $product) {
            /** @var AdvisorAttribute $attribute */
            $attribute = $product->getAttribute('advisor');

            $price = $this->getOriginalProductPrice($context, $product);

            if ($question->getSelectedMin() && $price->getCalculatedPrice() < $question->getSelectedMin()) {
                $attribute->addMiss($match);
                continue;
            }

            if ($question->getSelectedMax() && $price->getCalculatedPrice() > $question->getSelectedMax()) {
                $attribute->addMiss($match);
                continue;
            }
            $attribute->addMatch($match);
        }
    }

    /**
     * @param Advisor $advisor
     *
     * @return null|PriceRangeQuestion|PriceDefaultQuestion
     */
    private function getAnsweredPriceQuestion(Advisor $advisor)
    {
        foreach ($advisor->getAnsweredQuestions() as $question) {
            if ($question instanceof PriceRangeQuestion || $question instanceof PriceDefaultQuestion) {
                return $question;
            }
        }

        return null;
    }

    /**
     * @param PriceQuestionInterface $question
     *
     * @return string
     */
    private function createLabel(PriceQuestionInterface $question)
    {
        $questionStepText = $this->snippetManager->getNamespace('frontend/advisor/main')->get('QuestionStepText');
        $label = $questionStepText . ' ' . $this->currency->toCurrency($question->getSelectedMax());
        if ($question->getTemplate() === 'range_slider') {
            $label = $this->currency->toCurrency($question->getSelectedMin()) . ' ' . $questionStepText . ' ' . $this->currency->toCurrency($question->getSelectedMax());
        }

        if ($question instanceof PriceRangeQuestion) {
            return $label;
        }

        $selectedStep = $question->getSelectedStep();
        if (!$selectedStep) {
            return $label;
        }

        if ($selectedStep->getLabel()) {
            $label = $selectedStep->getLabel();
        }

        return $label;
    }

    /**
     * @param ShopContextInterface $context
     * @param ListProduct          $product
     *
     * @return Product\Price
     */
    private function getOriginalProductPrice(ShopContextInterface $context, ListProduct $product)
    {
        $originalProduct = Product::createFromListProduct($product);
        $originalProduct->setCheapestPriceRule($product->getCheapestPriceRule());
        $this->calculationService->calculateProduct($originalProduct, $context);
        $price = $originalProduct->getCheapestUnitPrice();
        if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
            $price = $originalProduct->getCheapestPrice();
        }

        return $price;
    }
}
