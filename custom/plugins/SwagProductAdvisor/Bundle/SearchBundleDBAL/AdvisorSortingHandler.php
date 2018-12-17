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

namespace SwagProductAdvisor\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionInterface;
use SwagProductAdvisor\Bundle\SearchBundle\AdvisorSorting;

/**
 * Class AdvisorSortingHandler
 */
class AdvisorSortingHandler implements SortingHandlerInterface
{
    /**
     * @var QuestionHandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param QuestionHandlerInterface[] $handlers
     */
    public function __construct($handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param SortingInterface $sorting
     *
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting instanceof AdvisorSorting;
    }

    /**
     * @param SortingInterface     $sorting
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     *
     * @throws \Exception
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /** @var AdvisorSorting $sorting */
        $questions = $sorting->getAdvisor()->getAnsweredQuestions();

        $selections = [];
        foreach ($questions as $question) {
            $handler = $this->getHandler($question);

            /** @var ProductContextInterface $context */
            $selections = array_merge(
                $selections,
                $handler->handle($question, $context, $query)
            );
        }

        if (empty($selections)) {
            return;
        }

        $selection = '(' . implode("\n + ", $selections) . ') as advisorRanking';

        $query->addSelect($selection);
        $query->addOrderBy('advisorRanking', 'DESC');
    }

    /**
     * @param QuestionInterface $question
     *
     * @throws \Exception
     *
     * @return QuestionHandlerInterface
     */
    private function getHandler(QuestionInterface $question)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($question)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf('Question of type %s not supported', $question->getType()));
    }
}
