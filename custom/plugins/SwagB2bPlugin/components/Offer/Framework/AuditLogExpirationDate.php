<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueDiffEntity;

class AuditLogExpirationDate extends AuditLogValueDiffEntity
{
    /**
     * @var string
     */
    public $orderNumber;

    /**
     * @var string
     */
    public $productName;

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OfferDateAdded';
    }
}
