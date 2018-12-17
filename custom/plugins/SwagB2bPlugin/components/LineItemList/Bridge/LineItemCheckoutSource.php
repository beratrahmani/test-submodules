<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Bridge;

use Shopware\B2B\LineItemList\Framework\LineItemListSource;

class LineItemCheckoutSource implements LineItemListSource
{
    /**
     * @var array
     */
    public $basketData;

    /**
     * @param array $basketData
     */
    public function __construct(array $basketData)
    {
        $this->basketData = $basketData;
    }
}
