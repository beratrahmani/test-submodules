<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductOrderNumberType;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\ProductName\Framework\ProductNameAware;

class ProductOrderNumberRuleEntity extends ContingentRuleEntity implements ProductNameAware
{
    /**
     * @var string
     */
    public $productOrderNumber;

    /**
     * @var string|null
     */
    public $productName = null;

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return array_merge(
            parent::toDatabaseArray(),
            ['product_order_number' => $this->productOrderNumber]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->productOrderNumber = (string) $data['product_order_number'];

        return parent::fromDatabaseArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        $this->productOrderNumber = (string) $data[$data['type'] . '_product_order_number'];

        return parent::fromDatabaseArrayPrefixed($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductOrderNumber(): string
    {
        return $this->productOrderNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductName(string $name = null)
    {
        $this->productName = $name;
    }
}
