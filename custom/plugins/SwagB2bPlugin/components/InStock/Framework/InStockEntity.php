<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Framework;

use Shopware\B2B\Common\CrudEntity;

class InStockEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $authId;

    /**
     * @var int
     */
    public $articlesDetailsId;

    /**
     * @var int
     */
    public $inStock;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'auth_id' => $this->authId,
            'articles_details_id' => $this->articlesDetailsId,
            'in_stock' => $this->inStock,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->inStock = (int) $data['in_stock'];
        $this->id = (int) $data['id'];
        $this->authId = (int) $data['auth_id'];
        $this->articlesDetailsId = (int) $data['articles_details_id'];

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = (int) $value;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
