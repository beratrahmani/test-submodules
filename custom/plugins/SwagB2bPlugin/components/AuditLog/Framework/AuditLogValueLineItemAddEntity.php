<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\ProductName\Framework\ProductNameAware;

class AuditLogValueLineItemAddEntity extends AuditLogValueDiffEntity implements ProductNameAware
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
     * {@inheritdoc}
     */
    public function getTemplateName(): string
    {
        return 'OrderClearanceLineItemAdd';
    }

    /**
     * {@inheritdoc}
     */
    public function isChanged(): bool
    {
        return true;
    }

    /**
     * @param string $name
     */
    public function setProductName(string $name = null)
    {
        $this->productName = $name;
    }

    /**
     * @return string
     */
    public function getProductOrderNumber(): string
    {
        return $this->orderNumber;
    }
}
