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

use Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundleDBAL\QuestionHandlerInterface;

/**
 * Class PriceQuestionHandlerDBAL
 */
class PriceQuestionHandlerDBAL implements QuestionHandlerInterface
{
    /**
     * @var PriceHelperInterface
     */
    private $priceHelper;

    /**
     * PriceQuestionHandler constructor.
     *
     * @param PriceHelperInterface $priceHelper
     */
    public function __construct(PriceHelperInterface $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QuestionInterface $question)
    {
        return $question instanceof PriceRangeQuestion || $question instanceof PriceDefaultQuestion;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QuestionInterface $question, ProductContextInterface $context, QueryBuilder $query)
    {
        $this->priceHelper->joinPrices($query, $context);
        $priceSelection = $this->priceHelper->getSelection($context);
        $priceSelection = 'MIN(' . $priceSelection . ')';

        $query->addSelect($priceSelection . ' as cheapestPriceSelection');

        if ($question->getSelectedMin() > 0 && $question->getSelectedMax() > 0) {
            $query->setParameter(':advisorMinPrice', $question->getSelectedMin());
            $query->setParameter(':advisorMaxPrice', $question->getSelectedMax());

            $condition = '(' . $priceSelection . ' BETWEEN :advisorMinPrice AND :advisorMaxPrice)';
            $selection = 'IF (' . $condition . ', ' . $question->getBoost() . ', 0)';
        } elseif ($question->getSelectedMin() > 0) {
            $condition = $priceSelection . ' >= :advisorMinPrice';
            $query->setParameter(':advisorMinPrice', $question->getSelectedMin());
            $selection = 'IF (' . $condition . ', ' . $question->getBoost() . ', 0)';
        } elseif ($question->getSelectedMax() > 0) {
            $query->setParameter(':advisorMaxPrice', $question->getSelectedMax());
            $condition = $priceSelection . ' <= :advisorMaxPrice';
            $selection = 'IF (' . $condition . ', ' . $question->getBoost() . ', 0)';
        } else {
            return [];
        }

        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');

        if ($question->isExclude()) {
            $query->andHaving($condition);
        }

        return [$selection];
    }
}
