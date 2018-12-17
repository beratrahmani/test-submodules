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

namespace SwagProductAdvisor\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\AdvisorAttribute;

/**
 * Class AdvisorSearch
 */
class AdvisorSearch implements ProductSearchInterface
{
    /**
     * @var ProductSearchInterface
     */
    private $adapterService;

    /**
     * @var MatchingDecoratorInterface[]
     */
    private $matchingDecorators;

    /**
     * AdvisorSearch constructor.
     *
     * @param MatchingDecoratorInterface[] $matchingDecorators
     * @param ProductSearchInterface       $adapterService
     */
    public function __construct(
        \IteratorAggregate $matchingDecorators,
        ProductSearchInterface $adapterService
    ) {
        $this->adapterService = $adapterService;
        $this->matchingDecorators = $matchingDecorators;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Criteria $criteria, Struct\ProductContextInterface $context)
    {
        $result = $this->adapterService->search($criteria, $context);

        foreach ($result->getProducts() as $product) {
            $product->addAttribute('advisor', new AdvisorAttribute());
        }

        if (!$criteria->hasSorting('advisor')) {
            return $result;
        }

        /** @var AdvisorSorting $sorting */
        $sorting = $criteria->getSorting('advisor');
        $advisor = $sorting->getAdvisor();

        foreach ($this->matchingDecorators as $decorator) {
            $decorator->decorate($result, $context, $advisor);
        }

        return $result;
    }
}
