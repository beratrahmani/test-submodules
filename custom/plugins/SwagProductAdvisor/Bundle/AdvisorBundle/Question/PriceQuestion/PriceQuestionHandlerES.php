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

use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\RangeQuery;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundleES\QuestionHandlerInterface;

/**
 * Class PriceQuestionHandlerES
 */
class PriceQuestionHandlerES implements QuestionHandlerInterface
{
    /**
     * @var FieldMappingInterface
     */
    private $fieldMapping;

    /**
     * PriceQuestionHandlerES constructor.
     *
     * @param FieldMappingInterface $fieldMapping
     */
    public function __construct(FieldMappingInterface $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
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
    public function handle(QuestionInterface $question, ProductContextInterface $context, BoolQuery $query)
    {
        $range = [];

        if ($question->getSelectedMin()) {
            $range['gte'] = $question->getSelectedMin();
        }
        if ($question->getSelectedMax()) {
            $range['lte'] = $question->getSelectedMax();
        }

        $type = BoolQuery::SHOULD;
        if ($question->isExclude()) {
            $type = BoolQuery::MUST;
        }

        $field = $this->fieldMapping->getPriceField($context);
        $query->add(
            new RangeQuery($field, $range),
            $type
        );
    }
}
