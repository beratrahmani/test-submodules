<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\Repository\SearchStruct;

/**
 * Offer search in controllers through repositories.
 */
class OfferSearchStruct extends SearchStruct
{
    /**
     * @var string
     */
    public $searchStatus;
}
