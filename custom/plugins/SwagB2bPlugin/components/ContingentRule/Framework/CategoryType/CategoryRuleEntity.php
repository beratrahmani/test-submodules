<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\CategoryType;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;

class CategoryRuleEntity extends ContingentRuleEntity
{
    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var string
     */
    public $name;

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return array_merge(
            parent::toDatabaseArray(),
            ['category_id' => $this->categoryId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->categoryId = (int) $data['category_id'];
        $this->name = (string) $data['category_name'];

        return parent::fromDatabaseArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        $this->categoryId = (int) $data[$data['type'] . '_category_id'];
        $this->name = (string) $data[$data['type'] . '_category_name'];

        return parent::fromDatabaseArrayPrefixed($data);
    }
}
