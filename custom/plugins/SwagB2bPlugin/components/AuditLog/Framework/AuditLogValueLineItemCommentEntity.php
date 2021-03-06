<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

class AuditLogValueLineItemCommentEntity extends AuditLogValueDiffEntity
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
        return 'OrderClearanceLineItemComment';
    }
}
